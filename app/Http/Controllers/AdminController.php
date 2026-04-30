<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeUser;
use App\Models\ActivityLog;
use App\Models\DataCorrection;
use App\Models\Depense;
use App\Models\FuelPrice;
use App\Models\ShiftPompeDetail;
use App\Models\ShiftSaisie;
use App\Models\Station;
use App\Models\StockMovement;
use App\Models\TankLevel;
use App\Models\User;
use Carbon\Carbon;
use App\Mail\UserWelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
   

    
    /**
     * Comparaison des stations avec filtres
     */
   public function stationComparison(Request $request)
{
    try {
        // 1. Étendre la période par défaut pour voir plus de données
        $startDate = $request->input('start_date', Carbon::now()->subMonths(3)->format('Y-m-d')); // 3 mois au lieu de 30 jours
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // 2. Journaliser pour le débogage
        \Log::info('Station Comparison - Start', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'selected_stations' => $request->input('stations', [])
        ]);
        
        // Convertir les dates
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // 3. Récupérer toutes les stations
        $allStations = Station::with(['manager'])->get();
        
        \Log::info('Stations trouvées', ['count' => $allStations->count()]);
        
        // 4. Si des stations spécifiques sont sélectionnées
        $selectedStations = $request->input('stations', []);
        $stationsToCompare = $selectedStations 
            ? Station::whereIn('id', $selectedStations)->get()
            : $allStations;
        
        \Log::info('Stations à comparer', ['count' => $stationsToCompare->count()]);
        
        // 5. Tableau pour stocker les données de comparaison
        $comparisonData = [];
        
        // 6. Pour chaque station, calculer les statistiques
        foreach ($stationsToCompare as $station) {
            \Log::info('Traitement station', ['station_id' => $station->id, 'nom' => $station->nom]);
            
            // Récupérer les shifts validés pour la période
            $shifts = ShiftSaisie::where('station_id', $station->id)
                ->where('statut', 'valide')
                ->whereBetween('date_shift', [$start, $end])
                ->get();
            
            \Log::info('Shifts trouvés pour station', [
                'station_id' => $station->id,
                'shifts_count' => $shifts->count()
            ]);
            
            // Si aucun shift validé, on peut quand même afficher la station avec 0
            if ($shifts->isEmpty()) {
                $comparisonData[] = [
                    'id' => $station->id,
                    'name' => $station->nom,
                    'code' => $station->code ?? '',
                    'manager' => $station->manager ? $station->manager->name : 'Non assigné',
                    'status' => $station->statut,
                    
                    // Ventes (tout à 0)
                    'essence_sales' => 0,
                    'gasoil_sales' => 0,
                    'total_sales' => 0,
                    
                    // Quantités
                    'essence_qty' => 0,
                    'gasoil_qty' => 0,
                    'total_qty' => 0,
                    
                    // Performance
                    'shifts_count' => 0,
                    'total_depenses' => 0,
                    'ca_per_shift' => 0,
                    'productivity' => 0,
                    'avg_ecart' => 0,
                    
                    // Stocks
                    'super_stock' => $this->getCurrentStock('super', $station->id),
                    'gasoil_stock' => $this->getCurrentStock('gasoil', $station->id),
                    
                    // Dates
                    'last_shift_date' => 'Aucun',
                ];
                
                continue;
            }
            
            // Calculer les ventes par type de carburant
            $essence_sales = 0;
            $gasoil_sales = 0;
            $essence_qty = 0;
            $gasoil_qty = 0;
            $total_sales = 0;
            $total_litres = 0;
            $total_depenses = 0;
            $total_ecart = 0;
            
            // Récupérer les détails des pompes pour chaque shift
            foreach ($shifts as $shift) {
                $shiftDetails = ShiftPompeDetail::where('shift_saisie_id', $shift->id)->get();
                
                \Log::info('Détails pompes pour shift', [
                    'shift_id' => $shift->id,
                    'details_count' => $shiftDetails->count()
                ]);
                
                foreach ($shiftDetails as $detail) {
                    $fuelType = strtolower(trim($detail->carburant));
                    $quantity = $detail->litrage_vendu ?? 0;
                    $amount = $detail->montant_ventes ?? 0;
                    
                    \Log::info('Détail pompe', [
                        'fuel_type' => $fuelType,
                        'quantity' => $quantity,
                        'amount' => $amount
                    ]);
                    
                    if (str_contains($fuelType, 'super') || str_contains($fuelType, 'essence')) {
                        $essence_qty += $quantity;
                        $essence_sales += $amount;
                    } elseif (str_contains($fuelType, 'gasoil') || str_contains($fuelType, 'gazole') || str_contains($fuelType, 'diesel')) {
                        $gasoil_qty += $quantity;
                        $gasoil_sales += $amount;
                    }
                }
                
                $total_sales += $shift->total_ventes;
                $total_litres += $shift->total_litres;
                $total_depenses += $shift->total_depenses;
                $total_ecart += $shift->ecart_final;
            }
            
            // Totaux
            $total_sales_calculated = $essence_sales + $gasoil_sales;
            $total_qty_calculated = $essence_qty + $gasoil_qty;
            
            // Performance metrics
            $shifts_count = $shifts->count();
            $ca_per_shift = $shifts_count > 0 ? $total_sales / $shifts_count : 0;
            $productivity = $total_qty_calculated > 0 ? $total_sales / $total_qty_calculated : 0;
            $avg_ecart_station = $shifts_count > 0 ? $total_ecart / $shifts_count : 0;
            
            // Stocks actuels
            $currentStocks = $this->getCurrentStocks($station->id);
            
            // Données pour cette station
            $stationData = [
                'id' => $station->id,
                'name' => $station->nom,
                'code' => $station->code ?? '',
                'manager' => $station->manager ? $station->manager->name : 'Non assigné',
                'status' => $station->statut,
                
                // Ventes
                'essence_sales' => $essence_sales,
                'gasoil_sales' => $gasoil_sales,
                'total_sales' => $total_sales,
                
                // Quantités
                'essence_qty' => $essence_qty,
                'gasoil_qty' => $gasoil_qty,
                'total_qty' => $total_qty_calculated,
                
                // Performance
                'shifts_count' => $shifts_count,
                'total_depenses' => $total_depenses,
                'ca_per_shift' => $ca_per_shift,
                'productivity' => $productivity,
                'avg_ecart' => $avg_ecart_station,
                
                // Stocks
                'super_stock' => $currentStocks['super'] ?? 0,
                'gasoil_stock' => $currentStocks['gasoil'] ?? 0,
                
                // Dates
                'last_shift_date' => $shifts->isNotEmpty() ? $shifts->max('date_shift')->format('d/m/Y') : 'Aucun',
            ];
            
            \Log::info('Données calculées pour station', $stationData);
            
            $comparisonData[] = $stationData;
        }
        
        // 7. Calculer les totaux globaux
        $totalStats = [
            'total_sales' => collect($comparisonData)->sum('total_sales'),
            'total_litres' => collect($comparisonData)->sum('total_qty'),
            'total_shifts' => collect($comparisonData)->sum('shifts_count'),
            'total_depenses' => collect($comparisonData)->sum('total_depenses'),
        ];
        
        \Log::info('Totaux globaux', $totalStats);
        
        // 8. Calculer les moyennes globales
        $stationCount = count($comparisonData);
        if ($stationCount > 0) {
            $totalStats['avg_sales_per_station'] = $totalStats['total_sales'] / $stationCount;
            $totalStats['avg_litres_per_station'] = $totalStats['total_litres'] / $stationCount;
            $totalStats['avg_shifts_per_station'] = $totalStats['total_shifts'] / $stationCount;
            $totalStats['avg_sales_per_shift'] = $totalStats['total_shifts'] > 0 
                ? $totalStats['total_sales'] / $totalStats['total_shifts'] 
                : 0;
        }
        
        // 9. Trier par total des ventes (du plus élevé au plus bas)
        $sortedData = collect($comparisonData)->sortByDesc('total_sales')->values()->all();
        
        // Ajouter les rangs
        foreach ($sortedData as $index => &$station) {
            $station['rank'] = $index + 1;
        }
        
        \Log::info('Données finales', ['stations_count' => count($sortedData)]);
        
        return view('admin.reports.station-comparison', [
            'allStations' => $allStations,
            'comparisonData' => $sortedData,
            'totalStats' => $totalStats,
            'fuelDistribution' => $this->calculateFuelDistributionForComparison($sortedData),
            'dailySales' => $this->getDailySalesEvolution($start, $end, $selectedStations),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedStations' => $selectedStations,
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Erreur dans stationComparison: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return view('admin.reports.station-comparison', [
            'allStations' => collect(),
            'comparisonData' => [],
            'totalStats' => [],
            'fuelDistribution' => [],
            'dailySales' => [],
            'startDate' => $request->input('start_date', Carbon::now()->subMonths(3)->format('Y-m-d')),
            'endDate' => $request->input('end_date', Carbon::now()->format('Y-m-d')),
            'selectedStations' => [],
            'error' => 'Erreur de chargement des données: ' . $e->getMessage()
        ]);
    }
}
public function todayShifts()
{
    $today = Carbon::today();
    
    $shifts = ShiftSaisie::with(['station', 'user', 'pompeDetails', 'depenses'])
        ->whereDate('date_shift', $today)
        ->orderBy('date_shift', 'desc')
        ->orderBy('shift', 'desc')
        ->paginate(20);
    
    $stats = [
        'total_shifts' => $shifts->total(),
        'validated_shifts' => ShiftSaisie::whereDate('date_shift', $today)
            ->where('statut', 'valide')
            ->count(),
        'pending_shifts' => ShiftSaisie::whereDate('date_shift', $today)
            ->where('statut', 'en_attente')
            ->count(),
        'total_sales' => ShiftSaisie::whereDate('date_shift', $today)
            ->where('statut', 'valide')
            ->sum('total_ventes'),
        'total_litres' => ShiftSaisie::whereDate('date_shift', $today)
            ->where('statut', 'valide')
            ->sum('total_litres'),
    ];
    
    return view('admin.shifts.today', compact('shifts', 'stats', 'today'));
}
    
    /**
     * Récupérer les stocks actuels d'une station
     */
    private function getCurrentStocks($stationId)
    {
        try {
            $stocks = [];
            
            $fuelTypes = ['super', 'gasoil'];
            
            foreach ($fuelTypes as $type) {
                $stock = StockMovement::where('station_id', $stationId)
                    ->where('fuel_type', $type)
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();
                    
                $stocks[$type] = $stock ? (float) $stock->stock_after : 0;
            }
            
            return $stocks;
            
        } catch (\Exception $e) {
            \Log::error('Erreur getCurrentStocks: ' . $e->getMessage());
            return ['super' => 0, 'gasoil' => 0];
        }
    }
    
    /**
     * Récupérer l'évolution des ventes par jour
     */
    private function getDailySalesEvolution($start, $end, $stationIds = [])
    {
        try {
            $query = ShiftSaisie::select(
                DB::raw('DATE(date_shift) as date'),
                DB::raw('SUM(total_ventes) as total_sales'),
                'station_id'
            )
            ->where('statut', 'valide')
            ->whereBetween('date_shift', [$start, $end]);
            
            if (!empty($stationIds)) {
                $query->whereIn('station_id', $stationIds);
            }
            
            $results = $query->groupBy('date', 'station_id')
                ->orderBy('date')
                ->get();
            
            // Organiser les données par date
            $dailyData = [];
            $dates = [];
            
            // Générer toutes les dates entre start et end
            $currentDate = $start->copy();
            while ($currentDate <= $end) {
                $dateStr = $currentDate->format('Y-m-d');
                $dates[] = $dateStr;
                $dailyData[$dateStr] = [
                    'date_display' => $currentDate->format('d/m'),
                    'total_sales' => 0,
                ];
                $currentDate->addDay();
            }
            
            // Remplir avec les données réelles
            foreach ($results as $result) {
                $dateStr = Carbon::parse($result->date)->format('Y-m-d');
                
                if (!isset($dailyData[$dateStr])) {
                    $dailyData[$dateStr] = [
                        'date_display' => Carbon::parse($result->date)->format('d/m'),
                        'total_sales' => 0,
                    ];
                }
                
                $dailyData[$dateStr]['total_sales'] += $result->total_sales;
            }
            
            // Convertir en tableau indexé et filtrer les dates sans données si nécessaire
            $filteredData = [];
            foreach ($dailyData as $dateStr => $data) {
                // Vous pouvez choisir d'inclure ou non les jours sans données
                // Ici, on les inclut avec 0
                $filteredData[] = $data;
            }
            
            return $filteredData;
            
        } catch (\Exception $e) {
            \Log::error('Erreur getDailySalesEvolution: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer le stock actuel d'une station
     */
    private function getCurrentStock($fuelType, $stationId)
    {
        try {
            // Normaliser le nom du carburant
            $normalizedFuelType = strtolower(trim($fuelType));
            
            // Si c'est gasoil, chercher les deux variantes
            if ($normalizedFuelType === 'gasoil' || $normalizedFuelType === 'gazole') {
                $stock = StockMovement::where('station_id', $stationId)
                    ->whereIn('fuel_type', ['gasoil', 'gazole'])
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();
            } else {
                $stock = StockMovement::where('station_id', $stationId)
                    ->where('fuel_type', $fuelType)
                    ->orderBy('movement_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
            
            return $stock ? (float) $stock->stock_after : 0;
        } catch (\Exception $e) {
            \Log::error('Erreur getCurrentStock: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Calculer la répartition par carburant
     */
 private function calculateFuelDistributionForComparison($comparisonData)
{
    $totalSales = collect($comparisonData)->sum('total_sales');
    $essenceSales = collect($comparisonData)->sum('essence_sales');
    $gasoilSales = collect($comparisonData)->sum('gasoil_sales');
    
    return [
        'essence' => [
            'sales' => $essenceSales,
            'qty' => collect($comparisonData)->sum('essence_qty'),
            'percentage' => $totalSales > 0 ? ($essenceSales / $totalSales) * 100 : 0,
        ],
        'gasoil' => [
            'sales' => $gasoilSales,
            'qty' => collect($comparisonData)->sum('gasoil_qty'),
            'percentage' => $totalSales > 0 ? ($gasoilSales / $totalSales) * 100 : 0,
        ],
    ];
}

    
    /**
     * Normaliser le type de carburant
     */
    private function normalizeFuelType($fuelType)
    {
        $fuelType = strtolower(trim($fuelType));
        
        $mapping = [
            
            'gazole' => 'gasoil',
            'gasoil' => 'gasoil',
            'gas oil' => 'gasoil',
            'super' => 'super',
            'essence' => 'super',
            
        ];
        
        return $mapping[$fuelType] ?? $fuelType;
    }
    
    /**
     * Maintenances 
     */
    public function maintenance()
    {
        // Toutes les stations
        $stations = Station::with(['manager', 'shifts' => function($q) {
            $q->whereMonth('date_shift', now()->month);
        }])->get();
        
        // Statistiques
        $stats = [
            'total_stations' => $stations->count(),
            'active_stations' => $stations->where('statut', 'actif')->count(),
            'pending_validations' => ShiftSaisie::where('statut', 'en_attente')->count(),
            'pending_corrections' => DataCorrection::whereNull('corrected_at')->count(),
            'shifts_today' => ShiftSaisie::whereDate('date_shift', today())->count(),
            'shifts_month' => ShiftSaisie::whereMonth('date_shift', now()->month)->count(),
        ];
        
        // Shifts en attente
        $pendingShifts = ShiftSaisie::with(['station', 'user'])
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Corrections en attente
        $pendingCorrections = DataCorrection::with(['corrector', 'station'])
            ->whereNull('corrected_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('admin.supervision.maintenance', compact(
            'stations',
            'stats',
            'pendingShifts',
            'pendingCorrections'
        ));
    }
    
    /**
     * Tarification
     */
    public function pricing()
    {
        // Prix actuels
        $currentPrices = [];
        $fuelTypes = ['super', 'gazole'];
        
        foreach ($fuelTypes as $type) {
            $latestPrice = FuelPrice::where('fuel_type', $type)
                ->latest()
                ->first();
                
            $currentPrices[$type] = $latestPrice ? $latestPrice->price_per_liter : 0;
        }
        
        // Historique des prix
        $priceHistory = FuelPrice::with('creator')
            ->orderBy('effective_from', 'desc')
            ->paginate(20);
        
        return view('admin.supervision.pricing', compact('currentPrices', 'priceHistory'));
    }
    
    /**
     * Mettre à jour les prix
     */
    public function updatePricing(Request $request)
    {
        $validated = $request->validate([
            'fuel_type' => 'required|in:super,gazole',
            'price_per_liter' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'reason' => 'required|string|max:500',
        ]);
        
        // Désactiver l'ancien prix
        FuelPrice::where('fuel_type', $validated['fuel_type'])
            ->where('is_active', true)
            ->update(['is_active' => false]);
        
        // Créer le nouveau prix
        FuelPrice::create([
            'fuel_type' => $validated['fuel_type'],
            'price_per_liter' => $validated['price_per_liter'],
            'effective_from' => $validated['effective_from'],
            'created_by' => Auth::id(),
            'reason' => $validated['reason'],
            'is_active' => true,
        ]);
        
        return redirect()->route('admin.supervision.pricing')
            ->with('success', 'Prix mis à jour avec succès!');
    }
    
 
    
    /**
     * Afficher la liste des stations
     */
    public function stationsIndex(Request $request)
{
       $query = Station::with(['manager', 'shifts' => function($q) {
        $q->whereMonth('date_shift', now()->month)
          ->where('statut', 'valide');
    }]);
    
    // Filtres
    if ($request->has('status')) {
        $query->where('statut', $request->status);
    }
    
    if ($request->has('city')) {
        $query->where('ville', 'like', '%' . $request->city . '%');
    }
    
    if ($request->has('manager_id')) {
        $query->where('manager_id', $request->manager_id);
    }
    
    $stations = $query->paginate(20);
    
    // Ajouter les statistiques pour chaque station
    $stations->each(function($station) {
        $station->month_sales = $station->shifts->sum('total_ventes');
        $station->super_stock = $this->getCurrentStock('super', $station->id);
        $station->gasoil_stock = $this->getCurrentStock('gasoil', $station->id);
        $station->pending_shifts = ShiftSaisie::where('station_id', $station->id)
            ->where('statut', 'en_attente')
            ->count();
        $station->validation_rate = ShiftSaisie::where('station_id', $station->id)->count() > 0 
            ? (ShiftSaisie::where('station_id', $station->id)->where('statut', 'valide')->count() / 
               ShiftSaisie::where('station_id', $station->id)->count()) * 100 
            : 0;
    });
    
    $totalStats = [
        'total_sales_month' => $stations->sum('month_sales'),
        'avg_sales_per_station' => $stations->avg('month_sales'),
        'active_stations' => Station::where('statut', 'actif')->count(),
        'stations_without_manager' => Station::whereNull('manager_id')->count(),
    ];
    
    $managers = User::role('manager')->get();
    
    return view('admin.stations.index', compact('stations', 'totalStats', 'managers'));
}
    
    /**
     * Afficher le détail d'une station
     */
    public function showStation($id)
    {
        $station = Station::with([
            'manager',
            'shifts' => function($q) {
                $q->whereMonth('date_shift', now()->month)
                  ->where('statut', 'valide')
                  ->orderBy('date_shift', 'desc');
            },
            'shifts.user',
            'shifts.pompeDetails'
        ])->findOrFail($id);
        
        // Statistiques du mois
        $monthStats = [
            'total_sales' => $station->shifts->sum('total_ventes'),
            'total_litres' => $station->shifts->sum('total_litres'),
            'shifts_count' => $station->shifts->count(),
            'avg_ecart' => $station->shifts->avg('ecart_final'),
            'avg_sales_per_shift' => $station->shifts->avg('total_ventes'),
            'total_depenses' => $station->shifts->sum('total_depenses'),
        ];
        
        // Stocks actuels
        $stocks = [
            'super' => $this->getCurrentStock('super', $station->id),
            'gasoil' => $this->getCurrentStock('gasoil', $station->id),
        ];
        
        // Derniers shifts
        $recentShifts = ShiftSaisie::where('station_id', $station->id)
            ->with('user')
            ->orderBy('date_shift', 'desc')
            ->take(10)
            ->get();
        
        // Évolution des ventes sur 30 jours
        $sales30Days = ShiftSaisie::select(
                DB::raw('DATE(date_shift) as date'),
                DB::raw('SUM(total_ventes) as sales'),
                DB::raw('SUM(total_litres) as litres')
            )
            ->where('station_id', $station->id)
            ->where('statut', 'valide')
            ->whereDate('date_shift', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Managers disponibles pour réassignation
        $availableManagers = User::role('manager')
            ->where(function($q) use ($station) {
                $q->whereNull('station_id')
                  ->orWhere('id', $station->manager_id);
            })
            ->get();
        
        return view('admin.stations.show', compact(
            'station', 
            'monthStats', 
            'stocks', 
            'recentShifts',
            'sales30Days',
            'availableManagers'
        ));
    }
    
    /**
     * Modifier le manager d'une station
     */
    public function updateStationManager(Request $request, $id)
    {
        $station = Station::findOrFail($id);
        
        $validated = $request->validate([
            'manager_id' => 'nullable|exists:users,id'
        ]);
        
        $oldManagerId = $station->manager_id;
        
        $station->update([
            'manager_id' => $validated['manager_id'],
            'updated_by' => Auth::id(),
        ]);
        
        // Mettre à jour l'ancien manager
        if ($oldManagerId) {
            User::where('id', $oldManagerId)->update(['station_id' => null]);
        }
        
        // Mettre à jour le nouveau manager
        if ($validated['manager_id']) {
            User::where('id', $validated['manager_id'])->update([
                'station_id' => $station->id,
                'updated_at' => now()
            ]);
        }
        
        // Log de l'activité
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'station_manager_update',
            'description' => "Manager de la station {$station->nom} modifié",
            'details' => json_encode([
                'station_id' => $station->id,
                'old_manager_id' => $oldManagerId,
                'new_manager_id' => $validated['manager_id']
            ])
        ]);
        
        return redirect()->route('admin.stations.show', $station->id)
            ->with('success', 'Manager mis à jour avec succès!');
    }
    
    /**
     * Validations en attente
     */
    public function pendingValidations(Request $request)
    {
        $stationId = $request->input('station_id');
        
        $query = ShiftSaisie::with(['station', 'user', 'pompeDetails', 'depenses'])
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc');
            
        if ($stationId) {
            $query->where('station_id', $stationId);
        }
        
        $shifts = $query->paginate(20);
        
        $stats = [
            'total' => ShiftSaisie::where('statut', 'en_attente')->count(),
            'today' => ShiftSaisie::where('statut', 'en_attente')
                ->whereDate('created_at', today())
                ->count(),
        ];
        
        $allStations = Station::all();
        
        return view('admin.validations.pending', compact('shifts', 'stats', 'allStations', 'stationId'));
    }
    
    /**
     * Valider un shift
     */
    public function validateShift(Request $request, $id)
    {
        $validated = $request->validate([
            'comment' => 'nullable|string|max:500'
        ]);
        
        $shift = ShiftSaisie::findOrFail($id);
        
        $shift->update([
            'statut' => 'valide',
            'valide_par' => Auth::id(),
            'valide_le' => now(),
            'commentaire_validation' => $validated['comment'] ?? null,
        ]);
        
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'shift_validated',
            'description' => "Shift #{$shift->id} validé pour la station " . $shift->station->nom,
            'details' => json_encode([
                'shift_id' => $shift->id,
                'station' => $shift->station->nom,
                'sales' => $shift->total_ventes,
                'ecart' => $shift->ecart_final
            ])
        ]);
        
        return redirect()->route('admin.validations.pending')
            ->with('success', 'Shift validé avec succès!');
    }
    
    /**
     * Rapports quotidiens
     */
    public function dailyReport(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        $stationId = $request->input('station_id');
        
        $query = ShiftSaisie::with(['station', 'user', 'pompeDetails', 'depenses'])
            ->whereDate('date_shift', $date);
            
        if ($stationId) {
            $query->where('station_id', $stationId);
        }
        
        $shifts = $query->orderBy('date_shift', 'desc')
            ->orderBy('shift', 'desc')
            ->paginate(20);
        
        // Statistiques
        $statsQuery = ShiftSaisie::whereDate('date_shift', $date);
        
        if ($stationId) {
            $statsQuery->where('station_id', $stationId);
        }
        
        $stats = [
            'total_shifts' => $shifts->count(),
            'validated_shifts' => $statsQuery->where('statut', 'valide')->count(),
            'pending_shifts' => $statsQuery->where('statut', 'en_attente')->count(),
            'rejected_shifts' => $statsQuery->where('statut', 'rejete')->count(),
            'total_sales' => $statsQuery->where('statut', 'valide')->sum('total_ventes'),
            'total_litres' => $statsQuery->where('statut', 'valide')->sum('total_litres'),
            'total_depenses' => $statsQuery->where('statut', 'valide')->sum('total_depenses'),
            'avg_ecart' => $statsQuery->where('statut', 'valide')->avg('ecart_final'),
        ];
        
        $allStations = Station::all();
        
        return view('admin.reports.daily', compact(
            'shifts', 'stats', 'allStations', 'date', 'stationId'
        ));
    }
    
    /**
     * Rapports mensuels
     */
    public function monthlyReport(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        $stationId = $request->input('station_id');
        
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        $statsQuery = ShiftSaisie::whereBetween('date_shift', [$startDate, $endDate]);
        
        if ($stationId) {
            $statsQuery->where('station_id', $stationId);
        }
        
        $stats = [
            'total_shifts' => $statsQuery->count(),
            'validated_shifts' => $statsQuery->where('statut', 'valide')->count(),
            'pending_shifts' => $statsQuery->where('statut', 'en_attente')->count(),
            'total_sales' => $statsQuery->where('statut', 'valide')->sum('total_ventes'),
            'total_litres' => $statsQuery->where('statut', 'valide')->sum('total_litres'),
            'total_depenses' => $statsQuery->where('statut', 'valide')->sum('total_depenses'),
            'avg_sales_per_shift' => $statsQuery->where('statut', 'valide')->avg('total_ventes'),
            'avg_ecart' => $statsQuery->where('statut', 'valide')->avg('ecart_final'),
        ];
        
        $allStations = Station::all();
        
        return view('admin.reports.monthly', compact(
            'stats', 'allStations', 'year', 'month', 'stationId', 'startDate', 'endDate'
        ));
    }
    
    /**
     * Gestion des utilisateurs
     */
public function index(Request $request)
{
    try {
        // Récupérer les utilisateurs avec leurs rôles spatie
        $query = User::with('roles', 'station')
                    ->orderBy('created_at', 'desc');
        
        // Filtre par rôle - Utiliser spatie/permission
        if ($request->has('role') && $request->role != '') {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        
        // Filtre par statut
        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status == 'pending') {
                $query->where('statut', 'pending');
            }
        }
        
        // Recherche
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('telephone', 'LIKE', "%{$search}%");
            });
        }
        
        // Pagination
        $users = $query->paginate(20);
        
        // Transformer les données pour l'affichage
        $users->getCollection()->transform(function($user) {
            // Récupérer le rôle principal depuis spatie
            $mainRole = $user->getRoleNames()->first();
            
            // Si pas de rôle spatie, utiliser la colonne 'role' du modèle
            $user->display_role = $mainRole ?: ($user->role ?? 'user');
            
            // Log pour débogage
            \Log::info('User roles:', [
                'user_id' => $user->id,
                'name' => $user->name,
                'spatie_roles' => $user->getRoleNames()->toArray(),
                'model_role' => $user->role,
                'display_role' => $user->display_role
            ]);
            
            return $user;
        });
        
        // Statistiques
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        
        // Compter les managers avec Spatie
        try {
            $managerCount = User::role('manager')->count();
        } catch (\Exception $e) {
            // Fallback si Spatie ne fonctionne pas
            $managerCount = User::where('role', 'manager')->count();
        }
        
        // Rôles disponibles pour le filtre
        $roles = [];
        try {
            // Rôles depuis Spatie
            $spatieRoles = \Spatie\Permission\Models\Role::all();
            foreach ($spatieRoles as $role) {
                $roles[$role->name] = $this->formatRoleName($role->name);
            }
        } catch (\Exception $e) {
            // Fallback si Spatie n'est pas installé
            $roles = [
                'administrateur' => 'Administrateur',
                'manager' => 'Manager',
                'chief' => 'Chef des opérations',
                'user' => 'Utilisateur',
            ];
        }
        
        // Rôles uniques pour le filtre
        $allRoles = collect($roles)->unique()->sort()->toArray();
        
        return view('admin.users.index', compact(
            'users', 
            'totalUsers', 
            'activeUsers', 
            'managerCount', 
            'roles'
        ));
        
    } catch (\Exception $e) {
        \Log::error('Erreur dans admin users index: ' . $e->getMessage());
        
        // Fallback simple en cas d'erreur
        $users = User::paginate(20);
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $managerCount = 0;
        $roles = [
            'administrateur' => 'Administrateur',
            'manager' => 'Manager',
            'chief' => 'Chef des opérations',
            'user' => 'Utilisateur',
        ];
        
        return view('admin.users.index', compact(
            'users', 
            'totalUsers', 
            'activeUsers', 
            'managerCount', 
            'roles'
        ));
    }
}
private function formatRoleName($roleName)
{
    $formattedNames = [
        'administrateur' => 'Administrateur',
        'admin' => 'Administrateur',
        'manager' => 'Manager',
        'chief' => 'Chef des opérations',
        'chef_operations' => 'Chef des opérations',
        'user' => 'Utilisateur',
    ];
    
    return $formattedNames[$roleName] ?? ucfirst($roleName);
}

private function applyFilters($query, $request)
{
    // Filtre par rôle
    if ($request->has('role') && $request->role != '') {
        $query->whereHas('roles', function($q) use ($request) {
            $q->where('name', $request->role);
        });
    }
    
    // Filtre par statut
    if ($request->has('status') && $request->status != '') {
        if ($request->status == 'active') {
            $query->where('is_active', true);
        } elseif ($request->status == 'inactive') {
            $query->where('is_active', false);
        } elseif ($request->status == 'pending') {
            $query->where('statut', 'pending');
        }
    }
    
    // Recherche
    if ($request->has('search') && $request->search != '') {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('telephone', 'LIKE', "%{$search}%");
        });
    }
}
/**
 * Liste de tous les shifts
 */
