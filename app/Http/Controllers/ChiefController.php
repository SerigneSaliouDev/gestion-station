<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\TankLevel;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Models\ShiftSaisie;
use App\Models\ShiftPompeDetail;
use App\Models\Station;
use App\Models\User;
use App\Models\Tank;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ChiefController extends Controller
{
    
   public function dashboard(Request $request)
    {
        try {
            // 1. Récupération de TOUTES les stations
            $allStations = Station::with('manager')->orderBy('nom')->get();
            $stationId = $request->input('station_id');
            
            \Log::info('Dashboard - Stations trouvées: ' . $allStations->count());
            
            // Gestion de la station sélectionnée
            $selectedStation = null;
            if ($stationId) {
                $selectedStation = Station::with('manager')->find($stationId);
                
                // Sauvegarder l'ID dans la session
                session([
                    'selected_station_id' => $stationId,
                    'dashboard_station_id' => $stationId
                ]);
            } else {
                session()->forget(['selected_station_id', 'dashboard_station_id']);
            }
            
            // Récupérer les stocks pour TOUTES les stations
            $stationStocks = $this->getAllStationStocks();
            
            // Récupérer les stocks pour la station sélectionnée
            $selectedStationStocks = $selectedStation ? 
                $this->getSelectedStationStocks($selectedStation->id) : null;
            
            // ==================== VENTES LIÉES AUX CUVES ====================
            
            // 1. Ventes depuis les cuves (depuis stock_movements)
            $salesFromTanks = $this->getSalesFromTanks($selectedStation);
            
            // 2. Ventes récentes depuis les cuves
            $recentSalesFromTanks = $this->getRecentSalesFromTanks($selectedStation, 10);
            
            // 3. Stock des cuves
            $tankStocks = $this->getTankStocks($selectedStation);
            
            // ==================== DONNÉES DÉTAILLÉES PAR JOUR ====================
            
            // 1. Shifts par jour du mois en cours
            $daysInMonth = now()->daysInMonth;
            $shiftsByDay = [];
            $salesByDayThisMonth = [];
            $currentYear = now()->year;
            $currentMonth = now()->month;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($currentYear, $currentMonth, $day);
                
                // Nombre de shifts par jour
                $shiftsQuery = ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                    return $q->where('station_id', $selectedStation->id);
                })->whereDate('date_shift', $date);
                
                $shiftsByDay[$day] = [
                    'date' => $date->format('d/m/Y'),
                    'count' => $shiftsQuery->count(),
                    'formatted_date' => $date->format('d/m')
                ];
                
                // Ventes par jour (shifts + tanks)
                $shiftSales = ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                        return $q->where('station_id', $selectedStation->id);
                    })->where('statut', 'valide')
                    ->whereDate('date_shift', $date)
                    ->sum('total_ventes') ?? 0;
                
                // CORRECTION:  StockMovement au lieu de Sale
                $tankSales = StockMovement::where('movement_type', 'vente')
                    ->when($selectedStation, function($q) use ($selectedStation) {
                        return $q->where('station_id', $selectedStation->id);
                    })
                    ->whereDate('movement_date', $date)
                    ->sum('total_amount') ?? 0;
                
                $salesByDayThisMonth[$day] = [
                    'date' => $date->format('d/m/Y'),
                    'sales' => $shiftSales + $tankSales,
                    'shift_sales' => $shiftSales,
                    'tank_sales' => $tankSales,
                    'formatted_date' => $date->format('d/m')
                ];
            }
            
            // 2. Statistiques détaillées des shifts
            $shiftsStats = [
                'today_by_shift' => [
                    'matin' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                            return $q->where('station_id', $selectedStation->id);
                        })->whereDate('date_shift', today())
                        ->where('shift', 'matin')
                        ->count(),
                    'soir' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                            return $q->where('station_id', $selectedStation->id);
                        })->whereDate('date_shift', today())
                        ->where('shift', 'soir')
                        ->count(),
                    'nuit' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                            return $q->where('station_id', $selectedStation->id);
                        })->whereDate('date_shift', today())
                        ->where('shift', 'nuit')
                        ->count(),
                ],
                'month_by_shift' => [
                    'matin' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                            return $q->where('station_id', $selectedStation->id);
                        })->whereMonth('date_shift', now()->month)
                        ->where('shift', 'matin')
                        ->count(),
                    'soir' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                            return $q->where('station_id', $selectedStation->id);
                        })->whereMonth('date_shift', now()->month)
                        ->where('shift', 'soir')
                        ->count(),
                    'nuit' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                            return $q->where('station_id', $selectedStation->id);
                        })->whereMonth('date_shift', now()->month)
                        ->where('shift', 'nuit')
                        ->count(),
                ],
                'total_by_shift_month' => [
                    'matin_sales' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                            return $q->where('station_id', $selectedStation->id);
                        })->where('statut', 'valide')
                        ->whereMonth('date_shift', now()->month)
                        ->where('shift', 'matin')
                        ->sum('total_ventes') ?? 0,
                    'soir_sales' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                            return $q->where('station_id', $selectedStation->id);
                        })->where('statut', 'valide')
                        ->whereMonth('date_shift', now()->month)
                        ->where('shift', 'soir')
                        ->sum('total_ventes') ?? 0,
                    'nuit_sales' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                            return $q->where('station_id', $selectedStation->id);
                        })->where('statut', 'valide')
                        ->whereMonth('date_shift', now()->month)
                        ->where('shift', 'nuit')
                        ->sum('total_ventes') ?? 0,
                ]
            ];
            
            // 3. Totaux pour les statistiques
            $totalShiftsMonth = array_sum(array_column(array_values($shiftsByDay), 'count'));
            
            // Calculer les ventes totales (shifts + tanks)
            $totalShiftSalesMonth = array_sum(array_column(array_values($salesByDayThisMonth), 'shift_sales'));
            $totalTankSalesMonth = array_sum(array_column(array_values($salesByDayThisMonth), 'tank_sales'));
            $totalSalesMonth = $totalShiftSalesMonth + $totalTankSalesMonth;
            
            $averageDailySales = 0;
            $daysWithSales = count(array_filter(array_values($salesByDayThisMonth), function($v) { 
                return $v['sales'] > 0; 
            }));
            
            if ($daysWithSales > 0) {
                $averageDailySales = $totalSalesMonth / $daysWithSales;
            }
            
            
            
            // Statistiques de base
            $pendingQuery = ShiftSaisie::where('statut', 'en_attente');
            $salesQuery = ShiftSaisie::where('statut', 'valide');
            
            if ($selectedStation) {
                $pendingQuery->where('station_id', $selectedStation->id);
                $salesQuery->where('station_id', $selectedStation->id);
            }
            
            $pendingValidationsCount = $pendingQuery->count();
            $todayShiftSales = $salesQuery->whereDate('date_shift', Carbon::today())->sum('total_ventes');
            
            // CORRECTION: Ventes tanks aujourd'hui depuis stock_movements
            $todayTankSales = StockMovement::where('movement_type', 'vente')
                ->whereDate('movement_date', Carbon::today())
                ->when($selectedStation, function($q) use ($selectedStation) {
                    return $q->where('station_id', $selectedStation->id);
                })
                ->sum('total_amount') ?? 0;
            
            $todaySales = $todayShiftSales + $todayTankSales;
            
            // Statistiques générales
            $statsQuery = ShiftSaisie::where('statut', 'valide');
            
            if ($selectedStation) {
                $statsQuery->where('station_id', $selectedStation->id);
            }
            
            // Utiliser les données déjà calculées
            $stats = [
                'avg_ecart' => $statsQuery->avg('ecart_final') ?? 0,
                'total_shifts_today' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                    return $q->where('station_id', $selectedStation->id);
                })->whereDate('date_shift', Carbon::today())->count(),
                'total_shifts_month' => $totalShiftsMonth,
                'total_sales_month' => $totalSalesMonth,
                'total_shift_sales_month' => $totalShiftSalesMonth,
                'total_tank_sales_month' => $totalTankSalesMonth,
                'today_sales' => $todaySales,
                'today_shift_sales' => $todayShiftSales,
                'today_tank_sales' => $todayTankSales,
                'average_daily_sales' => $averageDailySales,
                'days_with_sales' => $daysWithSales,
                'tank_stock_super' => $tankStocks['super']['current'] ?? 0,
                'tank_stock_gazole' => $tankStocks['gasoil']['current'] ?? 0,
                'tank_sales_today' => $salesFromTanks['today_total'],
                'tank_sales_month' => $salesFromTanks['month_total']
            ];
            
            // Shifts par statut
            $shiftsByStatus = [
                'en_attente' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                    return $q->where('station_id', $selectedStation->id);
                })->where('statut', 'en_attente')->count(),
                
                'valide' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                    return $q->where('station_id', $selectedStation->id);
                })->where('statut', 'valide')->count(),
                
                'rejete' => ShiftSaisie::when($selectedStation, function($q) use ($selectedStation) {
                    return $q->where('station_id', $selectedStation->id);
                })->where('statut', 'rejete')->count(),
            ];
            
            // Ventes des 7 derniers jours (SHIFTS + TANKS)
            $salesLast7Days = $this->getCombinedSalesLast7Days($selectedStation);
            
            // Données spécifiques à la station sélectionnée
            $stationSpecificData = null;
            if ($selectedStation) {
                $stationSpecificData = [
                    'today_sales' => $todaySales,
                    'today_shift_sales' => $todayShiftSales,
                    'today_tank_sales' => $todayTankSales,
                    'pending_validations' => ShiftSaisie::where('station_id', $selectedStation->id)
                        ->where('statut', 'en_attente')
                        ->count(),
                    'total_sales_month' => $totalSalesMonth,
                    'avg_ecart' => ShiftSaisie::where('station_id', $selectedStation->id)
                        ->where('statut', 'valide')
                        ->whereMonth('date_shift', Carbon::now()->month)
                        ->avg('ecart_final') ?? 0,
                    'recent_shifts' => ShiftSaisie::where('station_id', $selectedStation->id)
                        ->with('station')
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get(),
                    'recent_tank_sales' => $recentSalesFromTanks,
                    'shifts_by_day' => $shiftsByDay,
                    'sales_by_day' => $salesByDayThisMonth,
                    'shifts_stats' => $shiftsStats,
                    'tank_stocks' => $tankStocks,
                    'sales_from_tanks' => $salesFromTanks
                ];
            }
            
            // CORRECTION: Top 5 des stations avec StockMovement
            $topStations = Station::with('manager')->get()->map(function($station) {
                // Ventes shifts
                $shiftSales = ShiftSaisie::where('station_id', $station->id)
                    ->where('statut', 'valide')
                    ->whereMonth('date_shift', Carbon::now()->month)
                    ->sum('total_ventes') ?? 0;
                
                // CORRECTION: Ventes tanks depuis stock_movements
                $tankSales = StockMovement::where('movement_type', 'vente')
                    ->where('station_id', $station->id)
                    ->whereMonth('movement_date', Carbon::now()->month)
                    ->sum('total_amount') ?? 0;
                
                return [
                    'id' => $station->id,
                    'nom' => $station->nom,
                    'code' => $station->code,
                    'manager' => $station->manager,
                    'total_sales' => $shiftSales + $tankSales,
                    'shift_sales' => $shiftSales,
                    'tank_sales' => $tankSales,
                    'shifts_count' => ShiftSaisie::where('station_id', $station->id)
                        ->where('statut', 'valide')
                        ->whereMonth('date_shift', Carbon::now()->month)
                        ->count(),
                    'avg_ecart' => ShiftSaisie::where('station_id', $station->id)
                        ->where('statut', 'valide')
                        ->whereMonth('date_shift', Carbon::now()->month)
                        ->avg('ecart_final') ?? 0,
                ];
            })->sortByDesc('total_sales')->take(5)->values();
            
            // Dernières validations en attente
            $recentValidations = ShiftSaisie::with(['station', 'user'])
                ->where('statut', 'en_attente')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
            
            // Derniers shifts récents
            $recentShiftsQuery = ShiftSaisie::with(['station', 'user'])
                ->orderBy('created_at', 'desc')
                ->take(10);
            
            if ($selectedStation) {
                $recentShiftsQuery->where('station_id', $selectedStation->id);
            }
            
            $recentShifts = $recentShiftsQuery->get();
            
            // CORRECTION: Performance des stations avec StockMovement
            $stationsPerformance = $allStations->map(function($station) {
                // Ventes shifts aujourd'hui
                $todayShiftSales = ShiftSaisie::where('station_id', $station->id)
                    ->where('statut', 'valide')
                    ->whereDate('date_shift', Carbon::today())
                    ->sum('total_ventes') ?? 0;
                
                // CORRECTION: Ventes tanks aujourd'hui depuis stock_movements
                $todayTankSales = StockMovement::where('movement_type', 'vente')
                    ->where('station_id', $station->id)
                    ->whereDate('movement_date', Carbon::today())
                    ->sum('total_amount') ?? 0;
                
                // Ventes shifts ce mois
                $monthShiftSales = ShiftSaisie::where('station_id', $station->id)
                    ->where('statut', 'valide')
                    ->whereMonth('date_shift', Carbon::now()->month)
                    ->sum('total_ventes') ?? 0;
                
                // CORRECTION: Ventes tanks ce mois depuis stock_movements
                $monthTankSales = StockMovement::where('movement_type', 'vente')
                    ->where('station_id', $station->id)
                    ->whereMonth('movement_date', Carbon::now()->month)
                    ->sum('total_amount') ?? 0;
                
                $pending_validations = ShiftSaisie::where('station_id', $station->id)
                    ->where('statut', 'en_attente')
                    ->count();
                
                // Dernière activité
                $lastShift = ShiftSaisie::where('station_id', $station->id)
                    ->latest('created_at')
                    ->first();
                
                // CORRECTION: Dernière vente depuis stock_movements
                $lastSale = StockMovement::where('movement_type', 'vente')
                    ->where('station_id', $station->id)
                    ->latest('movement_date')
                    ->first();
                
                $lastActivity = $lastShift && $lastSale 
                    ? ($lastShift->created_at > $lastSale->movement_date ? $lastShift->created_at : $lastSale->movement_date)
                    : ($lastShift ? $lastShift->created_at : ($lastSale ? $lastSale->movement_date : null));
                
                return [
                    'id' => $station->id,
                    'nom' => $station->nom,
                    'code' => $station->code,
                    'manager' => $station->manager,
                    'today_sales' => $todayShiftSales + $todayTankSales,
                    'today_shift_sales' => $todayShiftSales,
                    'today_tank_sales' => $todayTankSales,
                    'pending_validations' => $pending_validations ?? 0,
                    'total_sales_month' => $monthShiftSales + $monthTankSales,
                    'month_shift_sales' => $monthShiftSales,
                    'month_tank_sales' => $monthTankSales,
                    'last_activity' => $lastActivity ? Carbon::parse($lastActivity)->format('d/m/Y H:i') : 'Aucune activité',
                    'is_active' => $station->statut == 'actif'
                ];
            });
            
            // Statistiques générales supplémentaires
            $activeStationsCount = Station::where('statut', 'actif')->count();
            $stationsWithoutManager = Station::doesntHave('manager')->count();
            $totalStations = $allStations->count();
            
            // Alertes
            $alerts = [];
            if ($pendingValidationsCount > 10) {
                $alerts[] = "{$pendingValidationsCount} validations en attente nécessitent votre attention!";
            }
            
            if ($stationsWithoutManager > 0) {
                $alerts[] = "{$stationsWithoutManager} station(s) sans manager assigné!";
            }
            
            // Vérifier les stocks bas
            if ($selectedStation && isset($tankStocks['super']['fill_percentage']) && $tankStocks['super']['fill_percentage'] < 20) {
                $alerts[] = "Stock SUPER faible ({$tankStocks['super']['fill_percentage']}%) pour {$selectedStation->nom}";
            }
            
            if ($selectedStation && isset($tankStocks['gasoil']['fill_percentage']) && $tankStocks['gasoil']['fill_percentage'] < 20) {
                $alerts[] = "Stock GAZOLE faible ({$tankStocks['gasoil']['fill_percentage']}%) pour {$selectedStation->nom}";
            }
            
            \Log::info('Dashboard - Préparation des données terminée', [
                'stations' => $allStations->count(),
                'selected_station' => $selectedStation ? $selectedStation->id : null,
                'pending' => $pendingValidationsCount,
                'total_shifts_month' => $totalShiftsMonth,
                'total_sales_month' => $totalSalesMonth,
                'tank_sales_month' => $totalTankSalesMonth
            ]);
            
            return view('chief.dashboard', compact(
                'allStations',
                'selectedStation',
                'stationId',
                'pendingValidationsCount',
                'activeStationsCount',
                'todaySales',
                'stats',
                'shiftsByStatus',
                'salesLast7Days',
                'stationSpecificData',
                'topStations',
                'recentValidations',
                'recentShifts',
                'stationsPerformance',
                'alerts',
                'totalStations',
                'stationsWithoutManager',
                'shiftsByDay',
                'salesByDayThisMonth',
                'shiftsStats',
                'totalShiftsMonth',
                'totalSalesMonth',
                'stationStocks',
                'selectedStationStocks',
                // Données des cuves
                'salesFromTanks',
                'recentSalesFromTanks',
                'tankStocks'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Erreur dans dashboard: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('chief.dashboard', [
                'allStations' => collect(),
                'error' => 'Erreur de chargement: ' . $e->getMessage(),
                'stationStocks' => [],
                'salesFromTanks' => [],
                'recentSalesFromTanks' => collect(),
                'tankStocks' => []
            ]);
        }
    }
