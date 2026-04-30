<?php
namespace App\Services;

use App\Models\StockMovement;
use App\Models\Sale;
use App\Models\ShiftSaisie;
use App\Models\Tank;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StockSyncService
{
    protected $stockOperationService;
    
    public function __construct(StockOperationService $stockOperationService)
    {
        $this->stockOperationService = $stockOperationService;
    }
    
    /**
     * VALIDER ET ENREGISTRER UNE VENTE EN TOUTE SÉCURITÉ
     */
    public function safeSaleRegistration(array $saleData, string $source = 'manual'): array
    {
        $lockKey = "stock_sync_{$saleData['station_id']}_{$saleData['fuel_type']}";
        
        return Cache::lock($lockKey, 15)->block(10, function() use ($saleData, $source) {
            return DB::transaction(function() use ($saleData, $source) {
                
                // 1. VÉRIFIER LE STOCK DISPONIBLE EN TEMPS RÉEL
                $availableStock = $this->getRealTimeAvailableStock(
                    $saleData['station_id'],
                    $saleData['fuel_type']
                );
                
                Log::info('Stock disponible en temps réel', [
                    'station_id' => $saleData['station_id'],
                    'fuel_type' => $saleData['fuel_type'],
                    'available_stock' => $availableStock,
                    'requested_quantity' => $saleData['quantity'],
                    'source' => $source
                ]);
                
                // 2. VALIDER LA QUANTITÉ
                if ($availableStock < $saleData['quantity']) {
                    throw new \Exception(sprintf(
                        "Stock insuffisant en temps réel. Disponible: %s L, Demandé: %s L",
                        number_format($availableStock, 2),
                        number_format($saleData['quantity'], 2)
                    ));
                }
                
                // 3. VÉRIFIER LES CONFLITS POTENTIELS
                $conflict = $this->checkPotentialConflict($saleData);
                if ($conflict['has_conflict']) {
                    throw new \Exception(sprintf(
                        "Conflit détecté: %s",
                        $conflict['message']
                    ));
                }
                
                // 4. ENREGISTRER LA VENTE SELON LA SOURCE
                $result = [];
                
                if ($source === 'manual') {
                    $result = $this->processManualSale($saleData);
                } elseif ($source === 'shift') {
                    $result = $this->processShiftSale($saleData);
                }
                
                // 5. METTRE À JOUR LE CACHE DE STOCK
                $this->updateStockCache($saleData['station_id'], $saleData['fuel_type']);
                
                Log::info('Vente enregistrée avec succès', [
                    'source' => $source,
                    'result' => $result,
                    'available_stock_after' => $this->getRealTimeAvailableStock(
                        $saleData['station_id'],
                        $saleData['fuel_type']
                    )
                ]);
                
                return $result;
                
            }, 5); // 5 tentatives
        });
    }
    
    /**
     * CALCULER LE STOCK DISPONIBLE EN TEMPS RÉEL
     */
    private function getRealTimeAvailableStock($stationId, $fuelType): float
    {
        // 1. Stock théorique depuis StockMovement
        $theoreticalStock = StockMovement::currentStock($fuelType, $stationId);
        
        // 2. Soustraire les ventes en cours (non encore confirmées)
        $pendingSales = $this->getPendingSales($stationId, $fuelType);
        
        // 3. Soustraire les ventes de shifts non traitées
        $pendingShiftSales = $this->getPendingShiftSales($stationId, $fuelType);
        
        $available = $theoreticalStock - $pendingSales - $pendingShiftSales;
        
        return max(0, $available);
    }
    
    /**
     * VÉRIFIER LES CONFLITS POTENTIELS
     */
    private function checkPotentialConflict(array $saleData): array
    {
        $stationId = $saleData['station_id'];
        $fuelType = $saleData['fuel_type'];
        $quantity = $saleData['quantity'];
        
        // 1. Vérifier les ventes manuelles simultanées
        $manualConflict = Sale::where('station_id', $stationId)
            ->where('fuel_type', $fuelType)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->where('recorded_by', $saleData['recorded_by'] ?? null)
            ->where('quantity', $quantity)
            ->whereNull('cancelled_at')
            ->first();
        
        if ($manualConflict) {
            return [
                'has_conflict' => true,
                'message' => 'Vente manuelle similaire déjà enregistrée il y a moins de 2 minutes',
                'conflict_id' => $manualConflict->id
            ];
        }
        
        // 2. Vérifier les shifts en cours pour le même type
        $shiftConflict = ShiftSaisie::where('station_id', $stationId)
            ->where('statut', 'en_cours') // Shift en cours
            ->where('date_shift', today())
            ->whereHas('stockMovements', function($query) use ($fuelType) {
                $query->where('fuel_type', $fuelType);
            })
            ->first();
        
        if ($shiftConflict) {
            return [
                'has_conflict' => true,
                'message' => 'Un shift est actuellement en cours pour ce type de carburant',
                'shift_id' => $shiftConflict->id
            ];
        }
        
        // 3. Vérifier si une cuve est en cours de jaugeage
        $tank = Tank::where('station_id', $stationId)
            ->where('fuel_type', $fuelType)
            ->whereNotNull('last_measured_at')
            ->where('last_measured_at', '>=', now()->subMinutes(5))
            ->first();
        
        if ($tank) {
            return [
                'has_conflict' => false,
                'warning' => 'Une cuve a été jaugée récemment. Vérifiez le stock physique.',
                'tank_id' => $tank->id
            ];
        }
        
        return ['has_conflict' => false, 'message' => 'Aucun conflit détecté'];
    }
    
    /**
     * TRAITER UNE VENTE MANUELLE
     */
    private function processManualSale(array $saleData): array
    {
        // 1. Enregistrer dans StockMovement
        $movement = $this->stockOperationService->registerSale([
            'station_id' => $saleData['station_id'],
            'fuel_type' => $saleData['fuel_type'],
            'quantity' => $saleData['quantity'],
            'unit_price' => $saleData['unit_price'],
            'customer_name' => $saleData['customer_name'] ?? 'Vente manuelle',
            'payment_method' => $saleData['payment_method'] ?? 'cash',
            'tank_number' => $saleData['tank_number'] ?? null,
            'recorded_by' => $saleData['recorded_by'],
            'movement_date' => $saleData['sale_date'] ?? now(),
            'auto_generated' => false
        ]);
        
        // 2. Créer l'enregistrement Sale
        $sale = Sale::create([
            'station_id' => $saleData['station_id'],
            'tank_id' => $saleData['tank_id'] ?? null,
            'fuel_type' => $saleData['fuel_type'],
            'tank_number' => $saleData['tank_number'] ?? null,
            'quantity' => $saleData['quantity'],
            'unit_price' => $saleData['unit_price'],
            'total_amount' => $saleData['quantity'] * $saleData['unit_price'],
            'sale_date' => $saleData['sale_date'] ?? now(),
            'customer_name' => $saleData['customer_name'] ?? null,
            'customer_type' => $saleData['customer_type'] ?? 'retail',
            'payment_method' => $saleData['payment_method'] ?? 'cash',
            'pump_number' => $saleData['pump_number'] ?? null,
            'notes' => $saleData['notes'] ?? null,
            'recorded_by' => $saleData['recorded_by'],
            'stock_movement_id' => $movement['movement']->id,
            'source' => 'manual'
        ]);
        
        // 3. Mettre à jour la cuve si spécifiée
        if (isset($saleData['tank_id'])) {
            $this->updateTankStock(
                $saleData['tank_id'],
                -$saleData['quantity']
            );
        }
        
        return [
            'success' => true,
            'type' => 'manual',
            'sale_id' => $sale->id,
            'movement_id' => $movement['movement']->id,
            'stock_before' => $movement['stock_before'],
            'stock_after' => $movement['stock_after']
        ];
    }
    
    /**
     * TRAITER UNE VENTE DE SHIFT
     */
    private function processShiftSale(array $saleData): array
    {
        // 1. Vérifier que le shift existe et est en attente
        $shift = ShiftSaisie::find($saleData['shift_id']);
        
        if (!$shift || $shift->statut !== 'en_attente') {
            throw new \Exception('Shift invalide ou déjà traité');
        }
        
        // 2. Marquer le mouvement comme auto-généré
        $movement = $this->stockOperationService->registerSale([
            'station_id' => $saleData['station_id'],
            'fuel_type' => $saleData['fuel_type'],
            'quantity' => $saleData['quantity'],
            'unit_price' => $saleData['unit_price'],
            'customer_name' => $shift->responsable . ' (Shift ' . $shift->shift . ')',
            'payment_method' => 'cash',
            'tank_number' => $saleData['tank_number'] ?? null,
            'recorded_by' => $saleData['recorded_by'] ?? $shift->user_id,
            'movement_date' => $shift->date_shift,
            'shift_saisie_id' => $shift->id,
            'auto_generated' => true
        ]);
        
        // 3. Créer un enregistrement Sale avec source=shift
        $sale = Sale::create([
            'station_id' => $saleData['station_id'],
            'tank_id' => $saleData['tank_id'] ?? null,
            'fuel_type' => $saleData['fuel_type'],
            'tank_number' => $saleData['tank_number'] ?? null,
            'quantity' => $saleData['quantity'],
            'unit_price' => $saleData['unit_price'],
            'total_amount' => $saleData['quantity'] * $saleData['unit_price'],
            'sale_date' => $shift->date_shift,
            'customer_name' => 'Shift ' . $shift->shift,
            'customer_type' => 'divers',
            'payment_method' => 'cash',
            'notes' => 'Vente automatique depuis shift #' . $shift->id,
            'recorded_by' => $saleData['recorded_by'] ?? $shift->user_id,
            'stock_movement_id' => $movement['movement']->id,
            'shift_saisie_id' => $shift->id,
            'source' => 'shift'
        ]);
        
        // 4. Mettre à jour la cuve
        if (isset($saleData['tank_id'])) {
            $this->updateTankStock(
                $saleData['tank_id'],
                -$saleData['quantity']
            );
        }
        
        // 5. Marquer le shift comme partiellement traité
        $this->markShiftProcessed($shift, $saleData['fuel_type'], $saleData['quantity']);
        
        return [
            'success' => true,
            'type' => 'shift',
            'sale_id' => $sale->id,
            'movement_id' => $movement['movement']->id,
            'shift_id' => $shift->id,
            'stock_before' => $movement['stock_before'],
            'stock_after' => $movement['stock_after']
        ];
    }
    
    /**
     * METTRE À JOUR LE STOCK D'UNE CUVE
     */
    private function updateTankStock($tankId, $quantity): void
    {
        $tank = Tank::find($tankId);
        if ($tank) {
            $tank->current_volume += $quantity;
            
            if ($tank->current_volume < 0) {
                $tank->current_volume = 0;
            }
            
            $tank->save();
            
            Log::info('Cuve mise à jour', [
                'tank_id' => $tankId,
                'quantity_change' => $quantity,
                'new_volume' => $tank->current_volume
            ]);
        }
    }
    
    /**
     * MARQUER UN SHIFT COMME TRAITÉ
     */
    private function markShiftProcessed(ShiftSaisie $shift, $fuelType, $quantity): void
    {
        // Ajouter une métadonnée au shift
        $processed = json_decode($shift->processed_fuel_types ?? '[]', true);
        $processed[] = [
            'fuel_type' => $fuelType,
            'quantity' => $quantity,
            'processed_at' => now()->toDateTimeString()
        ];
        
        $shift->update([
            'processed_fuel_types' => json_encode($processed),
            'stock_processed' => true
        ]);
    }
    
    /**
     * OBTENIR LES VENTES EN ATTENTE
     */
    private function getPendingSales($stationId, $fuelType): float
    {
        // Ventes manuelles en cours (créées il y a moins de 5 minutes)
        return Sale::where('station_id', $stationId)
            ->where('fuel_type', $fuelType)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->whereNull('cancelled_at')
            ->sum('quantity');
    }
    
    /**
     * OBTENIR LES VENTES DE SHIFTS EN ATTENTE
     */
    private function getPendingShiftSales($stationId, $fuelType): float
    {
        // Shifts en attente avec stock non traité
        return ShiftSaisie::where('station_id', $stationId)
            ->where('statut', 'en_attente')
            ->where('stock_processed', false)
            ->where('date_shift', '>=', now()->subDays(1))
            ->with(['pompeDetails' => function($query) use ($fuelType) {
                $query->where('carburant', 'LIKE', "%{$fuelType}%");
            }])
            ->get()
            ->sum(function($shift) {
                return $shift->pompeDetails->sum('litrage_vendu');
            });
    }
    
    /**
     * METTRE À JOUR LE CACHE DE STOCK
     */
    private function updateStockCache($stationId, $fuelType): void
    {
        $key = "stock_cache_{$stationId}_{$fuelType}";
        $availableStock = $this->getRealTimeAvailableStock($stationId, $fuelType);
        
        Cache::put($key, $availableStock, 60); // Cache 60 secondes
    }
    
    /**
     * VÉRIFIER LA COHÉRENCE DES STOCKS ENTRE SHIFTS ET VENTES MANUELLES
     */
    public function verifyStockConsistency($stationId = null): array
    {
        if (!$stationId) {
            $stationId = Auth::user()->station_id;
        }
        
        $fuelTypes = ['super', 'gasoil'];
        $inconsistencies = [];
        
        foreach ($fuelTypes as $fuelType) {
            // 1. Stock théorique
            $theoreticalStock = StockMovement::currentStock($fuelType, $stationId);
            
            // 2. Stock des ventes manuelles
            $manualSales = Sale::where('station_id', $stationId)
                ->where('fuel_type', $fuelType)
                ->whereNull('cancelled_at')
                ->where('source', 'manual')
                ->sum('quantity');
            
            // 3. Stock des shifts
            $shiftSales = Sale::where('station_id', $stationId)
                ->where('fuel_type', $fuelType)
                ->whereNull('cancelled_at')
                ->where('source', 'shift')
                ->sum('quantity');
            
            // 4. Stock calculé
            $calculatedStock = $manualSales + $shiftSales;
            
            // 5. Vérifier l'écart
            $difference = $theoreticalStock - $calculatedStock;
            
            if (abs($difference) > 0.01) { // Tolérance de 0.01 L
                $inconsistencies[$fuelType] = [
                    'theoretical' => $theoreticalStock,
                    'manual_sales' => $manualSales,
                    'shift_sales' => $shiftSales,
                    'calculated' => $calculatedStock,
                    'difference' => $difference,
                    'percentage' => $theoreticalStock > 0 ? 
                        abs(($difference / $theoreticalStock) * 100) : 0
                ];
            }
        }
        
        return [
            'consistent' => empty($inconsistencies),
            'inconsistencies' => $inconsistencies,
            'timestamp' => now()->toDateTimeString()
        ];
    }
}