public function shiftsIndex(Request $request)
{
    $query = ShiftSaisie::with(['station', 'user', 'pompeDetails', 'depenses'])
        ->orderBy('date_shift', 'desc')
        ->orderBy('shift', 'desc');
    
    // Filtres
    if ($request->filled('station_id')) {
        $query->where('station_id', $request->station_id);
    }
    
    if ($request->filled('status')) {
        $query->where('statut', $request->status);
    }
    
    if ($request->filled('date_from')) {
        $query->whereDate('date_shift', '>=', $request->date_from);
    }
    
    if ($request->filled('date_to')) {
        $query->whereDate('date_shift', '<=', $request->date_to);
    }
    
    if ($request->filled('user_id')) {
        $query->where('user_id', $request->user_id);
    }
    
    $shifts = $query->paginate(25);
    
    $stats = [
        'total' => $shifts->total(),
        'validated' => ShiftSaisie::where('statut', 'valide')->count(),
        'pending' => ShiftSaisie::where('statut', 'en_attente')->count(),
        'rejected' => ShiftSaisie::where('statut', 'rejete')->count(),
        'total_sales' => ShiftSaisie::where('statut', 'valide')->sum('total_ventes'),
    ];
    
    $stations = Station::all();
    $users = User::role(['manager', 'chief'])->get();
    
    return view('admin.shifts.index', compact('shifts', 'stats', 'stations', 'users'));
}