private function getSalesFromTanks($selectedStation = null)
{
    try {
        $query = StockMovement::where('movement_type', 'vente');
        
        if ($selectedStation) {
            $query->where('station_id', $selectedStation->id);
        }
        
        // 1. Ventes du mois (uniquement par mois, pas par année)
        $monthQuery = clone $query;
        $monthSales = $monthQuery->whereMonth('movement_date', Carbon::now()->month);
        
        
        // 2. Ventes du jour (attention si vos données sont en 2026)
        $todayQuery = clone $query;
        $currentYear = Carbon::now()->year;
        
        // Si vos données sont en 2026, ajustez la date
        if ($currentYear == 2025) {
            
            $todaySales = $todayQuery->whereDate('movement_date', '2026-01-15');
        } else {
            $todaySales = $todayQuery->whereDate('movement_date', Carbon::today());
        }
        
        // 3. Total toutes ventes
        $allTimeTotal = $query->sum('total_amount');
        
        return [
            'month_total' => $monthSales->sum('total_amount') ?? 0,
            'month_litres' => $monthSales->sum(DB::raw('ABS(quantity)')) ?? 0,
            'month_count' => $monthSales->count() ?? 0,
            'today_total' => $todaySales->sum('total_amount') ?? 0,
            'today_litres' => $todaySales->sum(DB::raw('ABS(quantity)')) ?? 0,
            'today_count' => $todaySales->count() ?? 0,
            'all_time_total' => $allTimeTotal ?? 0
        ];
        
    } catch (\Exception $e) {
        \Log::error('Error in getSalesFromTanks', [
            'error' => $e->getMessage()
        ]);
        
        return $this->emptySalesResult();
    }
}

/**
 * Récupérer les ventes récentes depuis les tanks
 */
