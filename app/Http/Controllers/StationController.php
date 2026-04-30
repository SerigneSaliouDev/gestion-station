<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Station;
use App\Models\User;


class StationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:chief')->only([
            'create', 'store', 'edit', 'update', 'destroy'
        ]);
    }

    /**
     * Afficher la liste des stations
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('chief')) {
            // Le chief voit toutes les stations
            $stations = Station::with('manager')
                ->orderBy('nom')
                ->paginate(20);
                
            // Calculer les statistiques
            $stats = $this->calculateStats($stations);
        } else {
            // Les managers ne voient que leurs stations
            $stations = $user->stations()
                ->orderBy('nom')
                ->paginate(20);
            $stats = null;
        }
        
        return view('chief.stations.index', compact('stations', 'stats'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        // Récupérer les managers disponibles (ceux qui n'ont pas de station active)
        $managers = User::role('manager')
            ->where(function($query) {
                $query->doesntHave('station')
                    ->orWhereHas('station', function($q) {
                        $q->where('is_active', false);
                    });
            })
            ->orderBy('name')
            ->get();
            
        return view('chief.stations.create', compact('managers'));
    }

    /**
     * Enregistrer une nouvelle station
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'code' => 'required|string|max:20|unique:stations,code',
                'ville' => 'required|string|max:100',
                'adresse' => 'nullable|string|max:500',
                'telephone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'capacite_super' => 'required|numeric|min:0',
                'capacite_gazole' => 'required|numeric|min:0',
                'manager_id' => 'nullable|exists:users,id',
                'is_active' => 'boolean'
            ], [
                'code.unique' => 'Ce code de station est déjà utilisé.',
                'capacite_super.required' => 'La capacité Super est requise.',
                'capacite_gazole.required' => 'La capacité Gazole est requise.'
            ]);
            
            // Vérifier si le manager est déjà assigné à une station active
            if (!empty($validated['manager_id'])) {
                $existingStation = Station::where('manager_id', $validated['manager_id'])
                    ->where('is_active', true)
                    ->first();
                    
                if ($existingStation) {
                    return back()->withInput()->withErrors([
                        'manager_id' => 'Ce manager est déjà assigné à la station: ' . $existingStation->nom
                    ]);
                }
            }
            
            // Par défaut, la station est active
            $validated['is_active'] = $request->has('is_active') ? true : true;
            
            // Créer la station
            $station = Station::create($validated);
            
            \Log::info('Nouvelle station créée', [
                'station_id' => $station->id,
                'nom' => $station->nom,
                'code' => $station->code,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('chief.stations.index')
                ->with('success', 'Station créée avec succès!');
                
        } catch (\Exception $e) {
            \Log::error('Erreur création station: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'une station
     */
    public function show($id)
    {
        $user = Auth::user();
        
        if ($user->hasRole('chief')) {
            $station = Station::with(['manager', 'shifts' => function($query) {
                $query->orderBy('date_shift', 'desc')->limit(10);
            }])->findOrFail($id);
        } else {
            $station = $user->stations()
                ->with(['manager', 'shifts' => function($query) {
                    $query->orderBy('date_shift', 'desc')->limit(10);
                }])
                ->findOrFail($id);
        }
        
        // Calculer les statistiques de la station
        $stats = $this->calculateStationStats($station);
        
        return view('chief.stations.show', compact('station', 'stats'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $station = Station::findOrFail($id);
        
        // Vérifier les permissions
        $user = Auth::user();
        if (!$user->hasRole('chief')) {
            abort(403, 'Accès non autorisé');
        }
        
        // Récupérer tous les managers
        $managers = User::role('manager')->orderBy('name')->get();
        
        return view('chief.stations.edit', compact('station', 'managers'));
    }

    /**
     * Mettre à jour une station
     */
    public function update(Request $request, $id)
    {
        try {
            $station = Station::findOrFail($id);
            
            // Vérifier les permissions
            $user = Auth::user();
            if (!$user->hasRole('chief')) {
                abort(403, 'Accès non autorisé');
            }
            
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'code' => 'required|string|max:20|unique:stations,code,' . $station->id,
                'ville' => 'required|string|max:100',
                'adresse' => 'nullable|string|max:500',
                'telephone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'capacite_super' => 'required|numeric|min:0',
                'capacite_gazole' => 'required|numeric|min:0',
                'manager_id' => 'nullable|exists:users,id',
                'is_active' => 'boolean'
            ]);
            
            // Vérifier si le manager est déjà assigné à une autre station active
            if (!empty($validated['manager_id']) && $validated['manager_id'] != $station->manager_id) {
                $existingStation = Station::where('manager_id', $validated['manager_id'])
                    ->where('is_active', true)
                    ->where('id', '!=', $station->id)
                    ->first();
                    
                if ($existingStation) {
                    return back()->withInput()->withErrors([
                        'manager_id' => 'Ce manager est déjà assigné à la station: ' . $existingStation->nom
                    ]);
                }
            }
            
            // Si la station est désactivée, libérer le manager
            if (!$request->has('is_active') && $station->manager_id) {
                $validated['manager_id'] = null;
            }
            
            $station->update($validated);
            
            \Log::info('Station mise à jour', [
                'station_id' => $station->id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('chief.stations.show', $station->id)
                ->with('success', 'Station mise à jour avec succès!');
                
        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour station: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une station
     */
    public function destroy($id)
    {
        try {
            $station = Station::findOrFail($id);
            
            // Vérifier les permissions
            $user = Auth::user();
            if (!$user->hasRole('chief')) {
                abort(403, 'Accès non autorisé');
            }
            
            // Vérifier s'il y a des données associées
            $hasShifts = $station->shifts()->exists();
            
            if ($hasShifts) {
                return back()->with('error', 'Impossible de supprimer cette station car elle contient des données historiques. Vous pouvez la désactiver à la place.');
            }
            
            $station->delete();
            
            \Log::info('Station supprimée', [
                'station_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return redirect()->route('chief.stations.index')
                ->with('success', 'Station supprimée avec succès!');
                
        } catch (\Exception $e) {
            \Log::error('Erreur suppression station: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Calculer les statistiques pour le dashboard
     */
    private function calculateStats($stations)
    {
        $totalStations = Station::count();
        $activeStations = Station::where('is_active', true)->count();
        
        // Calculer la capacité totale
        $totalCapacity = Station::sum('capacite_super') + 
                         Station::sum('capacite_gazole') +
                         Station::sum('capacite_essence piro') +
                     
        
        // Compter les managers actifs
        $activeManagers = User::role('manager')
            ->whereHas('station', function($query) {
                $query->where('is_active', true);
            })
            ->count();
        
        // Calculer les ventes totales (si vous avez cette donnée)
        $totalVentes = 0;
        if (class_exists('App\Models\ShiftSaisie')) {
            $totalVentes = \App\Models\ShiftSaisie::where('statut', 'valide')
                ->sum('total_ventes');
        }
        
        return [
            'total_ventes' => $totalVentes,
            'stations_actives' => $activeStations,
            'managers_actifs' => $activeManagers,
            'total_capacite' => $totalCapacity,
            'stations_total' => $totalStations,
            'stations_inactives' => $totalStations - $activeStations,
            'moyenne_station' => $totalStations > 0 ? $totalCapacity / $totalStations : 0
        ];
    }

    /**
     * Calculer les statistiques d'une station spécifique
     */
    private function calculateStationStats($station)
    {
        // Ventes du mois en cours
        $monthSales = $station->shifts()
            ->where('statut', 'valide')
            ->whereMonth('date_shift', now()->month)
            ->whereYear('date_shift', now()->year)
            ->sum('total_ventes');
        
        // Shifts du mois
        $monthShifts = $station->shifts()
            ->where('statut', 'valide')
            ->whereMonth('date_shift', now()->month)
            ->whereYear('date_shift', now()->year)
            ->count();
        
        // Dernier shift
        $lastShift = $station->shifts()
            ->where('statut', 'valide')
            ->orderBy('date_shift', 'desc')
            ->first();
        
        return [
            'month_sales' => $monthSales,
            'month_shifts' => $monthShifts,
            'average_sales_per_shift' => $monthShifts > 0 ? $monthSales / $monthShifts : 0,
            'last_activity' => $lastShift ? $lastShift->date_shift->format('d/m/Y') : 'Aucune',
            'total_capacity' => ($station->capacite_super ?? 0) + 
                               ($station->capacite_gazole ?? 0) +
                               ($station->capacite_premium ?? 0) +
                               ($station->capacite_kerosene ?? 0) +
                               ($station->capacite_petrole ?? 0)
        ];
    }

    /**
     * Méthodes pour la sélection de station (pour les managers)
     */
    public function showStationSelector()
    {
        $user = Auth::user();
        $stations = $user->stations()->withCount(['shifts'])->get();
        
        // Si une seule station, la sélectionner automatiquement
        if ($stations->count() === 1) {
            session(['selected_station_id' => $stations->first()->id]);
            return redirect()->route('manager.index_form');
        }
        
        return view('manager.station-selector', compact('stations'));
    }

    public function selectStation(Request $request)
    {
        $request->validate([
            'station_id' => 'required|exists:stations,id'
        ]);
        
        $user = Auth::user();
        
        // Vérifier que l'utilisateur a accès à cette station
        $station = $user->stations()->find($request->station_id);
        if (!$station) {
            return redirect()->back()->with('error', 'Accès non autorisé à cette station.');
        }
        
        session(['selected_station_id' => $request->station_id]);
        
        return redirect()->route('manager.index_form')->with('success', 'Station sélectionnée avec succès.');
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        if ($user->hasRole('chief')) {
            // Dashboard du chief avec toutes les stations
            $stations = Station::with(['manager', 'shifts' => function($query) {
                $query->where('statut', 'valide')
                    ->orderBy('date_shift', 'desc')
                    ->limit(5);
            }])->get();
            
            return view('chief.dashboard', compact('stations'));
        } else {
            // Dashboard du manager
            $stations = $user->stations()->with(['shifts' => function($query) {
                $query->orderBy('date_shift', 'desc')->limit(5);
            }])->get();
            
            $activeStation = session('selected_station_id') ? 
                $user->stations()->find(session('selected_station_id')) : 
                $stations->first();
            
            return view('manager.multi-station-dashboard', compact('stations', 'activeStation'));
        }
    }
}