/**
 * Afficher un shift spécifique
 */
public function showShift(ShiftSaisie $shift)
{
    $shift->load(['station', 'user', 'pompeDetails', 'depenses']);
    
    // Calculer les totaux par type de carburant
    $fuelTotals = [];
    foreach ($shift->pompeDetails as $detail) {
        $fuelType = strtolower($detail->carburant);
        if (!isset($fuelTotals[$fuelType])) {
            $fuelTotals[$fuelType] = [
                'litres' => 0,
                'amount' => 0
            ];
        }
        $fuelTotals[$fuelType]['litres'] += $detail->litrage_vendu;
        $fuelTotals[$fuelType]['amount'] += $detail->montant_ventes;
    }
    
    return view('admin.shifts.show', compact('shift', 'fuelTotals'));
}

/**
 * Éditer un shift
 */
public function editShift(ShiftSaisie $shift)
{
    $shift->load(['pompeDetails', 'depenses']);
    $stations = Station::all();
    
    return view('admin.shifts.edit', compact('shift', 'stations'));
}

/**
 * Mettre à jour un shift
 */
public function updateShift(Request $request, ShiftSaisie $shift)
{
    $validated = $request->validate([
        'ecart_final' => 'required|numeric',
        'commentaire_validation' => 'nullable|string|max:500',
        'statut' => 'required|in:valide,en_attente,rejete'
    ]);
    
    $shift->update([
        'ecart_final' => $validated['ecart_final'],
        'commentaire_validation' => $validated['commentaire_validation'],
        'statut' => $validated['statut'],
        'valide_par' => auth()->id(),
        'valide_le' => now(),
    ]);
    
    ActivityLog::create([
        'user_id' => auth()->id(),
        'action' => 'shift_updated',
        'description' => "Shift #{$shift->id} mis à jour",
        'details' => json_encode($shift->toArray())
    ]);
    
    return redirect()->route('admin.shifts.show', $shift)
        ->with('success', 'Shift mis à jour avec succès!');
}

