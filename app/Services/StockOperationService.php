<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Models\TankLevel;
use App\Models\ShiftSaisie;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StockOperationService
{
    /**
     * Enregistrer une réception de carburant
     */
    public function registerReception(array $data)
    {
        DB::beginTransaction();
        
        try {
            // Vérifier que station_id est présent
            if (!isset($data['station_id'])) {
                $data['station_id'] = Auth::user()->station_id ?? 1;
            }
            
            $fuelType = $data['fuel_type'];
            $quantity = $data['quantity'];
            $unitPrice = $data['unit_price'];
            $stationId = $data['station_id'];
            
            // Calcul du montant total
            $totalAmount = $quantity * $unitPrice;
            
            // Utiliser la méthode recordReception du modèle
            $movement = StockMovement::recordReception([
                'station_id' => $stationId,
                'fuel_type' => $fuelType,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'supplier_name' => $data['supplier_name'] ?? null,
                'invoice_number' => $data['invoice_number'] ?? null,
                'tank_number' => $data['tank_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => Auth::id(),
                'movement_date' => $data['reception_date'] ?? now(),
            ]);
            
            // Journaliser
            \Log::info('Réception enregistrée', [
                'movement_id' => $movement->id,
                'fuel_type' => $fuelType,
                'quantity' => $quantity,
                'stock_before' => $movement->stock_before,
                'stock_after' => $movement->stock_after,
                'station_id' => $stationId,
                'user_id' => Auth::id()
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'movement' => $movement,
                'stock_before' => $movement->stock_before,
                'stock_after' => $movement->stock_after
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur réception carburant', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Enregistrer une vente (déduction de stock)
     */
    public function registerSale(array $data)
    {
        DB::beginTransaction();
        
        try {
            // Vérifier que station_id est présent
            if (!isset($data['station_id'])) {
                $data['station_id'] = Auth::user()->station_id ?? 1;
            }
            
            $fuelType = $data['fuel_type'];
            $quantity = $data['quantity'];
            $unitPrice = $data['unit_price'];
            $stationId = $data['station_id'];
            
            // Utiliser la méthode recordSale du modèle
            $movement = StockMovement::recordSale([
                'station_id' => $stationId,
                'fuel_type' => $fuelType,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $quantity * $unitPrice,
                'customer_name' => $data['customer_name'] ?? null,
                'customer_type' => $data['customer_type'] ?? 'divers',
                'payment_method' => $data['payment_method'] ?? null,
                'tank_number' => $data['tank_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => Auth::id(),
                'movement_date' => $data['sale_date'] ?? now(),
                'shift_saisie_id' => $data['shift_saisie_id'] ?? null,
                'auto_generated' => $data['auto_generated'] ?? false,
            ]);
            
            \Log::info('Vente enregistrée dans stock', [
                'movement_id' => $movement->id,
                'fuel_type' => $fuelType,
                'quantity' => $quantity,
                'stock_before' => $movement->stock_before,
                'stock_after' => $movement->stock_after,
                'station_id' => $stationId,
                'shift_saisie_id' => $data['shift_saisie_id'] ?? null
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'movement' => $movement,
                'stock_before' => $movement->stock_before,
                'stock_after' => $movement->stock_after
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur dans registerSale', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }
    
    /**
     * Enregistrer un ajustement de stock
     */
    public function registerAdjustment(array $data)
    {
        DB::beginTransaction();
        
        try {
            // Vérifier que station_id est présent
            if (!isset($data['station_id'])) {
                $data['station_id'] = Auth::user()->station_id ?? 1;
            }
            
            $fuelType = $data['fuel_type'];
            $quantity = $data['quantity']; // Peut être positif ou négatif
            $reason = $data['reason'];
            $stationId = $data['station_id'];
            
            // Obtenir le stock avant
            $stockBefore = StockMovement::currentStock($fuelType, $stationId);
            
            // Calculer le stock après
            $stockAfter = $stockBefore + $quantity;
            
            // Création du mouvement d'ajustement
            $movement = StockMovement::create([
                'station_id' => $stationId,
                'fuel_type' => $fuelType,
                'movement_type' => 'ajustement',
                'quantity' => $quantity,
                'unit_price' => 0,
                'total_amount' => 0,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => "Ajustement: $reason",
                'recorded_by' => Auth::id(),
                'movement_date' => $data['adjustment_date'] ?? now(),
                'auto_generated' => false,
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'movement' => $movement,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Obtenir l'historique des mouvements
     */
    public function getMovementHistory($fuelType = null, $startDate = null, $endDate = null)
    {
        try {
            $stationId = Auth::user()->station_id ?? null;
            
            $query = StockMovement::with(['recorder', 'verifier', 'station'])
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc');
            
            // Filtrer par type de carburant
            if ($fuelType) {
                // Normaliser le type de carburant
                $normalizedFuelType = strtolower(trim($fuelType));
                
                if ($normalizedFuelType == 'gasoil') {
                    $query->where(function($q) {
                        $q->where('fuel_type', 'gasoil')
                          ->orWhere('fuel_type', 'gazole');
                    });
                } else {
                    $query->where('fuel_type', $normalizedFuelType);
                }
            }
            
            // Filtrer par station
            if ($stationId) {
                $query->where('station_id', $stationId);
            }
            
            // Filtrer par date
            if ($startDate) {
                $query->whereDate('movement_date', '>=', Carbon::parse($startDate));
            }
            
            if ($endDate) {
                $query->whereDate('movement_date', '<=', Carbon::parse($endDate));
            }
            
            return $query->paginate(20);
            
        } catch (\Exception $e) {
            \Log::error('Erreur dans getMovementHistory', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return collect(); // Retourner une collection vide en cas d'erreur
        }
    }
    
    /**
     * Vérifier la cohérence des stocks
     */
    public function checkStockConsistency($fuelType)
    {
        try {
            $stationId = Auth::user()->station_id ?? null;
            
            // Récupérer le dernier mouvement
            $latestMovement = StockMovement::where(function($q) use ($fuelType) {
                    if ($fuelType === 'gasoil') {
                        $q->where('fuel_type', 'gasoil')
                          ->orWhere('fuel_type', 'gazole');
                    } else {
                        $q->where('fuel_type', $fuelType);
                    }
                })
                ->when($stationId, function($q) use ($stationId) {
                    return $q->where('station_id', $stationId);
                })
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$latestMovement) {
                return [
                    'consistent' => true,
                    'message' => 'Aucun mouvement trouvé',
                    'stock_after' => 0
                ];
            }
            
            // Calculer le stock manuellement
            $calculatedStock = $this->calculateStockManually($fuelType, $stationId);
            $actualStock = $latestMovement->stock_after;
            
            // Tolérance de 0.1 litre
            $isConsistent = abs($calculatedStock - $actualStock) < 0.1;
            
            return [
                'consistent' => $isConsistent,
                'calculated' => $calculatedStock,
                'actual' => $actualStock,
                'difference' => round($calculatedStock - $actualStock, 2),
                'difference_percentage' => $actualStock > 0 ? 
                    round(abs(($calculatedStock - $actualStock) / $actualStock) * 100, 2) : 0,
                'message' => $isConsistent ? 
                    '✅ Stock cohérent' : 
                    '⚠️ Incohérence détectée: ' . round(abs($calculatedStock - $actualStock), 2) . ' L (' . 
                    round(abs(($calculatedStock - $actualStock) / $actualStock) * 100, 2) . '%)'
            ];
            
        } catch (\Exception $e) {
            \Log::error('Erreur dans checkStockConsistency', [
                'error' => $e->getMessage(),
                'fuel_type' => $fuelType,
                'station_id' => Auth::user()->station_id ?? null
            ]);
            
            return [
                'consistent' => false,
                'message' => '❌ Erreur de vérification: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculer le stock manuellement (vérification)
     */
    private function calculateStockManually($fuelType, $stationId = null)
    {
        $query = StockMovement::where(function($q) use ($fuelType) {
            if ($fuelType === 'gasoil') {
                $q->where('fuel_type', 'gasoil')
                  ->orWhere('fuel_type', 'gazole');
            } else {
                $q->where('fuel_type', $fuelType);
            }
        });
        
        if ($stationId) {
            $query->where('station_id', $stationId);
        }
        
        $movements = $query->orderBy('movement_date', 'asc')
                          ->orderBy('created_at', 'asc')
                          ->get();
        
        $stock = 0;
        foreach ($movements as $movement) {
            $stock += $movement->quantity;
        }
        
        return $stock;
    }
    
    /**
     * Obtenir le bilan des stocks
     */
    public function getStockBalance()
    {
        try {
            $stationId = Auth::user()->station_id ?? null;
            $fuelTypes = ['super', 'gasoil'];
            $balance = [];
            
            foreach ($fuelTypes as $fuelType) {
                // Stock actuel
                $currentStock = StockMovement::currentStock($fuelType, $stationId);
                
                // Totaux du mois
                $startOfMonth = now()->startOfMonth();
                $endOfMonth = now()->endOfMonth();
                
                $query = StockMovement::where(function($q) use ($fuelType) {
                    if ($fuelType === 'gasoil') {
                        $q->where('fuel_type', 'gasoil')
                          ->orWhere('fuel_type', 'gazole');
                    } else {
                        $q->where('fuel_type', $fuelType);
                    }
                });
                
                if ($stationId) {
                    $query->where('station_id', $stationId);
                }
                
                $monthlyData = $query->whereBetween('movement_date', [$startOfMonth, $endOfMonth])
                    ->selectRaw('
                        COALESCE(SUM(CASE WHEN movement_type = "reception" THEN ABS(quantity) ELSE 0 END), 0) as total_receptions,
                        COALESCE(SUM(CASE WHEN movement_type = "vente" THEN ABS(quantity) ELSE 0 END), 0) as total_sales,
                        COALESCE(SUM(CASE WHEN movement_type = "ajustement" THEN quantity ELSE 0 END), 0) as total_adjustments
                    ')
                    ->first();
                
                // Dernier jaugeage
                $lastTankLevel = TankLevel::where('station_id', $stationId)
                    ->where(function($q) use ($fuelType) {
                        if ($fuelType === 'gasoil') {
                            $q->where('fuel_type', 'gasoil')
                              ->orWhere('fuel_type', 'gazole');
                        } else {
                            $q->where('fuel_type', $fuelType);
                        }
                    })
                    ->orderBy('measurement_date', 'desc')
                    ->first();
                
                // Consistance
                $consistency = $this->checkStockConsistency($fuelType);
                
                $balance[$fuelType] = [
                    'current' => (float) $currentStock,
                    'monthly_receptions' => (float) ($monthlyData->total_receptions ?? 0),
                    'monthly_sales' => (float) ($monthlyData->total_sales ?? 0),
                    'monthly_adjustments' => (float) ($monthlyData->total_adjustments ?? 0),
                    'net_variation' => (float) (
                        ($monthlyData->total_receptions ?? 0) - 
                        ($monthlyData->total_sales ?? 0) + 
                        ($monthlyData->total_adjustments ?? 0)
                    ),
                    'physical_stock' => $lastTankLevel ? (float) $lastTankLevel->physical_stock : null,
                    'theoretical_stock' => $lastTankLevel ? (float) $lastTankLevel->theoretical_stock : null,
                    'difference' => $lastTankLevel ? (float) $lastTankLevel->difference : null,
                    'difference_percentage' => $lastTankLevel ? (float) $lastTankLevel->difference_percentage : null,
                    'last_measurement_date' => $lastTankLevel ? $lastTankLevel->measurement_date : null,
                    'consistency' => $consistency,
                ];
                
                \Log::info('Stock Balance for ' . $fuelType, [
                    'station_id' => $stationId,
                    'current_stock' => $currentStock,
                    'consistency' => $consistency
                ]);
            }
            
            return $balance;
            
        } catch (\Exception $e) {
            \Log::error('Erreur dans getStockBalance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'super' => [
                    'current' => 0,
                    'monthly_receptions' => 0,
                    'monthly_sales' => 0,
                    'monthly_adjustments' => 0,
                    'net_variation' => 0,
                    'consistency' => ['consistent' => false, 'message' => 'Erreur de calcul']
                ],
                'gasoil' => [
                    'current' => 0,
                    'monthly_receptions' => 0,
                    'monthly_sales' => 0,
                    'monthly_adjustments' => 0,
                    'net_variation' => 0,
                    'consistency' => ['consistent' => false, 'message' => 'Erreur de calcul']
                ]
            ];
        }
    }
    
    /**
     * Obtenir le stock actuel pour toutes les stations
     */
    public function getStocksByStation()
    {
        try {
            // Obtenir toutes les stations
            $stations = \App\Models\Station::all();
            $result = [];
            
            foreach ($stations as $station) {
                $result[$station->id] = [
                    'station' => $station,
                    'super' => StockMovement::currentStock('super', $station->id),
                    'gasoil' => StockMovement::currentStock('gasoil', $station->id),
                    'last_activity' => StockMovement::where('station_id', $station->id)
                        ->orderBy('movement_date', 'desc')
                        ->value('movement_date')
                ];
            }
            
            return $result;
            
        } catch (\Exception $e) {
            \Log::error('Erreur dans getStocksByStation', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }
    
    /**
     * Analyser les écarts de stock
     */
    public function analyzeStockDiscrepancies()
    {
        try {
            $stationId = Auth::user()->station_id ?? null;
            $fuelTypes = ['super', 'gasoil'];
            $discrepancies = [];
            
            foreach ($fuelTypes as $fuelType) {
                // Dernier jaugeage
                $lastTankLevel = TankLevel::where('station_id', $stationId)
                    ->where(function($q) use ($fuelType) {
                        if ($fuelType === 'gasoil') {
                            $q->where('fuel_type', 'gasoil')
                              ->orWhere('fuel_type', 'gazole');
                        } else {
                            $q->where('fuel_type', $fuelType);
                        }
                    })
                    ->orderBy('measurement_date', 'desc')
                    ->first();
                
                if ($lastTankLevel) {
                    $discrepancy = abs($lastTankLevel->difference_percentage);
                    
                    $discrepancies[$fuelType] = [
                        'physical_stock' => $lastTankLevel->physical_stock,
                        'theoretical_stock' => $lastTankLevel->theoretical_stock,
                        'difference' => $lastTankLevel->difference,
                        'difference_percentage' => $lastTankLevel->difference_percentage,
                        'measurement_date' => $lastTankLevel->measurement_date,
                        'status' => $discrepancy <= 2 ? 'normal' : 
                                   ($discrepancy <= 5 ? 'warning' : 'critical')
                    ];
                }
            }
            
            return $discrepancies;
            
        } catch (\Exception $e) {
            \Log::error('Erreur dans analyzeStockDiscrepancies', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }
}