private function getRecentSalesFromTanks($selectedStation = null, $limit = 10)
{
    try {
        \Log::info('DEBUG - getRecentSalesFromTanks - Using stock_movements');
        
        $query = StockMovement::with(['station', 'tank'])
            ->where('movement_type', 'vente')
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit);
        
        if ($selectedStation) {
            $query->where('station_id', $selectedStation->id);
        }
        
        $sales = $query->get();
        
        \Log::info('DEBUG - Recent sales from stock_movements', [
            'found_sales' => $sales->count(),
            'sales_ids' => $sales->pluck('id')->toArray()
        ]);
        
        return $sales->map(function($sale) {
            return [
                'id' => $sale->id,
                'date' => $sale->movement_date ? $sale->movement_date->format('d/m/Y H:i') : 'N/A',
                'station' => $sale->station ? $sale->station->nom : 'N/A',
                'tank' => $sale->tank ? 'Cuve ' . $sale->tank->number : ($sale->tank_number ? 'Cuve ' . $sale->tank_number : 'N/A'),
                'fuel_type' => $sale->fuel_type ?? 'N/A',
                'quantity' => abs($sale->quantity) ?? 0, // Convertir en positif
                'amount' => $sale->total_amount ?? 0,
                'customer' => $sale->customer_name ?? 'N/A',
                'payment_method' => $sale->payment_method ?? 'N/A',
                'badge_class' => $this->getSaleBadgeClass($sale->payment_method ?? 'cash')
            ];
        });
        
    } catch (\Exception $e) {
        \Log::error('Error in getRecentSalesFromTanks', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return collect();
    }
}
private function getTankStocks($selectedStation = null)
{
    $query = Tank::query();
    
    if ($selectedStation) {
        $query->where('station_id', $selectedStation->id);
    }
    
    $tanks = $query->get();
    
    $stocks = [
        'total_capacity' => $tanks->sum('capacity'),
        'total_current' => $tanks->sum('current_volume'),
        'fill_percentage' => $tanks->sum('capacity') > 0 ? 
            ($tanks->sum('current_volume') / $tanks->sum('capacity')) * 100 : 0,
        'tanks_count' => $tanks->count()
    ];
    
    // Grouper par type de carburant
    foreach ($tanks as $tank) {
        $fuelType = strtolower($tank->fuel_type);
        
        // Normaliser les types
        if (in_array($fuelType, ['gazole', 'gasoil', ])) {
            $fuelType = 'gasoil';
        }
        
        if (!isset($stocks[$fuelType])) {
            $stocks[$fuelType] = [
                'capacity' => 0,
                'current' => 0,
                'tanks' => [],
                'fill_percentage' => 0,
                'available_capacity' => 0
            ];
        }
        
        $stocks[$fuelType]['capacity'] += $tank->capacity;
        $stocks[$fuelType]['current'] += $tank->current_volume;
        $stocks[$fuelType]['tanks'][] = [
            'number' => $tank->number,
            'fuel_type' => $tank->fuel_type,
            'current_volume' => $tank->current_volume,
            'capacity' => $tank->capacity,
            'fill_percentage' => $tank->capacity > 0 ? ($tank->current_volume / $tank->capacity) * 100 : 0
        ];
    }
    
    // Calculer les pourcentages
    foreach ($stocks as $key => &$data) {
        if (is_array($data) && isset($data['capacity']) && $data['capacity'] > 0) {
            $data['fill_percentage'] = ($data['current'] / $data['capacity']) * 100;
            $data['available_capacity'] = $data['capacity'] - $data['current'];
        }
    }
    
    return $stocks;
}
   private function getCombinedSalesLast7Days($selectedStation = null)
{
   
    $endDate = Carbon::now(); // Aujourd'hui
    $startDate = $endDate->copy()->subDays(6); // Il y a 7 jours
    
    $dates = [];
    for ($i = 0; $i < 7; $i++) {
        $date = $startDate->copy()->addDays($i);
        $dates[] = [
            'date' => $date->format('d/m'),
            'full_date' => $date->format('Y-m-d'),
            'shift_sales' => 0,
            'tank_sales' => 0,
            'total_sales' => 0,
            'shift_litres' => 0,
            'tank_litres' => 0,
            'total_litres' => 0
        ];
    }
    
    \Log::info('7 Days Sales - Date Range', [
        'start_date' => $startDate->format('Y-m-d'),
        'end_date' => $endDate->format('Y-m-d'),
        'selected_station' => $selectedStation ? $selectedStation->id : 'all'
    ]);
    
    // 1. Ventes des shifts
    $shiftQuery = ShiftSaisie::where('statut', 'valide')
        ->whereBetween('date_shift', [$startDate, $endDate]);
    
    if ($selectedStation) {
        $shiftQuery->where('station_id', $selectedStation->id);
    }
    
    $shiftSales = $shiftQuery->selectRaw('DATE(date_shift) as date, SUM(total_ventes) as sales, SUM(total_litres) as litres')
        ->groupBy('date')
        ->get()
        ->keyBy('date');
    
    \Log::info('Shift Sales Data', [
        'count' => $shiftSales->count(),
        'dates' => $shiftSales->keys()->toArray()
    ]);
    
    // 2. Ventes des tanks
    $tankQuery = StockMovement::where('movement_type', 'vente')
        ->whereBetween('movement_date', [$startDate, $endDate]);
    
    if ($selectedStation) {
        $tankQuery->where('station_id', $selectedStation->id);
    }
    
    $tankSales = $tankQuery->selectRaw('DATE(movement_date) as date, SUM(total_amount) as sales, SUM(ABS(quantity)) as litres')
        ->groupBy('date')
        ->get()
        ->keyBy('date');
    
    \Log::info('Tank Sales Data', [
        'count' => $tankSales->count(),
        'dates' => $tankSales->keys()->toArray(),
        'sample' => $tankSales->take(3)->map(function($item) {
            return [
                'date' => $item->date,
                'sales' => $item->sales,
                'litres' => $item->litres
            ];
        })->values()
    ]);
    
    // 3. Combiner les données
    foreach ($dates as &$dayData) {
        $date = $dayData['full_date'];
        
        $shiftData = $shiftSales[$date] ?? null;
        $tankData = $tankSales[$date] ?? null;
        
        $dayData['shift_sales'] = $shiftData ? (float)$shiftData->sales : 0;
        $dayData['shift_litres'] = $shiftData ? (float)$shiftData->litres : 0;
        $dayData['tank_sales'] = $tankData ? (float)$tankData->sales : 0;
        $dayData['tank_litres'] = $tankData ? (float)$tankData->litres : 0;
        $dayData['total_sales'] = $dayData['shift_sales'] + $dayData['tank_sales'];
        $dayData['total_litres'] = $dayData['shift_litres'] + $dayData['tank_litres'];
        
        \Log::info('Day Data ' . $date, [
            'shift_sales' => $dayData['shift_sales'],
            'tank_sales' => $dayData['tank_sales'],
            'total_sales' => $dayData['total_sales']
        ]);
    }
    
    return $dates;
}
private function getSaleBadgeClass($paymentMethod)
{
    return match($paymentMethod) {
        'cash' => 'badge-success',
        'card' => 'badge-primary',
        'mobile_money' => 'badge-warning',
        'credit' => 'badge-info',
        default => 'badge-secondary'
    };
}

/**
 * Déterminer la performance d'un pompiste
 */
    private function determinerPerformancePompiste($avgPerShift, $avgEcart)
    {
        if ($avgPerShift == 0) return 'Inactif';
        
        // Score basé sur la moyenne par shift et l'écart
        $score = ($avgPerShift / 10000) - (abs($avgEcart) / 1000);
        
        if ($score > 50) return 'Excellent';
        if ($score > 35) return 'Très bon';
        if ($score > 20) return 'Bon';
        if ($score > 10) return 'Moyen';
        return 'À améliorer';
    }

    private function normalizeStockDataForView($stockData)
{
    if (!$stockData || !is_array($stockData)) {
        return [
            'super' => [
                'current_stock' => 0,
                'physical_stock' => null,
                'received' => 0,
                'sold' => 0,
                'total_sales' => 0,
                'difference' => null,
                'difference_percentage' => null,
            ],
            'gasoil' => [
                'current_stock' => 0,
                'physical_stock' => null,
                'received' => 0,
                'sold' => 0,
                'total_sales' => 0,
                'difference' => null,
                'difference_percentage' => null,
            ]
        ];
    }
    
    return $stockData;
}