/**
 * Supprimer un shift
 */
public function destroyShift(ShiftSaisie $shift)
{
    // Sauvegarder les données pour le log
    $shiftData = $shift->toArray();
    
    // Supprimer les détails des pompes
    $shift->pompeDetails()->delete();
    
    // Supprimer les dépenses
    $shift->depenses()->delete();
    
    // Supprimer le shift
    $shift->delete();
    
    ActivityLog::create([
        'user_id' => auth()->id(),
        'action' => 'shift_deleted',
        'description' => "Shift #{$shiftData['id']} supprimé",
        'details' => json_encode($shiftData)
    ]);
    
    return redirect()->route('admin.shifts.index')
        ->with('success', 'Shift supprimé avec succès!');
}

    
public function create()
{
    // Récupérer les rôles
    try {
        $roles = \Spatie\Permission\Models\Role::all();
    } catch (\Exception $e) {
        // Fallback si le package n'est pas installé
        $roles = collect([
            (object)['id' => 1, 'name' => 'admin', 'display_name' => 'Administrateur'],
            (object)['id' => 2, 'name' => 'manager', 'display_name' => 'Manager'],
            (object)['id' => 3, 'name' => 'chief', 'display_name' => 'Chef des opérations'],
            
        ]);
    }
    
    // Récupérer toutes les stations avec leur manager actuel
    $stations = Station::with('manager')->get();
    
    return view('admin.users.create', compact('roles', 'stations'));
}

