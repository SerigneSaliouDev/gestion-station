<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use App\Models\ShiftSaisie;
use App\Models\ShiftPompeDetail;
use App\Models\Depense;
use App\Models\Station;
use App\Models\User;

class ManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkStation')->except(['showIndexForm']);
        $this->middleware('role:manager')->except(['login', 'logout']);
    }

    /**
     * Affiche le formulaire de saisie des index avec la station du gérant
     */
    public function showIndexForm()
    {
        $user = Auth::user();
        $station = $user->station;
        
        // Vérifier si le gérant est assigné à une station
        if ($user->isManager() && !$station) {
            return redirect()->route('manager.history')
                ->with('error', 'Vous n\'êtes pas assigné à une station.');
        }
        
        // Récupérer les DERNIERS prix depuis la table fuel_prices
        $currentPrices = $this->getCurrentFuelPrices();

        $pumps = [
            ['name' => 'Pompe 1', 'fuel_type' => 'Gazole', 'unit_price' => 750, 'pump_id' => 1],
            ['name' => 'Pompe 2', 'fuel_type' => 'Super', 'unit_price' => 850, 'pump_id' => 2],
        ];

        return view('manager.saisie-index', [
            'pumps' => $pumps,
            'user' => $user,
            'station' => $station,
        ]);
    }

    /**
     * Récupérer les prix actuels des carburants
     */
    private function getCurrentFuelPrices()
    {
        $prices = [];
        
        $fuelTypes = ['super', 'gazole'];
        
        foreach ($fuelTypes as $type) {
            $latestPrice = \App\Models\FuelPrice::where('fuel_type', $type)
                ->latest()
                ->first();
                
            $prices[$type] = $latestPrice ? $latestPrice->price_per_liter : 0;
        }
        
        return $prices;
    }

    /**
     * Fonction helper pour calculer l'écart de manière cohérente
     */
    private function calculateEcart($versement, $ventes, $depenses = 0)
    {
        return [
            'initial' => $versement - $ventes, // Versement - Ventes
            'final' => $versement - ($ventes - $depenses) // Versement - (Ventes - Dépenses)
        ];
    }

    /**
     * Stocker une nouvelle saisie avec station_id
     */
    public function storeIndex(Request $request)
    {
        $user = Auth::user();
        
        // Vérifier si le gérant est assigné à une station
        if ($user->isManager() && !$user->station_id) {
            return redirect()->back()
                ->with('error', 'Vous n\'êtes pas assigné à une station.');
        }
        
        $validated = $request->validate([
            'shift_date' => 'required|date',
            'shift_time' => 'required|string',
            'responsible_name' => 'required|string',
            'pumps' => 'required|array',
            'pumps.*.opening_index' => 'required|numeric|min:0',
            'pumps.*.closing_index' => 'required|numeric|min:0',
            'pumps.*.total_return' => 'nullable|numeric|min:0',
            'cash_deposit_amount' => 'required|numeric|min:0',
            'depenses' => 'nullable|array',
            'depenses.*.type' => 'nullable|string',
            'depenses.*.montant' => 'nullable|numeric|min:0',
            'depenses.*.description' => 'nullable|string',
            'depenses.*.justificatif_file' => 'nullable|file|max:5120',
        ]);

        // Calcul des totaux pompes
        $totalLiters = 0;
        $totalSales = 0;

        foreach ($request->pumps as $pump) {
            $opening = floatval($pump['opening_index']);
            $closing = floatval($pump['closing_index']);
            $returnVal = floatval($pump['total_return'] ?? 0);
            $unitPrice = floatval($pump['unit_price']);

            if ($closing >= $opening) {
                $literage = ($closing - $opening) - $returnVal;
                if ($literage < 0) $literage = 0;
            } else {
                $literage = 0;
            }

            $totalLiters += $literage;
            $totalSales += $literage * $unitPrice;
        }

        $cashDeposit = floatval($request->cash_deposit_amount);
        
        // Calculer les écarts avec la fonction helper
        $ecarts = $this->calculateEcart($cashDeposit, $totalSales);
        $ecartInitial = $ecarts['initial'];

        // Créer la saisie AVEC station_id
        $shift = ShiftSaisie::create([
            'date_shift' => $request->shift_date,
            'shift' => $request->shift_time,
            'responsable' => $request->responsible_name,
            'total_litres' => $totalLiters,
            'total_ventes' => $totalSales,
            'versement' => $cashDeposit,
            'ecart' => $ecartInitial, // Écart initial sans dépenses
            'user_id' => auth()->id(),
            'station_id' => $user->station_id, // AJOUTÉ
            'statut' => 'en_attente', // Par défaut en attente de validation
        ]);

        // Enregistrer les détails des pompes
        foreach ($request->pumps as $pump) {
            $opening = floatval($pump['opening_index']);
            $closing = floatval($pump['closing_index']);
            $returnVal = floatval($pump['total_return'] ?? 0);
            $unitPrice = floatval($pump['unit_price']);

            if ($closing >= $opening) {
                $literage = ($closing - $opening) - $returnVal;
                if ($literage < 0) $literage = 0;
            } else {
                $literage = 0;
            }

            ShiftPompeDetail::create([
                'shift_saisie_id' => $shift->id,
                'pompe_nom' => $pump['name'],
                'carburant' => $pump['fuel_type'],
                'prix_unitaire' => $unitPrice,
                'index_ouverture' => $opening,
                'index_fermeture' => $closing,
                'retour_litres' => $returnVal,
                'litrage_vendu' => $literage,
                'montant_ventes' => $literage * $unitPrice,
            ]);
            
        }

        // Gestion des dépenses avec fichiers
        $totalDepenses = 0;
        if ($request->has('depenses')) {
            foreach ($request->depenses as $index => $depense) {
                if (!empty($depense['type']) && floatval($depense['montant']) > 0) {
                    $depenseData = [
                        'shift_saisie_id' => $shift->id,
                        'type_depense' => $depense['type'],
                        'montant' => floatval($depense['montant']),
                        'description' => $depense['description'] ?? null,
                    ];

                    // Gestion du fichier justificatif
                    if ($request->hasFile("depenses.{$index}.justificatif_file")) {
                        $file = $request->file("depenses.{$index}.justificatif_file");
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs('justificatifs', $fileName, 'public');
                        
                        $depenseData['justificatif'] = $filePath;
                    }

                    Depense::create($depenseData);
                    $totalDepenses += floatval($depense['montant']);
                }
            }
        }

        // Calculer l'écart final AVEC les dépenses
        $ecartsAvecDepenses = $this->calculateEcart($cashDeposit, $totalSales, $totalDepenses);
        $ecartFinal = $ecartsAvecDepenses['final'];
        
        $shift->update([
            'total_depenses' => $totalDepenses,
            'ecart_final' => $ecartFinal
        ]);

        return redirect()->route('manager.history')
            ->with('success', 'Saisie enregistrée avec succès! En attente de validation du chef d\'opérations.');

            
    }

    /**
     * Debug pour vérifier les calculs d'écart
     */
    public function debugEcart($id)
    {
        $shift = ShiftSaisie::find($id);
        
        echo "Ventes: " . $shift->total_ventes . "<br>";
        echo "Dépenses: " . $shift->total_depenses . "<br>";
        echo "Versement: " . $shift->versement . "<br>";
        echo "Écart stocké: " . $shift->ecart_final . "<br>";
        
        // Calcul selon notre formule
        $calcul = $shift->versement - ($shift->total_ventes - $shift->total_depenses);
        echo "Calcul correct: " . $calcul . "<br>";
        
        // Vérifiez la méthode du modèle
        echo "getEcartFinalCalculatedAttribute: " . $shift->ecart_final_calculated . "<br>";
    }

    /**
     * Historique des saisies - Filtré par station pour les gérants
     */
    public function history()
    {
        $user = Auth::user();
        
        // Requête de base
        $query = ShiftSaisie::with(['pompeDetails', 'depenses', 'station'])
            ->where('user_id', auth()->id());
        
        // Les gérants voient seulement leurs saisies de leur station
        if ($user->isManager()) {
            $query->where('station_id', $user->station_id);
        }
        
        $saisies = $query->orderBy('date_shift', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('manager.history', compact('saisies'));
    }

    /**
     * Afficher une saisie spécifique
     */
    public function show($id)
    {
        $shift = ShiftSaisie::with(['pompeDetails', 'depenses', 'station'])->findOrFail($id);

        // Vérifier l'accès
        if ($shift->user_id != auth()->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        // Pour les gérants, vérifier aussi la station
        $user = Auth::user();
        if ($user->isManager() && $shift->station_id != $user->station_id) {
            abort(403, 'Accès non autorisé à cette station.');
        }

        return view('manager.history-show', compact('shift'));
    }

    /**
     * Éditer une saisie
     */
    public function edit($id)
    {
        $shift = ShiftSaisie::with(['pompeDetails', 'depenses', 'station'])->findOrFail($id);

        // Vérifier l'accès
        if ($shift->user_id != auth()->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        // Pour les gérants, vérifier aussi la station
        $user = Auth::user();
        if ($user->isManager() && $shift->station_id != $user->station_id) {
            abort(403, 'Accès non autorisé à cette station.');
        }

        $pumps = [
            ['name' => 'Pompe 1', 'fuel_type' => 'Gazole', 'unit_price' => 750, 'pump_id' => 1],
            ['name' => 'Pompe 2', 'fuel_type' => 'Super', 'unit_price' => 850, 'pump_id' => 2],
        ];

        return view('manager.edit', compact('shift', 'pumps'));
    }

    /**
     * Mettre à jour une saisie
     */
    public function update(Request $request, $id)
    {
        $shift = ShiftSaisie::with('depenses')->findOrFail($id);

        // Vérifier l'accès
        if ($shift->user_id != auth()->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        // Pour les gérants, vérifier aussi la station
        $user = Auth::user();
        if ($user->isManager() && $shift->station_id != $user->station_id) {
            abort(403, 'Accès non autorisé à cette station.');
        }

        $validated = $request->validate([
            'shift_date' => 'required|date',
            'shift_time' => 'required|string',
            'responsible_name' => 'required|string|max:255',
            'cash_deposit_amount' => 'required|numeric|min:0',
            'pumps' => 'required|array',
            'existing_depenses' => 'nullable|array',
            'existing_depenses.*.type' => 'nullable|string',
            'existing_depenses.*.montant' => 'nullable|numeric|min:0',
            'existing_depenses.*.description' => 'nullable|string',
            'existing_depenses.*.justificatif_file' => 'nullable|file|max:5120',
            'existing_depenses.*.delete' => 'nullable|boolean',
            'new_depenses' => 'nullable|array',
            'new_depenses.*.type' => 'nullable|string',
            'new_depenses.*.montant' => 'nullable|numeric|min:0',
            'new_depenses.*.description' => 'nullable|string',
            'new_depenses.*.justificatif_file' => 'nullable|file|max:5120',
        ]);

        // Calcul des totaux pompes
        $totalLiters = 0;
        $totalSales = 0;

        foreach ($request->pumps as $pump) {
            $opening = floatval($pump['opening_index']);
            $closing = floatval($pump['closing_index']);
            $returnVal = floatval($pump['total_return'] ?? 0);
            $unitPrice = floatval($pump['unit_price']);

            if ($closing >= $opening) {
                $literage = ($closing - $opening) - $returnVal;
                if ($literage < 0) $literage = 0;
            } else {
                $literage = 0;
            }

            $totalLiters += $literage;
            $totalSales += $literage * $unitPrice;
        }

        $cashDeposit = floatval($request->cash_deposit_amount);
        
        // Calculer l'écart initial SANS dépenses
        $ecartInitial = $cashDeposit - $totalSales;

        // Mise à jour principale
        $shift->update([
            'date_shift' => $request->shift_date,
            'shift' => $request->shift_time,
            'responsable' => $request->responsible_name,
            'total_litres' => $totalLiters,
            'total_ventes' => $totalSales,
            'versement' => $cashDeposit,
            'ecart' => $ecartInitial,
            'statut' => 'en_attente', // Retour en attente après modification
        ]);

        // Supprimer anciens détails de pompes et recréer
        ShiftPompeDetail::where('shift_saisie_id', $shift->id)->delete();

        foreach ($request->pumps as $pump) {
            $opening = floatval($pump['opening_index']);
            $closing = floatval($pump['closing_index']);
            $returnVal = floatval($pump['total_return'] ?? 0);
            $unitPrice = floatval($pump['unit_price']);

            if ($closing >= $opening) {
                $literage = ($closing - $opening) - $returnVal;
                if ($literage < 0) $literage = 0;
            } else {
                $literage = 0;
            }

            ShiftPompeDetail::create([
                'shift_saisie_id' => $shift->id,
                'pompe_nom' => $pump['name'],
                'carburant' => $pump['fuel_type'],
                'prix_unitaire' => $unitPrice,
                'index_ouverture' => $opening,
                'index_fermeture' => $closing,
                'retour_litres' => $returnVal,
                'litrage_vendu' => $literage,
                'montant_ventes' => $literage * $unitPrice,
            ]);
        }

        // Gestion des dépenses existantes
        $totalDepenses = 0;
        
        if ($request->has('existing_depenses')) {
            foreach ($request->existing_depenses as $index => $depenseData) {
                $depense = Depense::find($depenseData['id']);
                
                if ($depense && $depense->shift_saisie_id == $shift->id) {
                    // Vérifier si la dépense doit être supprimée
                    if (isset($depenseData['delete']) && $depenseData['delete'] == '1') {
                        // Supprimer le fichier si existant
                        if ($depense->justificatif) {
                            Storage::disk('public')->delete($depense->justificatif);
                        }
                        $depense->delete();
                        continue;
                    }
                    
                    // Mettre à jour la dépense existante
                    $updateData = [
                        'type_depense' => $depenseData['type'],
                        'montant' => floatval($depenseData['montant']),
                        'description' => $depenseData['description'] ?? null,
                    ];
                    
                    // Gestion du fichier justificatif (nouveau fichier)
                    if ($request->hasFile("existing_depenses.{$index}.justificatif_file")) {
                        // Supprimer l'ancien fichier
                        if ($depense->justificatif) {
                            Storage::disk('public')->delete($depense->justificatif);
                        }
                        
                        // Enregistrer le nouveau fichier
                        $file = $request->file("existing_depenses.{$index}.justificatif_file");
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs('justificatifs', $fileName, 'public');
                        
                        $updateData['justificatif'] = $filePath;
                    }
                    
                    $depense->update($updateData);
                    $totalDepenses += floatval($depenseData['montant']);
                }
            }
        }

        // Gestion des nouvelles dépenses
        if ($request->has('new_depenses')) {
            foreach ($request->new_depenses as $index => $depenseData) {
                if (!empty($depenseData['type']) && floatval($depenseData['montant']) > 0) {
                    $newDepense = [
                        'shift_saisie_id' => $shift->id,
                        'type_depense' => $depenseData['type'],
                        'montant' => floatval($depenseData['montant']),
                        'description' => $depenseData['description'] ?? null,
                    ];

                    // Gestion du fichier justificatif
                    if ($request->hasFile("new_depenses.{$index}.justificatif_file")) {
                        $file = $request->file("new_depenses.{$index}.justificatif_file");
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $filePath = $file->storeAs('justificatifs', $fileName, 'public');
                        
                        $newDepense['justificatif'] = $filePath;
                    }

                    Depense::create($newDepense);
                    $totalDepenses += floatval($depenseData['montant']);
                }
            }
        }

        // Calculer l'écart final AVEC dépenses
        $ecartsAvecDepenses = $this->calculateEcart($cashDeposit, $totalSales, $totalDepenses);
        $ecartFinal = $ecartsAvecDepenses['final'];
        
        $shift->update([
            'total_depenses' => $totalDepenses,
            'ecart_final' => $ecartFinal
        ]);

        return redirect()->route('manager.history')
            ->with('success', 'Saisie mise à jour avec succès!');
    }

    /**
     * Suppression d'une saisie
     */
    public function destroy($id)
    {
        $shift = ShiftSaisie::findOrFail($id);

        // Vérifier l'accès
        if ($shift->user_id != auth()->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        // Pour les gérants, vérifier aussi la station
        $user = Auth::user();
        if ($user->isManager() && $shift->station_id != $user->station_id) {
            abort(403, 'Accès non autorisé à cette station.');
        }

        // Supprimer les fichiers justificatifs
        foreach ($shift->depenses as $depense) {
            if ($depense->justificatif) {
                Storage::disk('public')->delete($depense->justificatif);
            }
        }

        ShiftPompeDetail::where('shift_saisie_id', $shift->id)->delete();
        $shift->depenses()->delete();
        $shift->delete();

        return redirect()->route('manager.history')
            ->with('success', 'Saisie supprimée avec succès!');
    }

    /**
     * Télécharger un fichier justificatif
     */
    public function downloadJustificatif($id)
    {
        $depense = Depense::findOrFail($id);
        $shift = $depense->shiftSaisie;
        
        // Vérifier l'accès
        if ($shift->user_id != auth()->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        // Pour les gérants, vérifier aussi la station
        $user = Auth::user();
        if ($user->isManager() && $shift->station_id != $user->station_id) {
            abort(403, 'Accès non autorisé à cette station.');
        }
        
        if (!$depense->justificatif) {
            abort(404, 'Fichier non trouvé');
        }
        
        $filePath = storage_path('app/public/' . $depense->justificatif);
        
        if (!file_exists($filePath)) {
            abort(404, 'Fichier non trouvé sur le serveur');
        }
        
        return response()->download($filePath, basename($depense->justificatif));
    }

    /**
     * Générer PDF d'un shift spécifique
     */
    public function generatePdf($id)
    {
        $shift = ShiftSaisie::with(['pompeDetails', 'depenses', 'station'])->findOrFail($id);
        
        // Vérifier l'accès
        if ($shift->user_id != auth()->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        // Pour les gérants, vérifier aussi la station
        $user = Auth::user();
        if ($user->isManager() && $shift->station_id != $user->station_id) {
            abort(403, 'Accès non autorisé à cette station.');
        }

        // CORRECTION : Calculer l'écart final selon la formule
        $ecartFinalCalcule = $shift->versement - ($shift->total_ventes - $shift->total_depenses);

        $pdf = PDF::loadView('pdf.shift-report', [
            'shift' => $shift,
            'user' => auth()->user(),
            'periode' => 'shift_' . $shift->id,
            'shifts' => collect([$shift]),
            'stats' => [
                'totalShifts' => 1,
                'totalLitres' => $shift->total_litres,
                'totalVentes' => $shift->total_ventes,
                'totalVersement' => $shift->versement,
                'totalDepenses' => $shift->total_depenses,
                // CORRECTION : Utiliser l'écart calculé
                'totalEcartFinal' => $ecartFinalCalcule,
            ],
            'startDate' => $shift->date_shift,
            'endDate' => $shift->date_shift,
        ]);

        $filename = 'shift-' . $shift->id . '-' . $shift->date_shift->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Print
     */
    public function print($id)
    {
        $shift = ShiftSaisie::with(['pompeDetails', 'depenses', 'station'])->findOrFail($id);

        // Vérifier l'accès
        if ($shift->user_id != auth()->id()) {
            abort(403, 'Accès non autorisé');
        }
        
        // Pour les gérants, vérifier aussi la station
        $user = Auth::user();
        if ($user->isManager() && $shift->station_id != $user->station_id) {
            abort(403, 'Accès non autorisé à cette station.');
        }

        return view('manager.print', compact('shift'));
    }

    /**
     * Rapports détaillés avec synthèse des écarts - Filtrés par station
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        $periode = $request->input('periode', 'weekly');
        $jours = $request->input('jours', 7);
        
        // Calculer les dates
        $endDate = Carbon::now();
        $startDate = match($periode) {
            'daily' => $endDate->copy()->startOfDay(),
            'weekly' => $endDate->copy()->subDays(7),
            'monthly' => $endDate->copy()->subDays(30),
            'custom' => $endDate->copy()->subDays($jours),
            default => $endDate->copy()->subDays(7),
        };
        
        // Récupérer les shifts FILTRÉS PAR STATION
        $query = ShiftSaisie::with(['pompeDetails', 'depenses', 'station'])
            ->where('user_id', auth()->id())
            ->whereBetween('date_shift', [$startDate, $endDate]);
        
        // Les gérants voient seulement leurs saisies de leur station
        if ($user->isManager()) {
            $query->where('station_id', $user->station_id);
        }
        
        $shifts = $query->orderBy('date_shift', 'desc')
            ->orderBy('shift', 'asc')
            ->get();
        
        // CORRECTION : Recalculer les écarts selon la formule correcte pour chaque shift
        foreach ($shifts as $shift) {
            // Formule correcte : Écart = Versement - (Ventes - Dépenses)
            $shift->ecart_final_calcule = $shift->versement - ($shift->total_ventes - $shift->total_depenses);
            
            // Écart initial sans dépenses
            $shift->ecart_initial_calcule = $shift->versement - $shift->total_ventes;
        }
        
        // CORRECTION : Calculer les statistiques AVEC LA FORMULE CORRECTE
        $stats = [
            'totalShifts' => $shifts->count(),
            'totalLitres' => $shifts->sum('total_litres'),
            'totalVentes' => $shifts->sum('total_ventes'),
            'totalVersement' => $shifts->sum('versement'),
            'totalDepenses' => $shifts->sum('total_depenses'),
            // CORRECTION : Recalculer les écarts avec la formule correcte
            'totalEcartInitial' => $shifts->sum(function($shift) {
                return $shift->versement - $shift->total_ventes; // Versement - Ventes
            }),
            'totalEcartFinal' => $shifts->sum(function($shift) {
                return $shift->versement - ($shift->total_ventes - $shift->total_depenses); // Versement - (Ventes - Dépenses)
            }),
            'ecartMoyen' => $shifts->count() > 0 ? $shifts->avg(function($shift) {
                return $shift->versement - ($shift->total_ventes - $shift->total_depenses);
            }) : 0,
            // CORRECTION : Écart max = le plus POSITIF (meilleur = excédent)
            'ecartMax' => $shifts->max(function($shift) {
                return $shift->versement - ($shift->total_ventes - $shift->total_depenses);
            }),
            // CORRECTION : Écart min = le plus NÉGATIF (pire = manquant)
            'ecartMin' => $shifts->min(function($shift) {
                return $shift->versement - ($shift->total_ventes - $shift->total_depenses);
            }),
        ];
        
        // CORRECTION : Calculer la répartition des écarts AVEC LES DONNÉES RECALCULÉES
        $repartitionEcarts = [
            'manquant' => $shifts->where(function($shift) {
                return $shift->versement - ($shift->total_ventes - $shift->total_depenses) < 0;
            })->count(), // Négatif = Manquant
            'excédent' => $shifts->where(function($shift) {
                return $shift->versement - ($shift->total_ventes - $shift->total_depenses) > 0;
            })->count(), // Positif = Excédent
            'equilibre' => $shifts->where(function($shift) {
                return $shift->versement - ($shift->total_ventes - $shift->total_depenses) == 0;
            })->count(),
        ];
        
        // Calculer par carburant
        $byFuel = [];
        foreach ($shifts as $shift) {
            foreach ($shift->pompeDetails as $detail) {
                $fuelType = $detail->carburant;
                if (!isset($byFuel[$fuelType])) {
                    $byFuel[$fuelType] = [
                        'litres' => 0,
                        'montant' => 0,
                        'pompes' => []
                    ];
                }
                $byFuel[$fuelType]['litres'] += $detail->litrage_vendu;
                $byFuel[$fuelType]['montant'] += $detail->montant_ventes;
                $byFuel[$fuelType]['pompes'][] = $detail->pompe_nom;
            }
        }
        
        // Calculer les pourcentages
        $totalLitres = $stats['totalLitres'];
        $totalMontant = $stats['totalVentes'];
        foreach ($byFuel as &$data) {
            $data['pompes'] = array_unique($data['pompes']);
            $data['pourcentage_litres'] = $totalLitres > 0 ? round(($data['litres'] / $totalLitres) * 100, 2) : 0;
            $data['pourcentage_montant'] = $totalMontant > 0 ? round(($data['montant'] / $totalMontant) * 100, 2) : 0;
        }
        
        // Dépenses par type
        $depensesParType = [];
        foreach ($shifts as $shift) {
            foreach ($shift->depenses as $depense) {
                $type = $depense->type_depense;
                if (!isset($depensesParType[$type])) {
                    $depensesParType[$type] = [
                        'montant' => 0,
                        'nombre' => 0,
                        'icone' => $this->getDepenseIcon($type)
                    ];
                }
                $depensesParType[$type]['montant'] += $depense->montant;
                $depensesParType[$type]['nombre']++;
            }
        }
        
        // CORRECTION : Écarts journaliers avec les données recalculées
        $ecartsJournaliers = [];
        foreach ($shifts as $shift) {
            $date = $shift->date_shift->format('Y-m-d');
            if (!isset($ecartsJournaliers[$date])) {
                $ecartsJournaliers[$date] = [
                    'date_format' => $shift->date_shift->format('d/m'),
                    'nombre_shifts' => 0,
                    'total_ecart_initial' => 0,
                    'total_ecart_final' => 0,
                    'total_ventes' => 0,
                    'total_versement' => 0,
                    'total_depenses' => 0,
                    'shifts' => []
                ];
            }
            $ecartsJournaliers[$date]['nombre_shifts']++;
            
            // CORRECTION : Utiliser les calculs corrects
            $ecartInitial = $shift->versement - $shift->total_ventes;
            $ecartFinal = $shift->versement - ($shift->total_ventes - $shift->total_depenses);
            
            $ecartsJournaliers[$date]['total_ecart_initial'] += $ecartInitial;
            $ecartsJournaliers[$date]['total_ecart_final'] += $ecartFinal;
            $ecartsJournaliers[$date]['total_ventes'] += $shift->total_ventes;
            $ecartsJournaliers[$date]['total_versement'] += $shift->versement;
            $ecartsJournaliers[$date]['total_depenses'] += $shift->total_depenses;
            $ecartsJournaliers[$date]['shifts'][] = $shift;
        }
        
        // CORRECTION : Calculer les tendances des écarts avec les données recalculées
        $tendanceEcarts = $this->calculerTendanceEcarts($ecartsJournaliers);
        
        // CORRECTION : Ajouter les écarts recalculés aux shifts pour la vue
        foreach ($shifts as $shift) {
            $shift->ecart_final_calcule = $shift->versement - ($shift->total_ventes - $shift->total_depenses);
            $shift->ecart_initial_calcule = $shift->versement - $shift->total_ventes;
        }
        
        return view('manager.reports', compact(
            'periode', 'jours', 'startDate', 'endDate', 
            'shifts', 'stats', 'byFuel', 'depensesParType', 
            'ecartsJournaliers', 'repartitionEcarts', 'tendanceEcarts'
        ));
    }

    /**
     * Calculer la tendance des écarts
     */
    private function calculerTendanceEcarts($ecartsJournaliers)
    {
        $jours = count($ecartsJournaliers);
        if ($jours < 2) {
            return [
                'tendance' => 'stable',
                'message' => 'Données insuffisantes pour calculer la tendance'
            ];
        }
        
        // Prendre les 5 derniers jours pour la tendance
        $derniersEcarts = array_slice($ecartsJournaliers, -5, 5, true);
        $sommeVariations = 0;
        $count = 0;
        $previous = null;
        
        foreach ($derniersEcarts as $date => $data) {
            if ($previous !== null) {
                // Variation = écart actuel - écart précédent
                $variation = $data['total_ecart_final'] - $previous['total_ecart_final'];
                $sommeVariations += $variation;
                $count++;
            }
            $previous = $data;
        }
        
        if ($count == 0) {
            return ['tendance' => 'stable', 'message' => 'Tendance stable'];
        }
        
        $moyenneVariation = $sommeVariations / $count;
        
        if ($moyenneVariation > 1000) {
            return [
                'tendance' => 'deterioration', // Les écarts deviennent plus POSITIFS (excédents augmentent)
                'message' => 'Détérioration: augmentation des excédents de ' . number_format($moyenneVariation, 0, ',', ' ') . ' F CFA par jour'
            ];
        } elseif ($moyenneVariation < -1000) {
            return [
                'tendance' => 'amelioration', // Les écarts deviennent plus NÉGATIFS (manquants augmentent)
                'message' => 'Amélioration: augmentation des manquants de ' . number_format(abs($moyenneVariation), 0, ',', ' ') . ' F CFA par jour'
            ];
        } else {
            return [
                'tendance' => 'stable',
                'message' => 'Stabilité: les écarts sont relativement stables'
            ];
        }
    }

    /**
     * Générer PDF de rapport
     */
    public function generateReportPdf(Request $request)
    {
        $user = Auth::user();
        $periode = $request->input('periode', 'weekly');
        $jours = $request->input('jours', 7);
        
        // Calculer les dates
        $endDate = Carbon::now();
        $startDate = match($periode) {
            'daily' => $endDate->copy()->startOfDay(),
            'weekly' => $endDate->copy()->subDays(7),
            'monthly' => $endDate->copy()->subDays(30),
            'custom' => $endDate->copy()->subDays($jours),
            default => $endDate->copy()->subDays(7),
        };
        
        // Récupérer les shifts FILTRÉS PAR STATION
        $query = ShiftSaisie::with(['pompeDetails', 'depenses', 'station'])
            ->where('user_id', auth()->id())
            ->whereBetween('date_shift', [$startDate, $endDate]);
        
        // Les gérants voient seulement leurs saisies de leur station
        if ($user->isManager()) {
            $query->where('station_id', $user->station_id);
        }
        
        $shifts = $query->orderBy('date_shift', 'desc')
            ->orderBy('shift', 'asc')
            ->get();
        
        // CORRECTION CRITIQUE : Calculer les statistiques AVEC LA FORMULE CORRECTE
        $totalVentes = $shifts->sum('total_ventes');
        $totalVersement = $shifts->sum('versement');
        $totalDepenses = $shifts->sum('total_depenses');
        
        // Calculer l'écart final selon la formule : Versement - (Ventes - Dépenses)
        $totalEcartFinal = $totalVersement - ($totalVentes - $totalDepenses);
        
        $stats = [
            'totalShifts' => $shifts->count(),
            'totalLitres' => $shifts->sum('total_litres'),
            'totalVentes' => $totalVentes,
            'totalVersement' => $totalVersement,
            'totalDepenses' => $totalDepenses,
            // CORRECTION : Ajouter l'écart final calculé correctement
            'totalEcartFinal' => $totalEcartFinal,
        ];
        
        // Calculer par carburant
        $byFuel = [];
        foreach ($shifts as $shift) {
            foreach ($shift->pompeDetails as $detail) {
                $fuelType = $detail->carburant;
                if (!isset($byFuel[$fuelType])) {
                    $byFuel[$fuelType] = [
                        'litres' => 0,
                        'montant' => 0,
                        'pompes' => []
                    ];
                }
                $byFuel[$fuelType]['litres'] += $detail->litrage_vendu;
                $byFuel[$fuelType]['montant'] += $detail->montant_ventes;
                $byFuel[$fuelType]['pompes'][] = $detail->pompe_nom;
            }
        }
        
        // Calculer les pourcentages
        $totalLitres = $stats['totalLitres'];
        $totalMontant = $stats['totalVentes'];
        foreach ($byFuel as &$data) {
            $data['pourcentage_montant'] = $totalMontant > 0 ? round(($data['montant'] / $totalMontant) * 100, 2) : 0;
        }
        
        // Écarts journaliers
        $ecartsJournaliers = [];
        foreach ($shifts as $shift) {
            $date = $shift->date_shift->format('Y-m-d');
            if (!isset($ecartsJournaliers[$date])) {
                $ecartsJournaliers[$date] = [
                    'date_format' => $shift->date_shift->format('d/m'),
                    'total_ecart_final' => 0,
                ];
            }
            // CORRECTION : Utiliser la formule correcte
            $ecartFinal = $shift->versement - ($shift->total_ventes - $shift->total_depenses);
            $ecartsJournaliers[$date]['total_ecart_final'] += $ecartFinal;
        }
        
        // Passer toutes les variables à la vue
        $pdf = PDF::loadView('pdf.shift-report', [
            'periode' => $periode,
            'jours' => $jours,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'stats' => $stats,
            'byFuel' => $byFuel,
            'ecartsJournaliers' => $ecartsJournaliers,
            'shifts' => $shifts,
            'user' => $user
        ]);
        
        $filename = 'rapport-' . $periode . '-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Générer PDF d'un shift spécifique (alias)
     */
    public function generateShiftPdf($id)
    {
        return $this->generatePdf($id);
    }

    /**
     * Export PDF des rapports (ancienne méthode)
     */
    public function exportReports(Request $request)
    {
        $user = Auth::user();
        $periode = $request->periode ?? 'daily';

        $query = ShiftSaisie::with(['pompeDetails', 'depenses', 'station'])
            ->where('user_id', auth()->id());

        // Les gérants voient seulement leurs saisies de leur station
        if ($user->isManager()) {
            $query->where('station_id', $user->station_id);
        }

        if ($periode === 'daily') {
            $query->whereDate('date_shift', today());
        } elseif ($periode === 'weekly') {
            $query->whereBetween('date_shift', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($periode === 'monthly') {
            $query->whereMonth('date_shift', now()->month)
                  ->whereYear('date_shift', now()->year);
        }

        $shifts = $query->orderBy('date_shift', 'desc')->get();

        $pdf = PDF::loadView('pdf.reports', [
            'shifts' => $shifts,
            'periode' => $periode,
            'user' => auth()->user()
        ]);

        return $pdf->download("rapport-$periode.pdf");
    }

    /**
     * Helper pour les icônes de dépenses
     */
    private function getDepenseIcon($type)
    {
        return match($type) {
            'carburant_vehicule' => 'fas fa-car',
            'nourriture' => 'fas fa-utensils',
            'maintenance' => 'fas fa-tools',
            'achat_divers' => 'fas fa-shopping-cart',
            'frais_transport' => 'fas fa-bus',
            default => 'fas fa-receipt'
        };
    }
}