public function salesEvolution(Request $request)
{
    try {
        $period = $request->input('period', 30);
        $stationId = $request->input('station_id');
        
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays($period - 1);
        
        // Générer toutes les dates de la période
        $dates = [];
        for ($i = 0; $i < $period; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dates[$date->format('Y-m-d')] = [
                'shift_sales' => 0,
                'tank_sales' => 0,
                'shift_litres' => 0,
                'tank_litres' => 0
            ];
        }
        
        // ==================== 1. VENTES DES SHIFTS ====================
        $shiftQuery = ShiftSaisie::select(
                DB::raw('DATE(date_shift) as date'),
                DB::raw('SUM(total_ventes) as sales'),
                DB::raw('SUM(total_litres) as litres'),
                DB::raw('COUNT(*) as shifts_count')
            )
            ->where('statut', 'valide')
            ->whereBetween('date_shift', [$startDate, $endDate]);
        
        // Filtrer par station si spécifié
        if ($stationId) {
            $shiftQuery->where('station_id', $stationId);
        }
        
        $shiftSalesData = $shiftQuery->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        
        // ==================== 2. VENTES DES TANKS ====================
        $tankQuery = StockMovement::select(
                DB::raw('DATE(movement_date) as date'),
                DB::raw('SUM(total_amount) as sales'),
                DB::raw('SUM(ABS(quantity)) as litres'),
                DB::raw('COUNT(*) as sales_count')
            )
            ->where('movement_type', 'vente')
            ->whereBetween('movement_date', [$startDate, $endDate]);
        
        // Filtrer par station si spécifié
        if ($stationId) {
            $tankQuery->where('station_id', $stationId);
        }
        
        $tankSalesData = $tankQuery->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        
        \Log::info('Sales Evolution Debug', [
            'period' => $period,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'shift_sales_count' => $shiftSalesData->count(),
            'tank_sales_count' => $tankSalesData->count(),
            'shift_dates' => $shiftSalesData->keys()->toArray(),
            'tank_dates' => $tankSalesData->keys()->toArray()
        ]);
        
        // Fusionner les données
        $labels = [];
        $sales = [];        // Ventes totales (FCFA)
        $shiftSales = [];   // Ventes shifts seulement
        $tankSales = [];    // Ventes tanks seulement
        $litres = [];       // Litres totaux
        $shifts = [];       // Nombre de shifts
        
        foreach ($dates as $date => $values) {
            $carbonDate = Carbon::parse($date);
            $labels[] = $carbonDate->format('d/m');
            
            // Données shifts
            $shiftData = $shiftSalesData[$date] ?? null;
            $shiftSalesValue = $shiftData ? (float)$shiftData->sales : 0;
            $shiftLitresValue = $shiftData ? (float)$shiftData->litres : 0;
            $shiftCount = $shiftData ? (int)$shiftData->shifts_count : 0;
            
            // Données tanks
            $tankData = $tankSalesData[$date] ?? null;
            $tankSalesValue = $tankData ? (float)$tankData->sales : 0;
            $tankLitresValue = $tankData ? (float)$tankData->litres : 0;
            $tankCount = $tankData ? (int)$tankData->sales_count : 0;
            
            // Totaux
            $totalSales = $shiftSalesValue + $tankSalesValue;
            $totalLitres = $shiftLitresValue + $tankLitresValue;
            
            $sales[] = $totalSales;
            $shiftSales[] = $shiftSalesValue;
            $tankSales[] = $tankSalesValue;
            $litres[] = $totalLitres;
            $shifts[] = $shiftCount;
        }
        
        // Calculer les statistiques récapitulatives
        $totalSales = array_sum($sales);
        $totalShiftSales = array_sum($shiftSales);
        $totalTankSales = array_sum($tankSales);
        $totalLitres = array_sum($litres);
        $averageSales = $totalSales / $period;
        $maxSales = max($sales);
        $minSales = min($sales);
        
        // Trouver la date du pic de vente
        $maxIndex = array_keys($sales, $maxSales)[0] ?? 0;
        $peakDate = $labels[$maxIndex] ?? 'N/A';
        
        // Jours avec ventes
        $daysWithSales = count(array_filter($sales, function($v) { return $v > 0; }));
        
        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'sales' => $sales,
                'shift_sales' => $shiftSales,
                'tank_sales' => $tankSales,
                'litres' => $litres,
                'shifts' => $shifts
            ],
            'stats' => [
                'total_sales' => $totalSales,
                'total_shift_sales' => $totalShiftSales,
                'total_tank_sales' => $totalTankSales,
                'total_litres' => $totalLitres,
                'average_sales' => round($averageSales, 2),
                'max_sales' => $maxSales,
                'min_sales' => $minSales,
                'peak_date' => $peakDate,
                'total_shifts' => array_sum($shifts),
                'days_with_sales' => $daysWithSales,
                'average_daily_sales' => $daysWithSales > 0 ? $totalSales / $daysWithSales : 0
            ],
            'period' => $period,
            'station_id' => $stationId,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'timestamp' => now()->format('d/m/Y H:i:s')
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error in salesEvolution: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

    public function validations(Request $request)
    {
        // Récupérer la station sélectionnée depuis la session ou la requête
        $stationId = $request->input('station_id') ?? session('selected_station_id');
        
        $query = ShiftSaisie::with(['pompeDetails', 'depenses', 'station', 'user'])
            ->where('statut', 'en_attente');
        
        // Filtrer par station si spécifiée
        if ($stationId) {
            $query->where('station_id', $stationId);
            // Sauvegarder dans la session pour persistance
            session(['selected_station_id' => $stationId]);
        }
        
        $saisies = $query->orderBy('date_shift', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Récupérer toutes les stations pour le filtre
        $allStations = Station::orderBy('nom')->get();
        
        $stationsCount = Station::count();
        $managersCount = User::role('manager')->count();
        $alertesCount = 0;
        
        return view('chief.validations', compact(
            'saisies', 
            'allStations', 
            'stationId', 
            'stationsCount', 
            'managersCount', 
            'alertesCount'
        ));
    }
    /**
 * Méthode de debug pour voir ce qui se passe avec les stocks
 */

public function debugStockData(Request $request)
{
    $stationId = $request->input('station_id');
    $station = null;
    
    if ($stationId) {
        $station = Station::find($stationId);
    }
    
    // Test de la méthode getStationStockData
    $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
    $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
    
    $stockData = [];
    if ($stationId) {
        $stockData[$stationId] = $this->getStationStockData($stationId, $startDate, $endDate);
    }
    
    // Test direct de StockMovement
    $stockTests = [
        'gasoil' => [
            'current_stock' => StockMovement::currentStock('gasoil', $stationId),
            'movements_count' => StockMovement::where('fuel_type', 'gasoil')
                ->where('station_id', $stationId)
                ->count(),
            'last_movement' => StockMovement::where('fuel_type', 'gasoil')
                ->where('station_id', $stationId)
                ->orderBy('movement_date', 'desc')
                ->first(),
        ],
        'super' => [
            'current_stock' => StockMovement::currentStock('super', $stationId),
            'movements_count' => StockMovement::where('fuel_type', 'super')
                ->where('station_id', $stationId)
                ->count(),
        ]
    ];
    
    return response()->json([
        'debug' => [
            'station_id' => $stationId,
            'station' => $station ? $station->nom : null,
            'stock_data_method_result' => $stockData,
            'stock_tests' => $stockTests,
            'tank_levels' => TankLevel::where('station_id', $stationId)
                ->orderBy('measurement_date', 'desc')
                ->take(5)
                ->get()
                ->map(function($t) {
                    return [
                        'id' => $t->id,
                        'fuel_type' => $t->fuel_type,
                        'physical_stock' => $t->physical_stock,
                        'theoretical_stock' => $t->theoretical_stock,
                        'difference_percentage' => $t->difference_percentage,
                        'date' => $t->measurement_date,
                    ];
                }),
        ]
    ]);
}

public function showValidation($id)
{
    try {
        $saisie = ShiftSaisie::with(['station', 'user', 'validateur', 'pompeDetails', 'depenses'])
            ->find($id);
        
        if (!$saisie) {
            return redirect()->route('chief.validations')
                ->with('error', 'Saisie non trouvée.');
        }
        
        if ($saisie->statut !== 'en_attente') {
            $statutMessages = [
                'valide' => 'Cette saisie a déjà été validée.',
                'rejete' => 'Cette saisie a été rejetée.',
            ];
            
            return redirect()->route('chief.validations')
                ->with('warning', $statutMessages[$saisie->statut] ?? 'Cette saisie a déjà été traitée.');
        }
        
        return view('chief.validation-show', compact('saisie'));
        
    } catch (\Exception $e) {
        \Log::error('Erreur showValidation: ' . $e->getMessage());
        return redirect()->route('chief.validations')
            ->with('error', 'Erreur lors du chargement.');
    }
}
      public function validerSaisie(Request $request, $id)
    {
        $validated = $request->validate([
            'comment' => 'nullable|string|max:500'
        ]);
        
        $saisie = ShiftSaisie::findOrFail($id);
        
        // Mettre à jour le statut
        $saisie->update([
            'statut' => 'valide',
            'valide_par' => Auth::id(),
            'valide_le' => now(),
            'commentaire_validation' => $validated['comment'] ?? null,
        ]);
        
        return redirect()->route('chief.validations')
            ->with('success', 'Saisie validée avec succès!');
    }
    public function pendingCount(Request $request)
    {
        try {
            $query = ShiftSaisie::where('statut', 'en_attente');
            
            // Filtrer par station si spécifié
            if ($request->has('station_id')) {
                $query->where('station_id', $request->station_id);
            }
            
            $count = $query->count();
            
            return response()->json([
                'success' => true,
                'count' => $count,
                'timestamp' => now()->format('H:i:s')
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in pendingCount: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }
    

    public function rejeterSaisie(Request $request, $id)
    {
        $shift = ShiftSaisie::findOrFail($id);
        $validated = $request->validate(['raison_rejet' => 'required|string|max:500']);
        
        $shift->update([
            'statut' => 'rejete',
            'rejete_par' => Auth::id(),
            'rejete_le' => now(),
            'raison_rejet' => $validated['raison_rejet'],
        ]);
        
        return redirect()->route('chief.validations')->with('success', 'Saisie rejetée!');
    }

public function rapportsStations(Request $request)
{
    $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
    $endDate = $request->input('end_date', now()->format('Y-m-d'));
    $stationId = $request->input('station_id');
    
    \Log::info('ChiefController - rapportsStations', [
        'station_id_request' => $stationId,
        'start_date' => $startDate,
        'end_date' => $endDate
    ]);
    
    session([
        'start_date' => $startDate,
        'end_date' => $endDate,
        'station_id' => $stationId
    ]);
    
    // Récupérer TOUTES les stations pour le filtre
    $allStations = Station::all();
    
    // Récupérer les stations pour l'affichage
    $stations = $stationId 
        ? Station::where('id', $stationId)->get()
        : $allStations;
    
    // Calculer les performances des stations
    $stationPerformances = [];
    $totalVentes = 0;
    
    foreach ($stations as $station) {
        $performance = $this->calculateStationPerformance($station->id, $startDate, $endDate);
        $stationPerformances[] = $performance;
        $totalVentes += $performance['total_ventes'];
    }
    
    // Calculer les pourcentages
    foreach ($stationPerformances as &$performance) {
        $performance['pourcentage_total'] = $totalVentes > 0 
            ? ($performance['total_ventes'] / $totalVentes) * 100 
            : 0;
    }
    
    // Récupérer les shifts récents
    $recentShifts = ShiftSaisie::with('station')
        ->when($stationId, function($query) use ($stationId) {
            return $query->where('station_id', $stationId);
        })
        ->whereBetween('date_shift', [$startDate, $endDate])
        ->orderBy('date_shift', 'desc')
        ->paginate(10);
    
    // Statistiques globales
    $stats = $this->calculateGlobalStats($startDate, $endDate, $stationId);
    
    // Données de stock
    $stockData = [];
    foreach ($stations as $station) {
        $stockData[$station->id] = $this->getStationStockData($station->id, $startDate, $endDate);
    }
    
    // Répartition par carburant
    $fuelDistribution = $this->calculateFuelDistribution($startDate, $endDate, $stationId);
    
    
    $tankLevelsData = [];
    foreach ($stations as $station) {
        // Récupérer les jaugeages pour chaque station
        $tankLevelsData[$station->id] = \App\Models\TankLevel::with('tank')
            ->where('station_id', $station->id)
            ->whereBetween('measurement_date', [$startDate, $endDate])
            ->orderBy('measurement_date', 'desc')
            ->get()
            ->groupBy('fuel_type');
    }
    
    \Log::info('ChiefController - Données envoyées à la vue rapports', [
        'station_id' => $stationId,
        'stations_count' => $stations->count(),
        'all_stations_count' => $allStations->count(),
        'stock_data_keys' => array_keys($stockData),
        'tank_levels_data' => array_keys($tankLevelsData),
        'recent_shifts_count' => $recentShifts->count(),
        'has_station_id' => !empty($stationId)
    ]);
    
    // Retourner la vue avec TOUTES les données
    return view('chief.stations.rapports', compact(
        'stations', 
        'allStations', 
        'stationPerformances', 
        'recentShifts', 
        'stats', 
        'startDate', 
        'endDate', 
        'stationId', 
        'fuelDistribution', 
        'stockData',
        'tankLevelsData' 
    ));
}
    private function prepareDepensesData($saisie)
    {
        if (!$saisie->depenses || $saisie->depenses->isEmpty()) {
            return $this->getDefaultDepensesData();
        }
        
        $depenses = [];
        foreach ($saisie->depenses as $depense) {
            $depenses[] = [
                'description' => $depense->description ?? $depense->type_depense,
                'montant' => $depense->montant,
                'justificatif' => $depense->justificatif
            ];
        }
        
        return $depenses;
    }
        private function calculateStats($saisie)
    {
        return [
            'ecart_final' => $saisie->ecart_final ?? 0,
            'net_amount' => ($saisie->total_ventes ?? 0) - ($saisie->total_depenses ?? 0),
            'created_ago' => $saisie->created_at ? $saisie->created_at->diffForHumans() : 'N/A',
            'validated_ago' => $saisie->valide_le ? \Carbon\Carbon::parse($saisie->valide_le)->diffForHumans() : null
        ];
    }
    

    /**
     * Calculer la performance d'une station
     */
    private function calculateStationPerformance($stationId, $startDate, $endDate)
{
    $station = Station::find($stationId);
    
    if (!$station) {
        return [
            'station' => null,
            'total_ventes' => 0,
            'total_litres' => 0,
            'shifts_count' => 0,
            'avg_ecart' => 0,
            'par_carburant' => [
                'gasoil' => ['montant' => 0, 'litres' => 0, 'pourcentage' => 0],
                'super' => ['montant' => 0, 'litres' => 0, 'pourcentage' => 0]
            ],
            'performance' => 'Inactif'
        ];
    }
    
    // Récupérer les shifts de la période
    $shifts = ShiftSaisie::where('station_id', $stationId)
        ->whereBetween('date_shift', [$startDate, $endDate])
        ->where('statut', 'valide')
        ->get();
    
    // CORRECTION: Récupérer les ventes des tanks
    $tankSales = StockMovement::where('station_id', $stationId)
        ->where('movement_type', 'vente')
        ->whereBetween('movement_date', [$startDate, $endDate])
        ->get();
    
    // Totaux shifts
    $shiftVentes = $shifts->sum('total_ventes');
    $shiftLitres = $shifts->sum('total_litres');
    $shiftsCount = $shifts->count();
    
    // Totaux tanks
    $tankVentes = $tankSales->sum('total_amount');
    $tankLitres = $tankSales->sum(DB::raw('ABS(quantity)'));
    
    // Totaux combinés
    $totalVentes = $shiftVentes + $tankVentes;
    $totalLitres = $shiftLitres + $tankLitres;
    
    // Écart moyen (seulement pour les shifts)
    $avgEcart = $shiftsCount > 0 ? $shifts->avg('ecart_final') : 0;
    
    // Ventes par carburant (combiner shifts et tanks)
    $parCarburant = [
        'gasoil' => [
            'montant' => $shifts->sum('montant_gazole') + 
                        $tankSales->where('fuel_type', 'gasoil')->sum('total_amount'),
            'litres' => $shifts->sum('litres_gazole') + 
                       $tankSales->where('fuel_type', 'gasoil')->sum(DB::raw('ABS(quantity)')),
            'pourcentage' => $totalVentes > 0 ? 
                (($shifts->sum('montant_gazole') + $tankSales->where('fuel_type', 'gasoil')->sum('total_amount')) / $totalVentes) * 100 : 0
        ],

        'super' => [
            'montant' => $shifts->sum('montant_super') + 
                        $tankSales->where('fuel_type', 'super')->sum('total_amount'),
            'litres' => $shifts->sum('litres_super') + 
                       $tankSales->where('fuel_type', 'super')->sum(DB::raw('ABS(quantity)')),
            'pourcentage' => $totalVentes > 0 ? 
                (($shifts->sum('montant_super') + $tankSales->where('fuel_type', 'super')->sum('total_amount')) / $totalVentes) * 100 : 0
        ],

        'essence pirogue' => [
            'montant' => $shifts->sum('montant_essence pirogue') + 
                        $tankSales->where('fuel_type', 'essence pirogue')->sum('total_amount'),
            'litres' => $shifts->sum('litres_essence pirogue') + 
                       $tankSales->where('fuel_type', 'essence pirogue')->sum(DB::raw('ABS(quantity)')),
            'pourcentage' => $totalVentes > 0 ? 
                (($shifts->sum('montant_essence pirogue') + $tankSales->where('fuel_type', 'essence pirogue')->sum('total_amount')) / $totalVentes) * 100 : 0
        ],


   
    ];
    
    // Score de performance
    $performance = $this->calculatePerformanceScore($totalVentes, $shiftsCount, $avgEcart);
    
    return [
        'station' => $station,
        'total_ventes' => $totalVentes,
        'total_litres' => $totalLitres,
        'shifts_count' => $shiftsCount,
        'avg_ecart' => $avgEcart,
        'par_carburant' => $parCarburant,
        'performance' => $performance,
        'debug' => [
            'shift_ventes' => $shiftVentes,
            'tank_ventes' => $tankVentes,
            'shift_count' => $shiftsCount,
            'tank_count' => $tankSales->count()
        ]
    ];
}

    /**
     * Calculer le score de performance
     */
    private function calculatePerformanceScore($totalVentes, $shiftsCount, $avgEcart)
    {
        if ($shiftsCount == 0) return 'Inactif';
        
        // Score basé sur les ventes moyennes par shift
        $avgPerShift = $totalVentes / $shiftsCount;
        
        if ($avgPerShift > 500000) {
            return 'Excellent';
        } elseif ($avgPerShift > 300000) {
            return 'Très bon';
        } elseif ($avgPerShift > 150000) {
            return 'Bon';
        } else {
            return 'À améliorer';
        }
    }

    /**
     * Calculer les statistiques globales
     */
   private function calculateGlobalStats($startDate, $endDate, $stationId = null)
{
    // Ventes shifts
    $shiftQuery = ShiftSaisie::whereBetween('date_shift', [$startDate, $endDate])
        ->where('statut', 'valide');
        
    // Ventes tanks
    $tankQuery = StockMovement::where('movement_type', 'vente')
        ->whereBetween('movement_date', [$startDate, $endDate]);
        
    if ($stationId) {
        $shiftQuery->where('station_id', $stationId);
        $tankQuery->where('station_id', $stationId);
    }
    
    $shifts = $shiftQuery->get();
    $tankSales = $tankQuery->get();
    
    // Stations
    $stations = $stationId 
        ? Station::where('id', $stationId)->get()
        : Station::all();
    
    // Totaux combinés
    $totalShiftVentes = $shifts->sum('total_ventes');
    $totalTankVentes = $tankSales->sum('total_amount');
    $totalVentes = $totalShiftVentes + $totalTankVentes;
    
    // CORRECTION ICI: Calculer le volume total
    $shiftVolume = $shifts->sum('total_litres'); // Volume des shifts
    $tankVolume = $tankSales->sum(DB::raw('ABS(quantity)')); 
    $volumeTotal = $shiftVolume + $tankVolume; // Volume total
    
    \Log::info('Volume Total Debug', [
        'start_date' => $startDate,
        'end_date' => $endDate,
        'station_id' => $stationId,
        'shift_volume' => $shiftVolume,
        'tank_volume' => $tankVolume,
        'total_volume' => $volumeTotal,
        'shift_count' => $shifts->count(),
        'tank_count' => $tankSales->count()
    ]);
    
    // Meilleure station
    $bestStation = null;
    $bestStationVentes = 0;
    
    foreach ($stations as $station) {
        $stationShiftVentes = ShiftSaisie::where('station_id', $station->id)
            ->whereBetween('date_shift', [$startDate, $endDate])
            ->where('statut', 'valide')
            ->sum('total_ventes');
            
        $stationTankVentes = StockMovement::where('station_id', $station->id)
            ->where('movement_type', 'vente')
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->sum('total_amount');
            
        $stationVentes = $stationShiftVentes + $stationTankVentes;
            
        if ($stationVentes > $bestStationVentes) {
            $bestStationVentes = $stationVentes;
            $bestStation = $station;
        }
    }
    
    // Croissance vs période précédente
    $previousStartDate = Carbon::parse($startDate)->subDays(30)->format('Y-m-d');
    $previousEndDate = Carbon::parse($startDate)->subDay()->format('Y-m-d');
    
    $previousShifts = ShiftSaisie::whereBetween('date_shift', [$previousStartDate, $previousEndDate])
        ->where('statut', 'valide')
        ->when($stationId, function($q) use ($stationId) {
            return $q->where('station_id', $stationId);
        })
        ->sum('total_ventes');
        
    $previousTanks = StockMovement::where('movement_type', 'vente')
        ->whereBetween('movement_date', [$previousStartDate, $previousEndDate])
        ->when($stationId, function($q) use ($stationId) {
            return $q->where('station_id', $stationId);
        })
        ->sum('total_amount');
        
    $previousVentes = $previousShifts + $previousTanks;
    
    $croissance = $previousVentes > 0 
        ? (($totalVentes - $previousVentes) / $previousVentes) * 100 
        : ($totalVentes > 0 ? 100 : 0);
    
    // Stations actives (celles avec au moins un shift ou vente)
    $activeStations = 0;
    foreach ($stations as $station) {
        $hasShifts = ShiftSaisie::where('station_id', $station->id)
            ->whereBetween('date_shift', [$startDate, $endDate])
            ->where('statut', 'valide')
            ->exists();
            
        $hasTankSales = StockMovement::where('station_id', $station->id)
            ->where('movement_type', 'vente')
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->exists();
            
        if ($hasShifts || $hasTankSales) {
            $activeStations++;
        }
    }
    
    // Taux de remplissage (basé sur les capacités théoriques)
    $capaciteTotale = $stations->sum('capacite_super') + $stations->sum('capacite_gazole');
    $tauxRemplissage = $capaciteTotale > 0 
        ? ($volumeTotal / ($capaciteTotale * 30)) * 100 // Supposant 30 jours
        : 0;
    
    // Moyenne par station
    $moyenneStation = $activeStations > 0 ? $totalVentes / $activeStations : 0;
    
    return [
        'total_ventes' => $totalVentes,
        'volume_total' => $volumeTotal,
        'best_station' => $bestStation,
        'best_station_ventes' => $bestStationVentes,
        'croissance' => $croissance,
        'stations_actives' => $activeStations,
        'taux_remplissage' => $tauxRemplissage,
        'moyenne_station' => $moyenneStation,
        'debug' => [
            'shift_ventes' => $totalShiftVentes,
            'tank_ventes' => $totalTankVentes,
            'shift_volume' => $shiftVolume,
            'tank_volume' => $tankVolume,
            'capacite_totale' => $capaciteTotale
        ]
    ];
}

    /**
     * Calculer la répartition par carburant
     */
    private function calculateFuelDistribution($startDate, $endDate, $stationId = null)
{
    // Ventes shifts
    $shiftQuery = ShiftSaisie::whereBetween('date_shift', [$startDate, $endDate])
        ->where('statut', 'valide');
        
    // Ventes tanks
    $tankQuery = StockMovement::where('movement_type', 'vente')
        ->whereBetween('movement_date', [$startDate, $endDate]);
        
    if ($stationId) {
        $shiftQuery->where('station_id', $stationId);
        $tankQuery->where('station_id', $stationId);
    }
    
    $shifts = $shiftQuery->get();
    $tankSales = $tankQuery->get();
    
    // Totaux combinés
    $gasoilShift = $shifts->sum('montant_gazole');
    $superShift = $shifts->sum('montant_super');
    
    $gasoilTank = $tankSales->whereIn('fuel_type', ['gasoil', 'gazole'])->sum('total_amount');
    $superTank = $tankSales->where('fuel_type', 'super')->sum('total_amount');
    
    $gasoilTotal = $gasoilShift + $gasoilTank;
    $superTotal = $superShift + $superTank;
    $totalVentes = $gasoilTotal + $superTotal;
    
    if ($totalVentes == 0) {
        return [
            'gasoil' => 0,
            'super' => 0
        ];
    }
    
    return [
        'gasoil' => ($gasoilTotal / $totalVentes) * 100,
        'super' => ($superTotal / $totalVentes) * 100,
        'details' => [
            'gasoil_shift' => $gasoilShift,
            'gasoil_tank' => $gasoilTank,
            'super_shift' => $superShift,
            'super_tank' => $superTank,
            'total' => $totalVentes
        ]
    ];
}

      private function getStationStockData($stationId, $startDate, $endDate)
    {
        $fuelTypes = ['super', 'gasoil', 'essence pirogue'];
        $stockData = [];
        
        foreach ($fuelTypes as $fuelType) {
            // Stock initial au début de la période
            $initialStock = StockMovement::where(function($q) use ($fuelType) {
                    if ($fuelType === 'gasoil') {
                        $q->where('fuel_type', 'gasoil')
                          ->orWhere('fuel_type', 'gazole');
                    } else {
                        $q->where('fuel_type', $fuelType);
                    }
                })
                ->where('station_id', $stationId)
                ->where('movement_date', '<', $startDate)
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            $initialStockValue = $initialStock ? $initialStock->stock_after : 0;
            
            // Réceptions
            $receptions = StockMovement::where(function($q) use ($fuelType) {
                    if ($fuelType === 'gasoil') {
                        $q->where('fuel_type', 'gasoil')
                          ->orWhere('fuel_type', 'gazole');
                    } else {
                        $q->where('fuel_type', $fuelType);
                    }
                })
                ->where('station_id', $stationId)
                ->where('movement_type', 'reception')
                ->whereBetween('movement_date', [$startDate, $endDate])
                ->get();
            
            $totalReceived = $receptions->sum('quantity');
            $countReceptions = $receptions->count();
            
            // Ventes
            $sales = StockMovement::where(function($q) use ($fuelType) {
                    if ($fuelType === 'gasoil') {
                        $q->where('fuel_type', 'gasoil')
                          ->orWhere('fuel_type', 'gazole');
                    } else {
                        $q->where('fuel_type', $fuelType);
                    }
                })
                ->where('station_id', $stationId)
                ->where('movement_type', 'vente')
                ->whereBetween('movement_date', [$startDate, $endDate])
                ->get();
            
            $totalSold = abs($sales->sum('quantity'));
            $totalSalesAmount = $sales->sum('total_amount');
            $countSales = $sales->count();
            
            // Stock actuel (dernier mouvement)
            $currentStock = StockMovement::where(function($q) use ($fuelType) {
                    if ($fuelType === 'gasoil') {
                        $q->where('fuel_type', 'gasoil')
                          ->orWhere('fuel_type', 'gazole');
                    } else {
                        $q->where('fuel_type', $fuelType);
                    }
                })
                ->where('station_id', $stationId)
                ->where('movement_date', '<=', $endDate)
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            $currentStockValue = $currentStock ? $currentStock->stock_after : $initialStockValue;
            
            // Stock physique (dernier jaugeage)
            $latestTankLevel = TankLevel::where('station_id', $stationId)
                ->where(function($q) use ($fuelType) {
                    if ($fuelType === 'gasoil') {
                        $q->where('fuel_type', 'gasoil')
                          ->orWhere('fuel_type', 'gazole');
                    } else {
                        $q->where('fuel_type', $fuelType);
                    }
                })
                ->where('measurement_date', '<=', $endDate)
                ->orderBy('measurement_date', 'desc')
                ->first();
            
            $stockData[$fuelType] = [
                'initial_stock' => $initialStockValue,
                'current_stock' => $currentStockValue,
                'received' => $totalReceived,
                'sold' => $totalSold,
                'total_sales' => $totalSalesAmount,
                'count_receptions' => $countReceptions,
                'count_sales' => $countSales,
                'physical_stock' => $latestTankLevel ? $latestTankLevel->physical_stock : null,
                'difference' => $latestTankLevel ? ($latestTankLevel->physical_stock - $currentStockValue) : null,
                'difference_percentage' => $latestTankLevel && $currentStockValue > 0 
                    ? (($latestTankLevel->physical_stock - $currentStockValue) / $currentStockValue) * 100 
                    : null,
                'last_measurement_date' => $latestTankLevel ? $latestTankLevel->measurement_date : null,
            ];
            
            // DEBUG: Log des données récupérées
            \Log::info('Station Stock Data', [
                'station_id' => $stationId,
                'fuel_type' => $fuelType,
                'current_stock' => $currentStockValue,
                'receptions' => $totalReceived,
                'sales' => $totalSold,
                'physical_stock' => $latestTankLevel ? $latestTankLevel->physical_stock : null
            ]);
        }
        
        return $stockData;
    }
    public function refreshStockData($stationId)
{
    $station = Station::findOrFail($stationId);
    
    $superStock = $station->tanks()->where('fuel_type', 'super')->sum('current_volume');
    $gazoleStock = $station->tanks()->whereIn('fuel_type', ['gasoil', 'gazole', 'diesel'])->sum('current_volume');
    
    $superPercent = $station->capacite_super > 0 ? round(($superStock / $station->capacite_super) * 100, 1) : 0;
    $gazolePercent = $station->capacite_gazole > 0 ? round(($gazoleStock / $station->capacite_gazole) * 100, 1) : 0;
    
    $alerts = [];
    if ($superPercent < 20) {
        $alerts[] = ['type' => 'danger', 'message' => "Stock Super faible ($superPercent%)"];
    }
    if ($gazolePercent < 20) {
        $alerts[] = ['type' => 'warning', 'message' => "Stock Gazole faible ($gazolePercent%)"];
    }
    
    return response()->json([
        'success' => true,
        'stocks' => [
            'super' => number_format($superStock, 0, ',', ' '),
            'gazole' => number_format($gazoleStock, 0, ',', ' ')
        ],
        'super_percent' => $superPercent,
        'gazole_percent' => $gazolePercent,
        'alerts' => $alerts
    ]);
}

    private function normalizeFuelType($fuelType)
    {
        $fuelType = strtolower(trim($fuelType));
        
        $mapping = [
            
            'gazole' => 'gasoil',
            'gasoil' => 'gasoil',
            'super' => 'super',
            'essence pirogue' => 'Essence pirogue',
        ];
        
        return $mapping[$fuelType] ?? $fuelType;
    }

    public function rapportStationSpecifique($id)
    {
        $station = \App\Models\Station::find($id);

        if (!$station) {
            abort(404, "Station non trouvée");
        }

        $ventes = \App\Models\ShiftSaisie::where('station_id', $id)
                    ->where('statut', 'valide')
                    ->latest()
                    ->get();

        return view('chief.stations.rapports', compact('station', 'ventes'));
    }

    private function determinerPerformance($totalVentes, $avgEcart)
    {
        if ($totalVentes == 0) return 'Inactif';
        $score = ($totalVentes / 1000000) - (abs($avgEcart) / 10000);
        if ($score > 15) return 'Excellent';
        if ($score > 10) return 'Très bon';
        if ($score > 5) return 'Bon';
        return 'À améliorer';
    }

/**
 * Trouver la meilleure station avec flexibilité
 */
private function getBestStation($input, $isPerformancesArray = false)
{
    if ($isPerformancesArray) {
        // Cas 1: Tableau de performances déjà calculées
        if (empty($input)) return null;
        
        usort($input, fn($a, $b) => $b['total_ventes'] <=> $a['total_ventes']);
        return [
            'nom' => $input[0]['station']->nom ?? 'N/A',
            'ventes' => $input[0]['total_ventes'] ?? 0,
            'station' => $input[0]['station'] ?? null
        ];
    } else {
        // Cas 2: Collection de stations (calculer les ventes)
        $bestStation = null;
        $bestSales = 0;
        $bestStationObj = null;
        
        foreach ($input as $station) {
            // Ventes des shifts
            $stationSales = ShiftSaisie::where('station_id', $station->id)
                ->where('statut', 'valide')
                ->whereMonth('date_shift', now()->month)
                ->sum('total_ventes');
                
            // Ventes des tanks
            $tankSales = StockMovement::where('station_id', $station->id)
                ->where('movement_type', 'vente')
                ->whereMonth('movement_date', now()->month)
                ->sum('total_amount');
                
            $totalSales = $stationSales + $tankSales;
            
            if ($totalSales > $bestSales) {
                $bestSales = $totalSales;
                $bestStation = $station->nom;
                $bestStationObj = $station;
            }
        }
        
        return $bestStation ? [
            'nom' => $bestStation,
            'ventes' => $bestSales,
            'station' => $bestStationObj
        ] : null;
    }
}

    private function calculateGrowth($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = $end->diffInDays($start) + 1;

        $currentSales = ShiftSaisie::where('statut', 'valide')->whereBetween('date_shift', [$start, $end])->sum('total_ventes');
        $prevSales = ShiftSaisie::where('statut', 'valide')->whereBetween('date_shift', [$start->copy()->subDays($days), $start->copy()->subDay()])->sum('total_ventes');

        return $prevSales > 0 ? round((($currentSales - $prevSales) / $prevSales) * 100, 2) : 0;
    }

    private function calculateFillRate($stations)
    {
        $totalCapacity = $stations->sum('capacite_super') + $stations->sum('capacite_gazole');
        return $totalCapacity > 0 ? 0 : 0; // Logique de stock à implémenter
    }

    private function getSalesEvolution($startDate, $endDate, $stationId = null)
    {
        $query = ShiftSaisie::where('statut', 'valide')->whereBetween('date_shift', [$startDate, $endDate]);
        if ($stationId) $query->where('station_id', $stationId);

        $sales = $query->selectRaw('DATE(date_shift) as date, SUM(total_ventes) as sales')
            ->groupBy('date')->orderBy('date')->get();

        return ['data' => $sales, 'interval' => 'day', 'format' => 'd/m'];
    }

    public function createReception()
    {
        $fuelTypes = [
            'super' => 'Super',
            'gazole' => 'Gasoil'
        ];
        
        $tanks = [
            1 => 'Cuve 1 - Super',
            2 => 'Cuve 2 - Super', 
            3 => 'Cuve 3 - Gazole',
            4 => 'Cuve 4 - Gazole'
        ];
        
        return view('manager.stocks.create-reception', compact('fuelTypes', 'tanks'));
    }

    private function getFuelDistribution($startDate, $endDate, $stationId = null)
    {
        $details = ShiftPompeDetail::whereHas('shiftSaisie', function($q) use ($startDate, $endDate, $stationId) {
            $q->where('statut', 'valide')->whereBetween('date_shift', [$startDate, $endDate]);
            if ($stationId) $q->where('station_id', $stationId);
        })->get();

        $total = $details->sum('montant_ventes');
        if ($total == 0) return ['gasoil' => 0, 'super' => 0];

        // Normaliser et regrouper les carburants
        $groupedSales = [
            'gasoil' => 0,
            'super' => 0,   
            'essence pirogue' => 0
        ];
        
        foreach ($details as $detail) {
            $fuelType = $this->normalizeFuelType($detail->carburant);
            if (isset($groupedSales[$fuelType])) {
                $groupedSales[$fuelType] += $detail->montant_ventes;
            }
        }

        return [
            'gasoil' => round(($groupedSales['gasoil'] / $total) * 100, 2),
            'super' => round(($groupedSales['super'] / $total) * 100, 2),
        ];
    }

 public function analysePompistes(Request $request)
{
    $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
    $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
    
    $pompistes = User::role('manager')
        ->with(['shifts' => function($q) use ($startDate, $endDate) {
            $q->whereBetween('date_shift', [$startDate, $endDate])
              ->where('statut', 'valide');
        }, 'station'])
        ->get()
        ->map(function($user) {
            $user->total_ventes = $user->shifts->sum('total_ventes');
            $user->total_shifts = $user->shifts->count();
            $user->moyenne_ecart = $user->shifts->avg('ecart_final');
            return $user;
        });
            $determinerPerformance = function($avgPerShift, $avgEcart) {
        if ($avgPerShift == 0) return 'Inactif';
        
        $score = ($avgPerShift / 10000) - (abs($avgEcart) / 1000);
        
        if ($score > 50) return 'Excellent';
        if ($score > 35) return 'Très bon';
        if ($score > 20) return 'Bon';
        if ($score > 10) return 'Moyen';
        return 'À améliorer';
    };
    
    return view('chief.rapports.pompistes', compact(
        'pompistes', 
        'startDate', 
        'endDate', 
        'determinerPerformance'
    ));

    
    return view('chief.rapports.pompistes', compact('pompistes', 'startDate', 'endDate'));
}
    public function stations()
{
    // Récupérer toutes les stations avec leurs managers
    $stations = Station::with(['manager'])->get();
    
    // CORRECTION: Calculer les totaux RÉELS à partir des données
    $totalVentes = ShiftSaisie::where('statut', 'valide')->sum('total_ventes');
    
    // Calculer les capacités réelles à partir des cuves
    $capaciteTotale = 0;
    foreach ($stations as $station) {
        $capaciteSuper = $station->tanks()->where('fuel_type', 'super')->sum('capacity');
        $capaciteGazole = $station->tanks()
            ->whereIn('fuel_type', ['gasoil', 'gazole', 'diesel'])
            ->sum('capacity');
        
        // Mettre à jour les capacités dans la station si nécessaire
        if ($capaciteSuper > 0 || $capaciteGazole > 0) {
            $station->update([
                'capacite_super' => $capaciteSuper,
                'capacite_gazole' => $capaciteGazole
            ]);
        }
        
        $capaciteTotale += $capaciteSuper + $capaciteGazole;
    }
    
    // Managers actifs (ceux qui ont au moins un shift)
    $managersActifs = User::role('manager')
        ->whereHas('shifts')
        ->count();
    
    $stats = [
        'total_ventes' => $totalVentes,
        'moyenne_station' => $stations->count() > 0 ? $totalVentes / $stations->count() : 0,
        'stations_actives' => $stations->where('statut', 'actif')->count(),
        'stations_inactives' => $stations->where('statut', 'inactif')->count(),
        'total_capacite' => $capaciteTotale, // Utiliser la capacité calculée
        'managers_actifs' => $managersActifs,
        'best_station' => $this->getBestStation($stations),
        'total_stations' => $stations->count(),
    ];
    
    \Log::info('Stations data loaded', [
        'stations_count' => $stations->count(),
        'total_sales' => $totalVentes,
        'total_capacity' => $capaciteTotale,
        'active_managers' => $managersActifs
    ]);
    
    return view('chief.stations', compact('stations', 'stats'));
}

    public function showStation($id)
    {
        $station = Station::with(['manager', 'shifts' => function($query) {
            $query->orderBy('date_shift', 'desc')
                  ->take(10);
        }])->findOrFail($id);
        
        // Statistiques pour aujourd'hui
        $todaySales = ShiftSaisie::where('station_id', $id)
            ->whereDate('date_shift', today())
            ->where('statut', 'valide')
            ->sum('total_ventes');
        
        // Shifts en attente
        $pendingShiftsCount = ShiftSaisie::where('station_id', $id)
            ->where('statut', 'en_attente')
            ->count();
        
        // Statistiques du mois
        $monthShifts = ShiftSaisie::where('station_id', $id)
            ->where('statut', 'valide')
            ->whereBetween('date_shift', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])
            ->get();
        
        $monthShiftsCount = $monthShifts->count();
        
        $monthStats = [
            'total_sales' => $monthShifts->sum('total_ventes'),
            'shifts_count' => $monthShiftsCount,
            'total_litres' => $monthShifts->sum('total_litres'),
            'avg_ecart' => $monthShifts->avg('ecart_final') ?? 0,
            'total_depenses' => $monthShifts->sum('total_depenses')
        ];
        
        // Derniers shifts
        $recentShifts = ShiftSaisie::where('station_id', $id)
            ->orderBy('date_shift', 'desc')
            ->orderBy('shift', 'desc')
            ->take(5)
            ->get();
        
        // Écart moyen global
        $avgEcart = ShiftSaisie::where('station_id', $id)
            ->where('statut', 'valide')
            ->avg('ecart_final') ?? 0;
        
        return view('chief.stations.show', compact(
            'station',
            'todaySales',
            'pendingShiftsCount',
            'monthShiftsCount',
            'monthStats',
            'recentShifts',
            'avgEcart'
        ));
    }

    
public function genererRapportPDF(Request $request)
{
    try {
        $stationId = $request->input('station_id');
        $reportType = $request->input('report_type', 'station');
        
        switch ($reportType) {
            case 'station':
                // Utilisez 'pdf.station.report' maintenant
                return redirect()->route('pdf.station-report', ['stationId' => $stationId]);
            
            case 'reconciliation':
                // Pour reconciliation-report, passez les paramètres GET
                return redirect()->route('pdf.reconciliation-report', [
                    'station_id' => $stationId,
                    'fuel_type' => $request->input('fuel_type'),
                    'start_date' => $request->input('start_date'),
                    'end_date' => $request->input('end_date')
                ]);
                
            case 'inventory':
                return redirect()->route('pdf.inventory.report', [
                    'start_date' => $request->input('start_date'),
                    'end_date' => $request->input('end_date')
                ]);
                
            case 'sales-by-pump':
                return redirect()->route('pdf.sales-by-pump.report', [
                    'station_id' => $stationId,
                    'start_date' => $request->input('start_date'),
                    'end_date' => $request->input('end_date')
                ]);
                
            default:
                return back()->with('error', 'Type de rapport non supporté');
        }
        
    } catch (\Exception $e) {
        \Log::error('Erreur génération PDF: ' . $e->getMessage());
        return back()->with('error', 'Erreur génération PDF: ' . $e->getMessage());
    }
}
    /**
     * Afficher le formulaire de création d'une station
     */
    public function createStation()
    {
        $managers = User::role('manager')->get();
        return view('chief.stations.create', compact('managers'));
    }

    /**
     * Enregistrer une nouvelle station
     */

   

    /**
     * Méthodes pour les stocks du Chief (si vous avez besoin d'accéder aux stocks)
     */
    
    public function stockReceptions(Request $request)
    {
        $stationId = $request->input('station_id');
        
        $query = StockMovement::with(['station', 'recorder'])
            ->where('movement_type', 'reception');
        
        if ($stationId) {
            $query->where('station_id', $stationId);
        }
        
        $receptions = $query->orderBy('movement_date', 'desc')
            ->paginate(20);
        
        $allStations = Station::all();
        
        return view('chief.stocks.receptions', compact('receptions', 'allStations', 'stationId'));
    }

    public function stockMovements(Request $request)
    {
        $stationId = $request->input('station_id');
        $fuelType = $request->input('fuel_type');
        
        $query = StockMovement::with(['station', 'recorder', 'verifier']);
        
        if ($stationId) {
            $query->where('station_id', $stationId);
        }
        
        if ($fuelType) {
            $query->where('fuel_type', $fuelType);
        }
        
        $movements = $query->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $allStations = Station::all();
        $fuelTypes = ['super' => 'Super', 'gasoil' => 'Gasoil'];
        
        return view('chief.stocks.movements', compact('movements', 'allStations', 'fuelTypes', 'stationId', 'fuelType'));
    }

    public function stockHistory(Request $request, $station_id = null, $fuel_type = null)
    {
        $query = StockMovement::with(['station', 'recorder', 'verifier']);
        
        if ($station_id) {
            $query->where('station_id', $station_id);
        }
        
        if ($fuel_type) {
            $query->where('fuel_type', $fuel_type);
        }
        
        $stockMovements = $query->latest()->paginate(20);
        
        $station = $station_id ? Station::find($station_id) : null;
        $allStations = Station::all();
        
        return view('chief.stocks.history', compact('stockMovements', 'station', 'allStations', 'fuel_type'));
    }
        private function getAllStationStocks()
    {
        $allStations = Station::all();
        $stationStocks = [];
        
        foreach ($allStations as $station) {
            $superStock = $this->getCurrentStock('super', $station->id);
            $gasoilStock = $this->getCurrentStock('gasoil', $station->id);
            
            $lastSuperTank = TankLevel::where('station_id', $station->id)
                ->where('fuel_type', 'super')
                ->orderBy('measurement_date', 'desc')
                ->first();
                
            $lastGazoleTank = TankLevel::where('station_id', $station->id)
                ->whereIn('fuel_type', ['gazole', 'gasoil'])
                ->orderBy('measurement_date', 'desc')
                ->first();
            
            $lastActivity = StockMovement::where('station_id', $station->id)
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            $stationStocks[$station->id] = [
                'station' => $station,
                'super' => $superStock,
                'gasoil' => $gasoilStock,
                'last_super_tank' => $lastSuperTank,
                'last_gazole_tank' => $lastGazoleTank,
                'last_activity' => $lastActivity,
                'status' => $this->getStockStatus($superStock, $gasoilStock)
            ];
        }
        
        return $stationStocks;
    }
       private function getGazoleStock($stationId)
    {
        // Essayer d'abord avec 'gazole'
        $stock = StockMovement::where('station_id', $stationId)
            ->where('fuel_type', 'gazole')
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($stock) {
            return (float) $stock->stock_after;
        }
        
        // Si pas trouvé, essayer avec 'gasoil'
        $stock = StockMovement::where('station_id', $stationId)
            ->where('fuel_type', 'gasoil')
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($stock) {
            return (float) $stock->stock_after;
        }
        
        return 0;
    }
    
    /**
     * Déterminer le statut du stock
     */
    private function getStockStatus($superStock, $gasoilStock)
    {
        if ($superStock < 5000 || $gasoilStock < 5000) {
            return 'danger';
        } elseif ($superStock < 10000 || $gasoilStock < 10000) {
            return 'warning';
        } elseif ($superStock > 20000 && $gasoilStock > 20000) {
            return 'success';
        } else {
            return 'info';
        }
    }
    private function getSelectedStationStocks($stationId)
    {
        if (!$stationId) {
            return null;
        }
        
        // Stock Super
        $superStock = $this->getCurrentStock('super', $stationId);
        
        // Stock Gazole - gestion spéciale
        $gasoilStock = $this->getGazoleStock($stationId);
        
        // Derniers mouvements pour debug
        $lastMovements = StockMovement::where('station_id', $stationId)
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Dernier jaugeage
        $lastTankLevel = TankLevel::where('station_id', $stationId)
            ->orderBy('measurement_date', 'desc')
            ->first();
        
        // Dernières réceptions
        $lastReception = StockMovement::where('station_id', $stationId)
            ->where('movement_type', 'reception')
            ->orderBy('movement_date', 'desc')
            ->first();
        
        return [
            'super' => $superStock,
            'gasoil' => $gasoilStock,
            'last_movements' => $lastMovements,
            'last_reception' => $lastReception,
            'last_tank_level' => $lastTankLevel,
            'station' => Station::find($stationId)
        ];
    }
       private function getCurrentStock($fuelType, $stationId)
    {
        // Normaliser le nom du carburant
        $normalizedFuelType = strtolower(trim($fuelType));
        
        // Si c'est gasoil, chercher les deux variantes
        if ($normalizedFuelType === 'gasoil' || $normalizedFuelType === 'gazole') {
            // Essayer d'abord 'gasoil'
            $stock = StockMovement::where('station_id', $stationId)
                ->where('fuel_type', 'gasoil')
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Si pas trouvé, essayer 'gazole'
            if (!$stock) {
                $stock = StockMovement::where('station_id', $stationId)
                    ->where('fuel_type', 'gazole')
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        } else {
            // Pour super, chercher normalement
            $stock = StockMovement::where('station_id', $stationId)
                ->where('fuel_type', $fuelType)
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        }
        
        $stockValue = $stock ? (float) $stock->stock_after : 0;
        
        // DEBUG
        \Log::info('getCurrentStock', [
            'fuel_type' => $fuelType,
            'station_id' => $stationId,
            'found_stock' => $stockValue,
            'found_movement_id' => $stock ? $stock->id : null
        ]);
        
        return $stockValue;
    }
    

    public function stockBalance(Request $request, $station_id = null)
    {
        $query = Station::query();
        
        if ($station_id) {
            $query->where('id', $station_id);
        }
        
        $stations = $query->with('manager')->get();
        
        $balances = [];
        foreach ($stations as $station) {
            $balances[$station->id] = [
                'super' => StockMovement::currentStock('super', $station->id),
                'gasoil' => StockMovement::currentStock('gasoil', $station->id),
                'last_tank_level' => TankLevel::where('station_id', $station->id)
                    ->orderBy('measurement_date', 'desc')
                    ->first(),
                'station' => $station
            ];
        }
        
        return view('chief.stocks.balance', compact('balances', 'stations', 'station_id'));
    }
}