public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:administrateur,manager,chief,user',
            'statut' => 'required|in:active,inactive,pending',
            'station_id' => 'nullable|exists:stations,id',
            'send_welcome_email' => 'nullable|in:0,1',
        ]);
        
        // Générer un mot de passe temporaire
        $password = \Str::random(10);
        
        // IMPORTANT: S'assurer que l'utilisateur est actif
        $isActive = $validated['statut'] == 'active';
        
        // Logique d'assignation de station
        $stationId = $validated['station_id'] ?? null;
        
        // Pour les managers, vérifier la station
        if ($validated['role'] === 'manager' && $stationId) {
            $existingManager = User::role('manager')
                ->where('station_id', $stationId)
                ->first();
                
            if ($existingManager) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Cette station a déjà un manager. Veuillez choisir une autre station.');
            }
        }
        
        // CRÉER L'UTILISATEUR AVEC TOUS LES CHAMPS NÉCESSAIRES
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($password),
            'role' => $validated['role'],
            'statut' => $validated['statut'],
            'station_id' => $stationId,
            'is_active' => $isActive,
            'email_verified_at' => now(), // ← IMPORTANT: Marquer l'email comme vérifié
            'created_by' => auth()->id(),
        ]);
        
        // Vérifier que le mot de passe est bien stocké
        if (empty($user->password)) {
            \Log::error('Le mot de passe n\'a pas été enregistré pour l\'utilisateur: ' . $user->id);
            throw new \Exception('Erreur lors de l\'enregistrement du mot de passe');
        }
        
        // Assigner le rôle avec spatie
        try {
            $user->assignRole($validated['role']);
        } catch (\Exception $e) {
            \Log::error('Erreur assignation rôle spatie: ' . $e->getMessage());
        }
        
        // Si manager avec station, mettre à jour la station
        if ($validated['role'] === 'manager' && $stationId) {
            Station::where('id', $stationId)->update(['manager_id' => $user->id]);
        }
        
        // ENVOYER L'EMAIL DE BIENVENUE
        if ($request->has('send_welcome_email') && $request->send_welcome_email == '1') {
            try {
                \Mail::to($user->email)->send(new \App\Mail\WelcomeUser($user, $password));
                \Log::info('Email de bienvenue envoyé à ' . $user->email);
            } catch (\Exception $e) {
                \Log::error('Erreur envoi email de bienvenue: ' . $e->getMessage());
            }
        }
        
        // Log de l'activité
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'user_created',
                'description' => "Utilisateur {$user->name} créé avec le rôle {$validated['role']}",
                'details' => json_encode([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'password' => $password, // Ne pas log en production
                    'role' => $validated['role'],
                    'station_id' => $stationId,
                    'welcome_email_sent' => ($request->send_welcome_email == '1')
                ])
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur création log: ' . $e->getMessage());
        }
        
        // Message de succès
        $message = 'Utilisateur créé avec succès.';
        if ($request->send_welcome_email == '1') {
            $message .= ' Un email de bienvenue a été envoyé.';
        } else {
            $message .= ' Mot de passe temporaire: ' . $password;
        }
        
        return redirect()->route('admin.users.show', $user)
            ->with('success', $message);
            
    } catch (\Illuminate\Validation\ValidationException $e) {
        return redirect()->back()
            ->withInput()
            ->withErrors($e->errors());
    } catch (\Exception $e) {
        \Log::error('Erreur création utilisateur: ' . $e->getMessage());
        return redirect()->back()
            ->withInput()
            ->with('error', 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage());
    }
}
    
