<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Station;
use App\Models\ShiftSaisie;
use App\Models\StockMovement;
use App\Models\TankLevel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PdfReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Générer un rapport PDF pour une station spécifique
     */
      public function stationReport($stationId = null)
    {
        try {
            Log::info('=== DÉBUT GÉNÉRATION PDF STATION ===');
            
            // 1. Détermination de la Station
            $stationId = ($stationId && $stationId !== 'null') ? $stationId : session('station_id');
            $station = $stationId ? Station::with('manager')->find($stationId) : null;

            // 2. Gestion des Dates (Priorité : Request > Session > Mois en cours)
            $startDateStr = request('start_date') ?: session('start_date', Carbon::now()->startOfMonth()->toDateString());
            $endDateStr = request('end_date') ?: session('end_date', Carbon::now()->toDateString());

            $startDate = Carbon::parse($startDateStr)->startOfDay();
            $endDate = Carbon::parse($endDateStr)->endOfDay();
            
            // 3. Récupération des données réelles (Shifts validés)
            $shiftsQuery = ShiftSaisie::where('statut', 'valide')
                ->whereBetween('date_shift', [$startDate, $endDate]);
                
            if ($station) {
                $shiftsQuery->where('station_id', $station->id);
            }
            
            $shifts = $shiftsQuery->orderBy('date_shift', 'desc')->get();

            // 4. Calcul des Statistiques
            $stats = [
                'total_sales' => $shifts->sum('total_ventes'),
                'total_litres' => $shifts->sum('total_litres'),
                'shift_count' => $shifts->count(),
                'average_sales_per_shift' => $shifts->count() > 0 ? $shifts->sum('total_ventes') / $shifts->count() : 0,
                'average_ecart' => $shifts->sum('ecart_final'),
                'total_depenses' => $shifts->sum('total_depenses'),
            ];

            // 5. Calcul des Ventes par Carburant - SIMPLIFIÉ (sans vérification de colonnes)
            $salesByFuel = [
                'super' => 0,
                'gasoil' => 0,
            ];

            // VERSION SIMPLIFIÉE - Utiliser directement les données disponibles
            Log::info('Calcul des ventes par carburant');
            
            // Essayer d'abord avec les colonnes directes si elles existent
            $firstShift = $shifts->first();
            if ($firstShift) {
                // Vérifier par réflexion au lieu d'appeler columnExists
                $shiftAttributes = $firstShift->getAttributes();
                $hasMontantSuper = array_key_exists('montant_super', $shiftAttributes);
                $hasMontantGazole = array_key_exists('montant_gazole', $shiftAttributes);
                
                if ($hasMontantSuper && $hasMontantGazole) {
                    $salesByFuel = [
                        'super' => $shifts->sum('montant_super'),
                        'gasoil' => $shifts->sum('montant_gazole'),
                    ];
                    Log::info('Calcul via colonnes directes', $salesByFuel);
                } else {
                    // Méthode via les relations
                    foreach ($shifts as $shift) {
                        if (!$shift->relationLoaded('pompeDetails')) {
                            $shift->load('pompeDetails');
                        }
                        
                        if ($shift->pompeDetails && $shift->pompeDetails->count() > 0) {
                            foreach ($shift->pompeDetails as $detail) {
                                $fuelType = strtolower($detail->fuel_type ?? '');
                                $amount = $detail->montant ?? 0;
                                
                                if (in_array($fuelType, ['super', 'essence', 'sp95', 'sp98'])) {
                                    $salesByFuel['super'] += $amount;
                                } elseif (in_array($fuelType, ['gasoil', 'gazole', 'diesel'])) {
                                    $salesByFuel['gasoil'] += $amount;
                                }
                            }
                        }
                    }
                    Log::info('Calcul via relations', $salesByFuel);
                }
            }

            // Si toujours 0, utiliser une répartition proportionnelle
            if ($salesByFuel['super'] == 0 && $salesByFuel['gasoil'] == 0 && $stats['total_sales'] > 0) {
                $salesByFuel = [
                    'super' => $stats['total_sales'] * 0.6,
                    'gasoil' => $stats['total_sales'] * 0.4,
                ];
                Log::info('Utilisation de répartition par défaut', $salesByFuel);
            }

            // 6. Stocks Actuels (Via StockMovement)
            $currentStocks = [
                'super' => StockMovement::currentStock('super', $station ? $station->id : null),
                'gasoil' => StockMovement::currentStock('gasoil', $station ? $station->id : null),
            ];

            // 7. Préparation pour la vue
            $data = [
                'station' => $station,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'generatedAt' => now(),
                'generatedBy' => Auth::user()->name,
                'logoPath' => public_path('images/odysse.jpeg'),
                'stats' => $stats,
                'salesByFuel' => $salesByFuel,
                'currentStocks' => $currentStocks,
                'shifts' => $shifts,
            ];

            // 8. Génération du PDF
            if (view()->exists('pdf.station-report')) {
                $pdf = Pdf::loadView('pdf.station-report', $data);
            } else {
                Log::error('Vue pdf.station-report introuvable');
                return response()->json(['error' => 'Le fichier de vue PDF est manquant dans resources/views/pdf/station-report.blade.php'], 404);
            }

            $pdf->setPaper('A4', 'portrait');
            
            $filename = ($station ? "rapport-" . str_replace(' ', '_', $station->nom) : "rapport-global") . "-" . now()->format('dmY') . ".pdf";

            Log::info('=== FIN GÉNÉRATION PDF STATION ===');
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('ERREUR PDF STATION: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Erreur lors de la génération: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Rapport de réconciliation PDF - AVEC DONNÉES RÉELLES
     */
    public function reconciliationReport(Request $request)
{
    try {
        Log::info('=== DÉBUT RECONCILIATION REPORT ===');
        
        // 1. Récupération des paramètres
        $stationId = $request->input('station_id') ?: session('station_id');
        $fuelType = $request->input('fuel_type');
        $startDate = $request->input('start_date') ?: session('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date') ?: session('end_date', Carbon::now()->endOfMonth());
        
        // Conversion en objets Carbon
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();
        
        $station = $stationId ? Station::find($stationId) : null;
        
        Log::info('Paramètres réconciliation', [
            'station_id' => $stationId,
            'station' => $station ? $station->nom : 'Toutes stations',
            'fuel_type' => $fuelType ?: 'Tous',
            'periode' => $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y')
        ]);
        
        // 2. Récupérer uniquement réceptions et ventes (EXCLURE les ajustements)
        $query = StockMovement::with(['recorder', 'station'])
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->whereIn('movement_type', ['reception', 'vente']) // ← EXCLUT LES AJUSTEMENTS
            ->orderBy('movement_date', 'asc')
            ->orderBy('created_at', 'asc');
        
        if ($stationId) {
            $query->where('station_id', $stationId);
        }
        
        if ($fuelType) {
            if ($fuelType === 'gasoil') {
                $query->where(function($q) {
                    $q->where('fuel_type', 'gasoil')
                      ->orWhere('fuel_type', 'gazole');
                });
            } else {
                $query->where('fuel_type', $fuelType);
            }
        }
        
        $movements = $query->get();
        
        // 3. Récupérer les ajustements séparément pour calculs
        $adjustmentsQuery = StockMovement::whereBetween('movement_date', [$startDate, $endDate])
            ->where('movement_type', 'ajustement');
            
        if ($stationId) {
            $adjustmentsQuery->where('station_id', $stationId);
        }
        
        if ($fuelType) {
            if ($fuelType === 'gasoil') {
                $adjustmentsQuery->where(function($q) {
                    $q->where('fuel_type', 'gasoil')
                      ->orWhere('fuel_type', 'gazole');
                });
            } else {
                $adjustmentsQuery->where('fuel_type', $fuelType);
            }
        }
        
        $adjustments = $adjustmentsQuery->get();
        
        Log::info('Données récupérées', [
            'mouvements_principaux' => $movements->count(),
            'ajustements' => $adjustments->count()
        ]);
        
        // 4. Calcul des totaux SANS ajustements
        $totals = [
            'receptions' => $movements->where('movement_type', 'reception')->sum('quantity'),
            'sales' => abs($movements->where('movement_type', 'vente')->sum('quantity')),
            'amount' => $movements->sum('total_amount'),
        ];
        
        // 5. Calcul du stock théorique (sans ajustements)
        $stockTheorique = $totals['receptions'] - $totals['sales'];
        
        // 6. Calcul des ajustements séparément
        $adjustmentsData = [
            'total' => $adjustments->sum('quantity'),
            'positifs' => $adjustments->where('quantity', '>', 0)->sum('quantity'),
            'negatifs' => abs($adjustments->where('quantity', '<', 0)->sum('quantity')),
            'montant' => $adjustments->sum('total_amount'),
            'count' => $adjustments->count(),
        ];
        
        // 7. Stock final théorique avec ajustements
        $stockFinalAvecAjustements = $stockTheorique + $adjustmentsData['total'];
        
        Log::info('Calculs terminés', [
            'receptions' => $totals['receptions'],
            'ventes' => $totals['sales'],
            'stock_theorique' => $stockTheorique,
            'ajustements_totaux' => $adjustmentsData['total'],
            'stock_final' => $stockFinalAvecAjustements
        ]);
        
        // 8. Préparation des données pour la vue
        $data = [
            'station' => $station,
            'fuelType' => $fuelType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            
            // Mouvements principaux (sans ajustements)
            'movements' => $movements,
            
            // Totaux
            'totals' => $totals,
            'stockTheorique' => $stockTheorique,
            
            // Données ajustements (pour informations complémentaires)
            'adjustmentsData' => $adjustmentsData,
            'stockFinalAvecAjustements' => $stockFinalAvecAjustements,
            
            // Métadonnées
            'generatedAt' => now(),
            'generatedBy' => Auth::check() ? Auth::user()->name : 'Système',
        ];
        
        // 9. Génération du PDF
        $pdf = Pdf::loadView('pdf.reconciliation-report', $data);
        $pdf->setPaper('A4', 'landscape');
        
        // 10. Nom du fichier
        $filename = "reconciliation-" . 
                   ($station ? $station->code . '-' : 'global-') . 
                   ($fuelType ? $fuelType . '-' : '') . 
                   Carbon::now()->format('Y-m-d') . ".pdf";
        
        Log::info('=== FIN RECONCILIATION REPORT ===');
        
        return $pdf->download($filename);
        
    } catch (\Exception $e) {
        Log::error('ERREUR RECONCILIATION REPORT', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la génération du rapport',
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Rapport d'inventaire PDF - AVEC DONNÉES RÉELLES
     */
public function inventoryReport(Request $request)
{
    try {
        Log::info('=== DÉBUT INVENTORY REPORT ===');
        
        // 1. Récupération des paramètres
        $stationId = $request->input('station_id') ?: session('station_id');
        $startDate = Carbon::parse($request->input('start_date') ?: session('start_date', now()->subDays(30)));
        $endDate = Carbon::parse($request->input('end_date') ?: session('end_date', now()));
        
        // 2. Récupération de la station si spécifiée
        $station = $stationId ? Station::with('manager')->find($stationId) : null;
        
        Log::info('Paramètres inventaire', [
            'station_id' => $stationId,
            'station_nom' => $station ? $station->nom : 'Toutes stations',
            'periode' => $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y')
        ]);
        
        // 3. Récupération des jaugeages avec filtres
        $query = TankLevel::with(['measurer', 'station'])
            ->whereBetween('measurement_date', [$startDate, $endDate])
            ->orderBy('station_id', 'asc')
            ->orderBy('measurement_date', 'desc');
            
        if ($stationId) {
            $query->where('station_id', $stationId);
        }
        
        $tankLevels = $query->get();
        
        // 4. Groupement par station pour comparaison (surtout si toutes stations)
        $stationsData = [];
        $allStationsGrouped = $tankLevels->groupBy('station_id');
        
        foreach ($allStationsGrouped as $stId => $levels) {
            $stationData = Station::find($stId);
            if ($stationData) {
                // Calcul des statistiques par station
                $statsByStation = [
                    'station' => $stationData,
                    'count' => $levels->count(),
                    'total_theoretical' => $levels->sum('theoretical_stock'),
                    'total_physical' => $levels->sum('physical_stock'),
                    'avg_difference_per_mille' => $levels->avg(function($item) {
                        return $this->calculatePerMille($item);
                    }) ?? 0,
                    'max_difference_per_mille' => $levels->max(function($item) {
                        return abs($this->calculatePerMille($item));
                    }) ?? 0,
                    'discrepancies' => $levels->filter(function($item) {
                        $perMille = $this->calculatePerMille($item);
                        return abs($perMille) > 5;
                    })->count(),
                    'levels' => $levels->sortByDesc('measurement_date'),
                ];
                
                // Calcul par type de carburant dans la station
                $byFuelType = $levels->groupBy('fuel_type');
                $fuelStats = [];
                foreach ($byFuelType as $fuel => $fuelLevels) {
                    $fuelStats[$fuel] = [
                        'count' => $fuelLevels->count(),
                        'avg_per_mille' => $fuelLevels->avg(function($item) {
                            return $this->calculatePerMille($item);
                        }) ?? 0,
                        'max_per_mille' => $fuelLevels->max(function($item) {
                            return abs($this->calculatePerMille($item));
                        }) ?? 0,
                    ];
                }
                
                $stationsData[$stId] = array_merge($statsByStation, ['fuel_stats' => $fuelStats]);
            }
        }
        
        // 5. Calcul des statistiques globales
        $globalStats = [
            'total_measurements' => $tankLevels->count(),
            'total_stations' => count($stationsData),
            'average_difference_per_mille' => $tankLevels->avg(function($item) {
                return $this->calculatePerMille($item);
            }) ?? 0,
            'max_difference_per_mille' => $tankLevels->max(function($item) {
                return abs($this->calculatePerMille($item));
            }) ?? 0,
            'min_difference_per_mille' => $tankLevels->min(function($item) {
                return $this->calculatePerMille($item);
            }) ?? 0,
            'discrepancies' => $tankLevels->filter(function($item) {
                $perMille = $this->calculatePerMille($item);
                return abs($perMille) > 5;
            })->count(),
        ];
        
        // 6. Comparaison entre stations (si plus d'une station)
        $comparisonData = [];
        if (count($stationsData) > 1) {
            foreach ($stationsData as $stId => $data) {
                $comparisonData[] = [
                    'station' => $data['station'],
                    'avg_per_mille' => $data['avg_difference_per_mille'],
                    'discrepancies' => $data['discrepancies'],
                    'count' => $data['count'],
                ];
            }
            
            // Trier par écart moyen (du meilleur au pire)
            usort($comparisonData, function($a, $b) {
                return abs($a['avg_per_mille']) <=> abs($b['avg_per_mille']);
            });
        }
        
        Log::info('Statistiques inventaire', [
            'total_mesures' => $globalStats['total_measurements'],
            'stations' => $globalStats['total_stations'],
            'ecart_moyen_‰' => $globalStats['average_difference_per_mille'],
            'anomalies' => $globalStats['discrepancies']
        ]);
        
        // 7. Préparation des données pour la vue
        $data = [
            'tankLevels' => $tankLevels,
            'station' => $station,
            'stats' => $globalStats,
            'stationsData' => $stationsData,
            'comparisonData' => $comparisonData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now(),
            'generatedBy' => Auth::user()->name,
            'showAllStations' => !$stationId,
        ];
        
        // 8. Génération du PDF
        $pdf = Pdf::loadView('pdf.inventory-report', $data);
        $pdf->setPaper('A4', 'landscape');
        
        // 9. Nom du fichier
        $filename = "inventaire-" . 
                   ($station ? $station->code . '-' : 'global-') . 
                   Carbon::now()->format('Y-m-d') . ".pdf";
        
        Log::info('=== FIN INVENTORY REPORT ===');
        
        return $pdf->download($filename);
        
    } catch (\Exception $e) {
        Log::error('ERREUR INVENTORY PDF: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Impossible de générer le PDF d\'inventaire.',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Helper pour calculer l'écart en pour mille (‰)
 */
private function calculatePerMille($tankLevel)
{
    if (!$tankLevel || $tankLevel->theoretical_stock <= 0) {
        return 0;
    }
    
    // Si différence déjà calculée
    if (isset($tankLevel->difference) && $tankLevel->difference_percentage) {
        // Convertir % en ‰
        return $tankLevel->difference_percentage * 10;
    }
    
    // Calculer manuellement
    $diffLiters = ($tankLevel->physical_stock - $tankLevel->theoretical_stock);
    return ($diffLiters / $tankLevel->theoretical_stock) * 1000;
}
    
    /**
     * Rapport des ventes par pompiste - AVEC DONNÉES RÉELLES
     */
    public function salesByPumpReport(Request $request)
    {
        try {
            Log::info('=== DÉBUT SALES BY PUMP REPORT ===');
            
            $stationId = $request->input('station_id');
            $startDate = Carbon::parse($request->input('start_date') ?: session('start_date', now()->subDays(30)));
            $endDate = Carbon::parse($request->input('end_date') ?: session('end_date', now()));
            
            $station = $stationId ? Station::find($stationId) : null;
            
            // Récupérer les ventes RÉELLES
            $query = ShiftSaisie::with(['user', 'station'])
                ->where('statut', 'valide')
                ->whereBetween('date_shift', [$startDate, $endDate])
                ->orderBy('date_shift', 'desc');
            
            if ($stationId) {
                $query->where('station_id', $stationId);
            }
            
            $sales = $query->get();
            
            Log::info('Ventes trouvées', ['count' => $sales->count()]);
            
            // Regrouper par pompiste RÉEL
            $salesByPump = $sales->groupBy('user_id')->map(function ($shifts, $userId) {
                $user = $shifts->first()->user;
                return [
                    'user' => $user,
                    'total_sales' => $shifts->sum('total_ventes'),
                    'total_litres' => $shifts->sum('total_litres'),
                    'shift_count' => $shifts->count(),
                    'average_sales' => $shifts->avg('total_ventes'),
                    'average_ecart' => $shifts->avg('ecart_final'),
                ];
            })->sortByDesc('total_sales');
            
            $data = [
                'station' => $station,
                'salesByPump' => $salesByPump,
                'totalSales' => $sales->sum('total_ventes'),
                'totalLitres' => $sales->sum('total_litres'),
                'startDate' => Carbon::parse($startDate),
                'endDate' => Carbon::parse($endDate),
                'generatedAt' => now(),
                'generatedBy' => Auth::check() ? Auth::user()->name : 'Système',
            ];
            
            Log::info('Ventes par pompiste', ['count' => $salesByPump->count()]);
            
            $pdf = Pdf::loadView('pdf.sales-by-pump', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $filename = "sales-by-pump-" . ($station ? $station->code . '-' : '') . Carbon::now()->format('Y-m-d') . ".pdf";
            
            Log::info('=== FIN SALES BY PUMP REPORT ===');
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('ERREUR SALES BY PUMP REPORT', ['error' => $e->getMessage()]);
            return response('<h1>Erreur PDF Ventes par Pompiste</h1><p>' . $e->getMessage() . '</p>', 500);
        }
    }
    
    /**
     * Rapport individuel pour un shift - AVEC DONNÉES RÉELLES
     */
  public function shiftReport($shiftId)
{
    try {
        Log::info('=== DÉBUT SHIFT REPORT ===', ['shiftId' => $shiftId]);
        
        // Chercher d'abord avec relations
        $shift = ShiftSaisie::with(['pompeDetails', 'depenses', 'user', 'station'])
            ->find($shiftId);
        
        // Si shift non trouvé, chercher sans relations ou créer un test
        if (!$shift) {
            Log::warning('Shift non trouvé, recherche sans relations', ['shiftId' => $shiftId]);
            $shift = ShiftSaisie::find($shiftId);
            
            if (!$shift) {
                Log::error('Shift inexistant', ['shiftId' => $shiftId]);
                return $this->generateSimplePdf('Rapport Shift', "Shift #$shiftId non trouvé");
            }
        }
        
        Log::info('Shift trouvé', [
            'id' => $shift->id,
            'station' => $shift->station ? $shift->station->nom : 'N/A',
            'responsable' => $shift->responsable,
        ]);
        
        // S'assurer que les relations existent
        if (!$shift->relationLoaded('pompeDetails')) {
            $shift->load('pompeDetails');
        }
        if (!$shift->relationLoaded('depenses')) {
            $shift->load('depenses');
        }
        if (!$shift->relationLoaded('station')) {
            $shift->load('station');
        
        
        
        }
        
        
        
        $data = [
            'shift' => $shift,
            'user' => Auth::check() ? Auth::user() : (object)['name' => 'Système'],
        ];
        
        // Essayer différentes vues
        $viewName = null;
        $viewsToTry = ['pdf.shift.report', 'pdf.shift-individual', 'pdf.shift-report.blade.php'];
        
        foreach ($viewsToTry as $view) {
            if (view()->exists($view)) {
                $viewName = $view;
                break;
            }
        }
        
        if (!$viewName) {
            Log::error('Aucune vue shift trouvée');
            return $this->generateShiftTestPdf($shift);
        }
        
        Log::info('Utilisation de la vue', ['view' => $viewName]);
        
        $pdf = Pdf::loadView($viewName, $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = "shift-report-{$shift->id}-" . Carbon::now()->format('Y-m-d') . ".pdf";
        
        Log::info('=== FIN SHIFT REPORT ===');
        
        return $pdf->download($filename);
        
    } catch (\Exception $e) {
        Log::error('ERREUR SHIFT REPORT', [
            'shiftId' => $shiftId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return $this->generateErrorPdf('Shift', $e->getMessage());
    }
}
  public function generateReports(Request $request)
    {
        // 1. On définit la période globale (par défaut : le mois en cours)
        $startDate = Carbon::parse($request->get('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->get('end_date', now()->endOfDay()));

        // 2. On prépare les données communes
        $commonData = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now(),
            'generatedBy' => auth()->user()->name,
            'station' => Station::find($request->station_id),
        ];

        // 3. Application de la période aux différentes requêtes
        // Pour la Réconciliation (Mouvements)
        $movements = StockMovement::whereBetween('movement_date', [$startDate, $endDate])
                         ->where('station_id', $request->station_id)
                         ->get();

        // Pour l'Inventaire (Jaugeages)
        $tankLevels = TankLevel::whereBetween('measurement_date', [$startDate, $endDate])
                           ->where('station_id', $request->station_id)
                           ->get();

        // Pour les Ventes (Performance/Pompiste)
        $sales = ShiftSaisie::whereBetween('date_shift', [$startDate, $endDate])
                 ->where('station_id', $request->station_id)
                 ->where('statut', 'valide')
                 ->get();

        // 4. On envoie les MÊMES dates à chaque vue
        // Exemple pour le PDF de Réconciliation :
        $pdf = Pdf::loadView('reports.reconciliation', array_merge($commonData, [
            'movements' => $movements,
            'totals' => $this->calculateTotals($movements)
        ]));
        
        return $pdf->download('rapport.pdf');
    }
    
    /**
     * Calcule les totaux pour les mouvements
     */
    private function calculateTotals($movements)
    {
        return [
            'receptions' => $movements->where('movement_type', 'reception')->sum('quantity'),
            'sales' => abs($movements->where('movement_type', 'vente')->sum('quantity')),
            'adjustments' => $movements->where('movement_type', 'ajustement')->sum('quantity'),
            'amount' => $movements->sum('total_amount'),
        ];
    }
}