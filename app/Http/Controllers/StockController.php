<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\TankLevel;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    /**
     * Afficher le tableau de bord des stocks
     */
    public function dashboard()
{
    // Supprimé 'premium' si la station ne le vend pas
    $fuelTypes = ['super', 'gazole']; 
    
    $stocks = [];
    foreach ($fuelTypes as $type) {
        $stocks[$type] = [
            'current' => StockMovement::currentStock($type),
            'last_month' => $this->getStockOneMonthAgo($type),
            'variation' => $this->calculateStockVariation($type),
            'last_reception' => $this->getLastReception($type),
            'last_inventory' => $this->getLastInventory($type),
        ];
    }
    
    // CORRECTION : Utiliser 'recorder' au lieu de 'enregistreur'
    $latestMovements = StockMovement::with(['recorder', 'shift'])
        ->orderBy('movement_date', 'desc')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
        
    $latestTankLevels = TankLevel::with('measurer')
        ->latestMeasurements(5)
        ->get();
        
    $alerts = $this->getStockAlerts();
    
    // Ajoutez ceci pour les ventes récentes
    $recentSales = Sale::with('recorder')
        ->whereNull('cancelled_at')
        ->orderBy('sale_date', 'desc')
        ->limit(10)
        ->get();
        
    $salesStats = [
        'today' => Sale::whereDate('sale_date', today())
            ->whereNull('cancelled_at')
            ->sum('quantity'),
        'this_week' => Sale::whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNull('cancelled_at')
            ->sum('quantity'),
        'this_month' => Sale::whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->whereNull('cancelled_at')
            ->sum('quantity'),
    ];
    
    return view('manager.stocks.dashboard', compact(
        'stocks', 
        'latestMovements', 
        'latestTankLevels', 
        'alerts',
        'recentSales',
        'salesStats'
    ));
}
    
    /**
     * Afficher le formulaire d'enregistrement de réception
     */
    public function createReception()
    {
        $fuelTypes = $this->getFuelTypes();
        $tanks = $this->getTanks();
        
        return view('manager.stocks.receptions.create', compact('fuelTypes', 'tanks'));
    }
    
    /**
     * Enregistrer une réception
     */
    public function storeReception(Request $request)
    {
        // 1. Validation des données
        $data = $request->validate([
            'reception_date' => 'required|date',
            'fuel_type' => 'required|string|in:super,gazole', 
            'tank_number' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:1', // Quantité doit être positive
            'unit_price' => 'required|numeric|min:0',
            'supplier_name' => 'required|string|max:255',
            'invoice_number' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Assurez-vous que l'utilisateur est connecté pour avoir un 'recorded_by'
        if (!Auth::check()) {
            return redirect()->back()->with('error', 'Vous devez être connecté pour enregistrer cette réception.');
        }
        
        DB::beginTransaction();
        
        try {
            
            // 2. Calcul des champs dérivés
            
            // Stock avant l'opération (utilise la méthode statique du modèle)
            $stockBefore = StockMovement::currentStock($data['fuel_type']);
            
            // Stock après l'opération (réception = addition)
            $stockAfter = $stockBefore + $data['quantity'];
            
            // Montant total de la réception
            $totalAmount = $data['quantity'] * $data['unit_price'];

            // 3. Enregistrement du Mouvement de Stock
            $movement = StockMovement::create([
                'movement_date' => Carbon::parse($data['reception_date']),
                'fuel_type' => $data['fuel_type'],
                'movement_type' => 'reception', // Type fixe pour une réception
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
                'total_amount' => $totalAmount,
                'supplier_name' => $data['supplier_name'],
                'invoice_number' => $data['invoice_number'],
                'tank_number' => $data['tank_number'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $data['notes'],
                'recorded_by' => Auth::id(),
                // 'verified_by' et 'verified_at' restent NULL initialement
            ]);
            
            DB::commit();
            
            // 4. Redirection et message de succès
            return redirect()->route('manager.stocks.dashboard')
                ->with('success', "Réception de {$data['quantity']} L de {$data['fuel_type']} enregistrée avec succès! Le stock est maintenant de {$stockAfter} L.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // 5. Gestion des erreurs
            // Pour le débogage, vous pouvez temporairement afficher l'erreur.
            // echo $e->getMessage(); die;
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'enregistrement de la réception. Veuillez vérifier les logs. Message: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher le formulaire de jaugeage
     */
    public function createTankLevel()
    {
        $tanks = [
            'C1' => ['fuel_type' => 'super', 'capacity' => 30000, 'description' => 'Cuve 1 - Super', 'theoretical_stock' => StockMovement::currentStock('super')],
            'C2' => ['fuel_type' => 'gazole', 'capacity' => 30000, 'description' => 'Cuve 2 - Gazole', 'theoretical_stock' => StockMovement::currentStock('gazole')],
            'C4' => ['fuel_type' => 'gazole', 'capacity' => 25000, 'description' => 'Cuve 4 - Réserve Gazole', 'theoretical_stock' => StockMovement::currentStock('gazole')],
        ];
        
        return view('manager.stocks.tank-levels.create', compact('tanks'));
        }
    
    /**
     * Enregistrer un jaugeage
     */
   public function storeTankLevel(Request $request)
{
    $request->validate([
        'measurement_date' => 'required|date',
        'tank_number' => 'required|string|max:50',
        'level_cm' => 'required|numeric|min:0',
        'temperature_c' => 'nullable|numeric|min:-50|max:100',
        'theoretical_stock' => 'required|numeric|min:0',
        'observations' => 'nullable|string|max:500',
    ]);
    
    // Récupérer les infos de la cuve
    $tank = $this->getTankDetails($request->tank_number);
    
    // Calculer le volume à partir du niveau
    $volumeLiters = $this->calculateVolumeFromLevel($request->tank_number, $request->level_cm);
    
    // Calculer la différence
    $difference = $volumeLiters - $request->theoretical_stock;
    $differencePercentage = $request->theoretical_stock > 0 
        ? ($difference / $request->theoretical_stock) * 100 
        : 0;
    
    DB::beginTransaction();
    
    try {
        $tankLevel = TankLevel::create([
            'measurement_date' => $request->measurement_date,
            'tank_number' => $request->tank_number,
            'fuel_type' => $tank['fuel_type'],
            'level_cm' => $request->level_cm,
            'temperature_c' => $request->temperature_c,
            'volume_liters' => $volumeLiters,
            'theoretical_stock' => $request->theoretical_stock,
            'physical_stock' => $volumeLiters,
            'difference' => $difference,
            'difference_percentage' => $differencePercentage,
            'observations' => $request->observations,
            'measured_by' => Auth::id(),
        ]);
        
        DB::commit();
        
        // Vérifier si l'écart dépasse le seuil
        if (abs($differencePercentage) > 1.0) {
            // Option 1: Utiliser les logs Laravel natifs
            \Log::warning('Écart de stock détecté lors du jaugeage', [
                'tank_level_id' => $tankLevel->id,
                'tank_number' => $request->tank_number,
                'fuel_type' => $tank['fuel_type'],
                'difference_percentage' => $differencePercentage,
                'measured_by' => Auth::id(),
                'threshold' => 1.0
            ]);
            
            // Option 2: Créer un enregistrement dans une table d'alertes
            // \App\Models\StockAlert::create([
            //     'type' => 'stock_discrepancy',
            //     'tank_level_id' => $tankLevel->id,
            //     'tank_number' => $request->tank_number,
            //     'fuel_type' => $tank['fuel_type'],
            //     'difference_percentage' => $differencePercentage,
            //     'measured_by' => Auth::id(),
            //     'status' => 'pending',
            // ]);
        }
        
        return redirect()->route('manager.stocks.dashboard')
            ->with('success', 'Jaugeage enregistré avec succès!')
            ->with('alert', abs($differencePercentage) > 1.0 ? 
                'Écart de stock détecté: ' . round($differencePercentage, 2) . '%' : null);
            
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
    }
}
    
    /**
     * Rapport de réconciliation
     */
   public function reconciliationReport(Request $request)
{
    $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
    $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
    $fuelType = $request->input('fuel_type');
    
    // CORRECTION : Utiliser 'recorder' au lieu de 'enregistreur'
    $query = StockMovement::with(['recorder', 'verifier'])
        ->whereBetween('movement_date', [$startDate, $endDate])
        ->orderBy('movement_date');
        
    if ($fuelType) {
        $query->where('fuel_type', $fuelType);
    }
    
    $movements = $query->get();
    
    // Calculer les totaux
    $totals = [
        'receptions' => $movements->where('movement_type', 'reception')->sum('quantity'),
        'sales' => abs($movements->where('movement_type', 'vente')->sum('quantity')),
        'adjustments' => $movements->where('movement_type', 'ajustement')->sum('quantity'),
    ];
    
    $fuelTypes = $this->getFuelTypes();
    
    return view('manager.stocks.reports.reconciliation', compact('movements', 'totals', 'fuelTypes', 'startDate', 'endDate', 'fuelType'));
}
    
    /**
     * Rapport d'inventaire
     */
    public function inventoryReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
        $tankLevels = TankLevel::with(['measurer', 'verifier'])
            ->whereBetween('measurement_date', [$startDate, $endDate])
            ->orderBy('measurement_date', 'desc')
            ->get();
            
        // Statistiques
        $stats = [
            'total_measurements' => $tankLevels->count(),
            'average_difference' => $tankLevels->avg('difference_percentage'),
            'max_difference' => $tankLevels->max('difference_percentage'),
            'discrepancies' => $tankLevels->where('difference_percentage', '>', 1.0)
                ->orWhere('difference_percentage', '<', -1.0)
                ->count(),
        ];
        
        return view('manager.stocks.reports.inventory', compact('tankLevels', 'stats', 'startDate', 'endDate'));
    }
    
    // Méthodes helper privées
    private function getFuelTypes()
    {
        // SUPPRESSION DE 'premium'
        return [
            'super' => 'Super',
            'gazole' => 'Gazole', 
        ];
    }
    
    private function getTanks()
    {
        return [
            'C1' => 'Cuve 1 - Super',
            'C2' => 'Cuve 2 - Gazole',
            // Si C4 ne contient que du Gazole, c'est OK
            'C4' => 'Cuve 4 - Réserve', 
        ];
    }
    
    private function getTanksWithDetails()
    {
        return [
            'C1' => ['fuel_type' => 'super', 'capacity' => 30000, 'description' => 'Cuve 1 - Super'],
            'C2' => ['fuel_type' => 'gazole', 'capacity' => 30000, 'description' => 'Cuve 2 - Gazole'],
            'C4' => ['fuel_type' => 'gazole', 'capacity' => 25000, 'description' => 'Cuve 4 - Réserve Gazole'],
        ];
    }
    
    private function getTankDetails($tankNumber)
    {
        $tanks = $this->getTanksWithDetails();
        return $tanks[$tankNumber] ?? ['fuel_type' => 'unknown', 'capacity' => 0];
    }
    
    private function calculateVolumeFromLevel($tankNumber, $levelCm)
    {
        // Cette méthode devrait utiliser les spécifications techniques de la cuve
        // Pour l'exemple, on utilise une formule simple
        $tank = $this->getTankDetails($tankNumber);
        $capacity = $tank['capacity'];
        
        // Simuler une cuve cylindrique
        // Volume = niveau * capacité_max / hauteur_max
        // Pour l'exemple, on suppose une hauteur de 200 cm
        $maxHeight = 200;
        return ($levelCm / $maxHeight) * $capacity;
    }
    
    private function getStockOneMonthAgo($fuelType)
    {
        $oneMonthAgo = now()->subMonth();
        
        $movement = StockMovement::where('fuel_type', $fuelType)
            ->where('movement_date', '<=', $oneMonthAgo)
            ->orderBy('movement_date', 'desc')
            ->first();
            
        return $movement ? $movement->stock_after : 0;
    }
    
    private function calculateStockVariation($fuelType)
    {
        $current = StockMovement::currentStock($fuelType);
        $previous = $this->getStockOneMonthAgo($fuelType);
        
        if ($previous == 0) return 0;
        
        return (($current - $previous) / $previous) * 100;
    }
    
    private function getLastReception($fuelType)
    {
        return StockMovement::where('fuel_type', $fuelType)
            ->where('movement_type', 'reception')
            ->orderBy('movement_date', 'desc')
            ->first();
    }
    
    private function getLastInventory($fuelType)
    {
        return TankLevel::where('fuel_type', $fuelType)
            ->orderBy('measurement_date', 'desc')
            ->first();
    }
    
    private function getStockAlerts()
    {
        $alerts = [];
        
        // Vérifier les stocks bas
        $fuelTypes = $this->getFuelTypes();
        foreach ($fuelTypes as $key => $name) {
            $currentStock = StockMovement::currentStock($key);
            if ($currentStock < 5000) { // Seuil de 5000 litres
                $alerts[] = [
                    'type' => 'low_stock',
                    'fuel_type' => $name,
                    'current_stock' => $currentStock,
                    'message' => 'Stock bas: ' . number_format($currentStock, 0, ',', ' ') . ' litres',
                    'severity' => 'warning'
                ];
            }
        }
        
        // Vérifier les écarts de jaugeage récents
        $recentDiscrepancies = TankLevel::where('measurement_date', '>=', now()->subDays(7))
            ->where(function($q) {
                $q->where('difference_percentage', '>', 2.0)
                  ->orWhere('difference_percentage', '<', -2.0);
            })
            ->get();
            
        foreach ($recentDiscrepancies as $discrepancy) {
            $alerts[] = [
                'type' => 'discrepancy',
                'tank' => $discrepancy->tank_number,
                'difference' => $discrepancy->difference_percentage,
                'message' => 'Écart de ' . round($discrepancy->difference_percentage, 2) . '% sur la cuve ' . $discrepancy->tank_number,
                'severity' => 'danger'
            ];
        }
        
        return $alerts;
    }
}