public function update(Request $request, User $user)
{
    // Debug : Voir ce que le formulaire envoie réellement
    \Log::info('Données reçues pour update utilisateur:', $request->all());
    
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'role' => 'required|exists:roles,name',
        'station_id' => 'nullable|exists:stations,id',
        // ✅ SOLUTION : Accepter 'status' OU 'statut'
        'status' => 'nullable|in:active,inactive,pending',
        'statut' => 'nullable|in:active,inactive,pending',
    ]);
    
    // Déterminer la valeur du statut (peu importe le nom du champ)
    $statusValue = $validated['status'] ?? $validated['statut'] ?? null;
    
    // Vérifier que le statut est fourni
    if (!$statusValue) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Le champ statut est requis.');
    }
    
    // Convertir en 'is_active'
    $isActive = $statusValue == 'active';
    
    // Logique d'assignation de station
    $stationId = $validated['station_id'] ?? null;
    
    // Si c'est un manager, vérifier la station
    if ($validated['role'] === 'manager' && $stationId) {
        $existingManager = User::role('manager')
            ->where('station_id', $stationId)
            ->where('id', '!=', $user->id)
            ->first();
            
        if ($existingManager) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cette station a déjà un manager. Veuillez choisir une autre station.');
        }
    }
    
    // Sauvegarder l'ancienne station
    $oldStationId = $user->station_id;
    
    // Mettre à jour l'utilisateur
    $user->update([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'role' => $validated['role'],
        'station_id' => $stationId,
        'is_active' => $isActive,
        'statut' => $statusValue,
    ]);
    
    // Synchroniser les rôles spatie
    $user->syncRoles([$validated['role']]);
    
    // Mettre à jour les stations
    if ($validated['role'] === 'manager') {
        // Désaffecter l'ancienne station si différente
        if ($oldStationId && $oldStationId != $stationId) {
            Station::where('id', $oldStationId)->update(['manager_id' => null]);
        }
        
        // Affecter la nouvelle station
        if ($stationId) {
            Station::where('id', $stationId)->update(['manager_id' => $user->id]);
        }
    } else {
        // Si ce n'est plus un manager, désaffecter la station
        if ($oldStationId) {
            Station::where('id', $oldStationId)->update(['manager_id' => null]);
        }
    }
    
    return redirect()->route('admin.users.show', $user)
        ->with('success', 'Utilisateur mis à jour avec succès.');
}
    
    public function destroy(User $user)
    {
        // Vérifier que l'utilisateur ne se supprime pas lui-même
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        
        // Supprimer les rôles
        $user->roles()->detach();
        
        // Désactiver plutôt que supprimer
        $user->update([
            'statut' => 'inactive',
            'is_active' => false,
            'email' => $user->email . '_deleted_' . time(),
        ]);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur désactivé avec succès.');
    }
    
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'send_email' => 'required|in:0,1',
        ]);
        
        // Générer un nouveau mot de passe temporaire
        $password = \Str::random(10);
        
        // Mettre à jour le mot de passe
        $user->update([
            'password' => bcrypt($password),
        ]);
        
        // Envoyer l'email si demandé
        if ($request->send_email == '1') {
            // Code pour envoyer l'email avec le nouveau mot de passe
            // Mail::to($user->email)->send(new PasswordResetMail($password));
        }
        
        return redirect()->back()
            ->with('success', 'Mot de passe réinitialisé. Nouveau mot de passe: ' . $password);
    }

    
    /**
     * Créer un utilisateur
     */
    public function createUser()
    {
        $stations = Station::all();
        $roles = ['admin', 'manager', 'chief', 'user'];
        
        return view('admin.users.create', compact('stations', 'roles'));
    }
    
    /**
     * Stocker un utilisateur
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,manager,chief,user',
            'station_id' => 'nullable|exists:stations,id',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'station_id' => $validated['station_id'],
            'password' => bcrypt($validated['password']),
            'created_by' => Auth::id(),
        ]);
        
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'user_created',
            'description' => "Utilisateur {$user->name} créé",
            'details' => json_encode($user->toArray())
        ]);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès!');
    }
    
    /**
     * Journal système
     */
  

