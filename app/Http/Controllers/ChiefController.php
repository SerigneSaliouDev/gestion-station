<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use App\Models\ShiftSaisie;
use App\Models\Station;
use App\Models\User;

class ChiefController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkRole:charge-operations');
    }
    
    /**
     * Dashboard du chef
     */
    public function dashboard()
    {
        $totalStations = Station::count();
        $totalShifts = ShiftSaisie::count();
        $shiftsEnAttente = ShiftSaisie::where('statut', 'en_attente')->count();
        $shiftsValides = ShiftSaisie::where('statut', 'valide')->count();
        
        // Derniers shifts en attente
        $derniersShifts = ShiftSaisie::with(['user', 'station'])
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Stations avec le plus de shifts
        $stationsActives = Station::withCount(['shifts' => function($query) {
            $query->whereMonth('created_at', now()->month);
        }])->orderBy('shifts_count', 'desc')
          ->limit(5)
          ->get();
        
        return view('chief.dashboard', compact(
            'totalStations', 'totalShifts', 'shiftsEnAttente', 
            'shiftsValides', 'derniersShifts', 'stationsActives'
        ));
    }
    
    /**
     * Liste des saisies à valider
     */
    public function validations()
    {
        $shifts = ShiftSaisie::with(['user', 'station', 'pompeDetails', 'depenses'])
            ->where('statut', 'en_attente')
            ->orderBy('date_shift', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('chief.validations', compact('shifts'));
    }
    
    /**
     * Détail d'une saisie pour validation
     */
    public function showValidation($id)
    {
        $shift = ShiftSaisie::with(['user', 'station', 'pompeDetails', 'depenses'])->findOrFail($id);
        
        return view('chief.validation-show', compact('shift'));
    }
    
    /**
     * Valider une saisie
     */
    public function validerSaisie(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);
        
        $shift = ShiftSaisie::findOrFail($id);
        
        $shift->update([
            'statut' => 'valide',
            'validated_by' => Auth::id(),
            'validation_date' => now(),
            'notes_validation' => $request->notes,
        ]);
        
        return redirect()->route('chief.validations')
            ->with('success', 'Saisie validée avec succès.');
    }
    
    /**
     * Rejeter une saisie
     */
    public function rejeterSaisie(Request $request, $id)
    {
        $request->validate([
            'raison' => 'required|string|max:500',
        ]);
        
        $shift = ShiftSaisie::findOrFail($id);
        
        $shift->update([
            'statut' => 'rejete',
            'validated_by' => Auth::id(),
            'validation_date' => now(),
            'notes_validation' => $request->raison,
        ]);
        
        return redirect()->route('chief.validations')
            ->with('success', 'Saisie rejetée avec succès.');
    }
    
    /**
     * Rapports par station
     */
    public function rapportsStations(Request $request)
    {
        $periode = $request->input('periode', 'monthly');
        $stationId = $request->input('station_id');
        
        // Toutes les stations
        $stations = Station::all();
        
        // Requête de base
        $query = ShiftSaisie::with(['station', 'user'])
            ->where('statut', 'valide');
        
        // Filtrer par station si spécifié
        if ($stationId) {
            $query->where('station_id', $stationId);
        }
        
        // Filtrer par période
        $endDate = Carbon::now();
        $startDate = match($periode) {
            'daily' => $endDate->copy()->startOfDay(),
            'weekly' => $endDate->copy()->subDays(7),
            'monthly' => $endDate->copy()->startOfMonth(),
            'yearly' => $endDate->copy()->startOfYear(),
            default => $endDate->copy()->startOfMonth(),
        };
        
        $query->whereBetween('date_shift', [$startDate, $endDate]);
        
        // Récupérer les données
        $shifts = $query->orderBy('date_shift', 'desc')->get();
        
        // Calculer les statistiques par station
        $statsParStation = [];
        foreach ($stations as $station) {
            $shiftsStation = $shifts->where('station_id', $station->id);
            
            $statsParStation[$station->id] = [
                'nom' => $station->nom,
                'shifts_count' => $shiftsStation->count(),
                'total_ventes' => $shiftsStation->sum('total_ventes'),
                'total_versement' => $shiftsStation->sum('versement'),
                'total_depenses' => $shiftsStation->sum('total_depenses'),
                'ecart_final' => $shiftsStation->sum('ecart_final'),
                'pompistes' => $shiftsStation->groupBy('user_id')->count(),
            ];
        }
        
        // Statistiques globales
        $statsGlobales = [
            'total_shifts' => $shifts->count(),
            'total_ventes' => $shifts->sum('total_ventes'),
            'total_versement' => $shifts->sum('versement'),
            'total_depenses' => $shifts->sum('total_depenses'),
            'ecart_final' => $shifts->sum('ecart_final'),
            'stations_count' => $shifts->groupBy('station_id')->count(),
        ];
        
        return view('chief.rapports-stations', compact(
            'stations', 'statsParStation', 'statsGlobales', 
            'periode', 'stationId', 'startDate', 'endDate'
        ));
    }
    
    /**
     * Analyse des pompistes
     */
    public function analysePompistes(Request $request)
    {
        $periode = $request->input('periode', 'monthly');
        $stationId = $request->input('station_id');
        
        // Filtrer par période
        $endDate = Carbon::now();
        $startDate = match($periode) {
            'daily' => $endDate->copy()->startOfDay(),
            'weekly' => $endDate->copy()->subDays(7),
            'monthly' => $endDate->copy()->startOfMonth(),
            'yearly' => $endDate->copy()->startOfYear(),
            default => $endDate->copy()->startOfMonth(),
        };
        
        // Requête pour les pompistes
        $query = ShiftSaisie::with(['user', 'station'])
            ->where('statut', 'valide')
            ->whereBetween('date_shift', [$startDate, $endDate]);
        
        if ($stationId) {
            $query->where('station_id', $stationId);
        }
        
        $shifts = $query->orderBy('date_shift', 'desc')->get();
        
        // Grouper par pompiste
        $pompistesData = [];
        $pompistesGroupes = $shifts->groupBy('user_id');
        
        foreach ($pompistesGroupes as $userId => $shiftsPompiste) {
            $user = $shiftsPompiste->first()->user;
            
            $pompistesData[$userId] = [
                'nom' => $user->name,
                'station' => $user->station ? $user->station->nom : 'Non assigné',
                'shifts_count' => $shiftsPompiste->count(),
                'total_ventes' => $shiftsPompiste->sum('total_ventes'),
                'total_versement' => $shiftsPompiste->sum('versement'),
                'total_depenses' => $shiftsPompiste->sum('total_depenses'),
                'ecart_final' => $shiftsPompiste->sum('ecart_final'),
                'ecart_moyen' => $shiftsPompiste->avg('ecart_final'),
                'performance' => $this->calculerPerformance($shiftsPompiste),
            ];
        }
        
        // Trier par performance
        usort($pompistesData, function($a, $b) {
            return $b['performance']['score'] <=> $a['performance']['score'];
        });
        
        $stations = Station::all();
        
        return view('chief.analyse-pompistes', compact(
            'pompistesData', 'stations', 'periode', 'stationId', 
            'startDate', 'endDate'
        ));
    }
    
    /**
     * Calculer la performance d'un pompiste
     */
    private function calculerPerformance($shifts)
    {
        $totalShifts = $shifts->count();
        $shiftsExcédent = $shifts->where('ecart_final', '>', 0)->count();
        $shiftsManquant = $shifts->where('ecart_final', '<', 0)->count();
        $shiftsEquilibre = $shifts->where('ecart_final', '=', 0)->count();
        
        $score = ($shiftsExcédent * 100) + ($shiftsEquilibre * 50) - ($shiftsManquant * 25);
        $score = max(0, $score);
        
        if ($totalShifts > 0) {
            $tauxExcédent = ($shiftsExcédent / $totalShifts) * 100;
            $tauxManquant = ($shiftsManquant / $totalShifts) * 100;
        } else {
            $tauxExcédent = $tauxManquant = 0;
        }
        
        // Déterminer le niveau
        if ($tauxExcédent >= 80) {
            $niveau = 'Excellent';
            $couleur = 'success';
        } elseif ($tauxExcédent >= 60) {
            $niveau = 'Bon';
            $couleur = 'info';
        } elseif ($tauxManquant <= 20) {
            $niveau = 'Moyen';
            $couleur = 'warning';
        } else {
            $niveau = 'À améliorer';
            $couleur = 'danger';
        }
        
        return [
            'score' => $score,
            'niveau' => $niveau,
            'couleur' => $couleur,
            'taux_excédent' => $tauxExcédent,
            'taux_manquant' => $tauxManquant,
            'shifts_excédent' => $shiftsExcédent,
            'shifts_manquant' => $shiftsManquant,
            'shifts_equilibre' => $shiftsEquilibre,
        ];
    }
    
    /**
     * Générer rapport PDF pour le chef
     */
    public function genererRapportPDF(Request $request)
    {
        $periode = $request->input('periode', 'monthly');
        $type = $request->input('type', 'stations');
        
        // Logique de génération du rapport pour le chef
        
        $pdf = PDF::loadView('chief.pdf.rapport-global', [
            // Données du rapport
        ]);
        
        return $pdf->download('rapport-chief-' . $periode . '.pdf');
    }
}