/**
 * Afficher le formulaire de création d'une station
 */
public function createStation()
{
    // Récupérer seulement les managers DISPONIBLES (sans station)
    $managers = User::role('manager')
        ->where(function($q) {
            $q->whereNull('station_id')
              ->orWhere('station_id', 0);
        })
        ->with('station') // Charger pour info
        ->orderBy('name')
        ->get();
    
    // Si aucun manager disponible, proposer d'en créer un
    if ($managers->isEmpty()) {
        \Log::warning('Aucun manager disponible pour création de station');
    }
    
    $villes = Station::distinct()->pluck('ville')->filter()->values();
    
    return view('admin.stations.create', compact('managers', 'villes'));
}


/**
 * Stocker une nouvelle station
 */
public function storeStation(Request $request)
{
    \Log::info('Création station - Données reçues:', $request->all());
    
    $validated = $request->validate([
        'nom' => 'required|string|max:255',
        'code' => 'required|string|max:50|unique:stations,code',
        'adresse' => 'required|string',
        'ville' => 'required|string',
        'telephone' => 'nullable|string|max:20',
        'manager_id' => 'nullable|exists:users,id',
        'statut' => 'required|in:actif,inactif,maintenance',
        'capacite_super' => 'nullable|numeric|min:0',
        'capacite_gazole' => 'nullable|numeric|min:0',
        'capacite_essence_pirogue' => 'nullable|numeric|min:0',
    ]);
    
    // VÉRIFICATION CRITIQUE : Le manager est-il disponible ?
    if (!empty($validated['manager_id'])) {
        $existingAssignment = Station::where('manager_id', $validated['manager_id'])->first();
        if ($existingAssignment) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ce manager est déjà assigné à la station : ' . $existingAssignment->nom);
        }
        
        // Vérifier aussi que le user a bien le rôle manager
        $managerUser = User::find($validated['manager_id']);
        if (!$managerUser || !$managerUser->hasRole('manager')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cet utilisateur n\'a pas le rôle manager');
        }
    }
    
    try {
        // Créer la station
        $station = Station::create([
            'nom' => $validated['nom'],
            'code' => strtoupper($validated['code']),
            'adresse' => $validated['adresse'],
            'ville' => $validated['ville'],
            'telephone' => $validated['telephone'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'statut' => $validated['statut'],
            'capacite_super' => $validated['capacite_super'] ?? 0,
            'capacite_gazole' => $validated['capacite_gazole'] ?? 0,
            'capacite_essence_pirogue' => $validated['capacite_essence_pirogue'] ?? 0,
        ]);
        
        // Mettre à jour le manager
        if (!empty($validated['manager_id'])) {
            User::where('id', $validated['manager_id'])->update([
                'station_id' => $station->id,
            ]);
        }
        
        return redirect()->route('admin.stations.show', $station)
            ->with('success', 'Station créée avec succès!');
            
    } catch (\Exception $e) {
        \Log::error('Erreur création station: ' . $e->getMessage());
        return redirect()->back()
            ->withInput()
            ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
    }
}
public function edit(User $user)
{
    // Empêcher l'édition de son propre compte
    if ($user->id === auth()->id()) {
        return redirect()->route('admin.users.show', $user)
            ->with('warning', 'Utilisez votre profil pour modifier votre compte.');
    }
    
    try {
        // Déterminer le type de rôles
        $roles = [];
        
        // Essayer spatie/permission d'abord
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            $spatieRoles = \Spatie\Permission\Models\Role::all();
            foreach ($spatieRoles as $role) {
                $roles[$role->name] = $role->name;
            }
        }
        

        
        // Récupérer toutes les stations
        $stations = \App\Models\Station::all();
        
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $roles, // Tableau associatif [valeur => label]
            'stations' => $stations,
            'currentRole' => $user->role ?? 'user'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Erreur dans edit user: ' . $e->getMessage());
        return redirect()->route('admin.users.index')
            ->with('error', 'Erreur lors du chargement: ' . $e->getMessage());
    }
}
    public function show(User $user)
    {
        // Charger les relations nécessaires
        $user->load([
            'station', 
            'shifts' => function($query) {
                $query->orderBy('date_shift', 'desc')->take(10);
            }
        ]);
        
        // Vérifier si l'utilisateur a des rôles spatie/permission
        if (method_exists($user, 'getRoleNames')) {
            $userRoles = $user->getRoleNames();
        } else {
            // Fallback si spatie n'est pas utilisé
            $userRoles = collect([$user->role ?? 'user']);
        }
        
        // Statistiques de l'utilisateur
        $stats = [
            'total_shifts' => $user->shifts()->count(),
            'total_sales' => $user->shifts()->where('statut', 'valide')->sum('total_ventes'),
            'pending_shifts' => $user->shifts()->where('statut', 'en_attente')->count(),
            'rejected_shifts' => $user->shifts()->where('statut', 'rejete')->count(),
            'avg_ecart' => $user->shifts()->where('statut', 'valide')->avg('ecart_final'),
            'shifts_this_month' => $user->shifts()
                ->whereMonth('date_shift', now()->month)
                ->count(),
            'sales_this_month' => $user->shifts()
                ->where('statut', 'valide')
                ->whereMonth('date_shift', now()->month)
                ->sum('total_ventes'),
        ];
        
        // Activités récentes de l'utilisateur
        $recentActivities = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        // Derniers shifts
        $recentShifts = $user->shifts()
            ->with('station')
            ->orderBy('date_shift', 'desc')
            ->take(5)
            ->get();
        
        // Stations disponibles pour assignation (si l'utilisateur est manager)
        $availableStations = collect();
        if ($user->hasRole('manager') || $user->role === 'manager') {
            $availableStations = Station::where(function($query) use ($user) {
                $query->whereNull('manager_id')
                      ->orWhere('manager_id', $user->id);
            })->get();
        }
        
        return view('admin.users.show', compact(
            'user', 
            'userRoles',
            'stats', 
            'recentActivities',
            'recentShifts',
            'availableStations'
        ));
    }

/**
 * Afficher le formulaire d'édition d'une station
 */
public function editStation($id)
{
    $station = Station::findOrFail($id);
    $managers = User::role('manager')->get();
    
    return view('admin.stations.edit', compact('station', 'managers'));
}

/**
 * Mettre à jour une station
 */
public function updateStation(Request $request, $id)
{
    $station = Station::findOrFail($id);
    
    $validated = $request->validate([
        'nom' => 'required|string|max:255',
        'code' => 'required|string|max:50|unique:stations,code,' . $station->id,
        'adresse' => 'required|string',
        'ville' => 'required|string',
        'telephone' => 'nullable|string|max:20',
        // PAS de email ici
        'responsable_id' => 'nullable|exists:users,id',
        'statut' => 'required|in:actif,inactif,maintenance',
        'capacite_super' => 'nullable|numeric|min:0',
        'capacite_gazole' => 'nullable|numeric|min:0',
        'capacite_essence_pirogue' => 'nullable|numeric|min:0',
        'ouverture' => 'nullable|date_format:H:i',
        'fermeture' => 'nullable|date_format:H:i',
        'notes' => 'nullable|string|max:1000',
    ]);

    // Sauvegarder l'ancien manager
    $oldManagerId = $station->manager_id;
    
    // Mettre à jour la station
    $station->update([
        'nom' => $validated['nom'],
        'code' => strtoupper($validated['code']),
        'adresse' => $validated['adresse'],
        'ville' => $validated['ville'],
        'telephone' => $validated['telephone'],
        'responsable_id' => $validated['responsable_id'],
        'statut' => $validated['statut'],
        'capacite_super' => $validated['capacite_super'] ?? $station->capacite_super,
        'capacite_gazole' => $validated['capacite_gazole'] ?? $station->capacite_gazole,
        'capacite_essence_pirogue' => $validated['capacite_essence_pirogue'] ?? $station->capacite_essence_pirogue,
        'ouverture' => $validated['ouverture'],
        'fermeture' => $validated['fermeture'],
        'notes' => $validated['notes'] ?? null,
        'updated_by' => auth()->id(),
    ]);

    // Mettre à jour les managers
    if ($oldManagerId && $oldManagerId != $validated['manager_id']) {
        User::where('id', $oldManagerId)->update(['station_id' => null]);
    }
    
    if ($validated['manager_id'] && $oldManagerId != $validated['manager_id']) {
        User::where('id', $validated['manager_id'])->update([
            'station_id' => $station->id,
        ]);
    }
    
    ActivityLog::create([
        'user_id' => auth()->id(),
        'action' => 'station_updated',
        'description' => "Station {$station->nom} mise à jour",
        'details' => json_encode($station->toArray())
    ]);
    
    return redirect()->route('admin.stations.show', $station)
        ->with('success', 'Station mise à jour avec succès!');
}

/**
 * Supprimer une station
 */
public function destroyStation($id)
{
    $station = Station::findOrFail($id);
    
    // Vérifier si la station a des données associées
    $hasShifts = $station->shifts()->exists();
    $hasUsers = $station->users()->exists();
    
    if ($hasShifts || $hasUsers) {
        return redirect()->back()
            ->with('error', 'Impossible de supprimer cette station car elle a des données associées.');
    }
    
    // Désaffecter le manager
    if ($station->manager_id) {
        User::where('id', $station->manager_id)->update(['station_id' => null]);
    }
    
    // Sauvegarder les données pour le log
    $stationData = $station->toArray();
    
    // Supprimer la station
    $station->delete();
    
    ActivityLog::create([
        'user_id' => auth()->id(),
        'action' => 'station_deleted',
        'description' => "Station {$stationData['nom']} supprimée",
        'details' => json_encode($stationData)
    ]);
    
    return redirect()->route('admin.stations.index')
        ->with('success', 'Station supprimée avec succès!');
}
}