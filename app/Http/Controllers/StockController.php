<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Tank;
use App\Models\TankLevel;
use App\Models\User;
use App\Models\Station;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Services\TankCalibrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\StockOperationService; 

class StockController extends Controller
{
    protected $stockService;
    protected $calibrationService;
    
    /**
     * Constructeur avec injection de dépendance
     */
    public function __construct(StockOperationService $stockService, TankCalibrationService $calibrationService) 
    {
        $this->stockService = $stockService;
        $this->calibrationService = $calibrationService;
        $this->middleware('auth');
        $this->middleware('role:manager|operations_chief|admin');
        
        // S'assurer que l'utilisateur a une station
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user->station_id) {
                abort(403, 'Vous devez être assigné à une station pour accéder aux stocks.');
            }
            return $next($request);
        });
    }
    
    /**
     * Afficher le tableau de bord des stocks
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $stationId = $user->station_id;
        
        // Vérifier que la station existe
        $station = Station::findOrFail($stationId);
        
        // Récupérer les cuves de CETTE station uniquement
        $tanks = Tank::where('station_id', $stationId)->get();
        
        // Récupérer tous les jaugeages récents de CETTE station
        $recentLevels = TankLevel::where('station_id', $stationId)
            ->with('tank')
            ->orderBy('measurement_date', 'desc')
            ->get();
        
        // Organiser par type de carburant
        $stocks = [];
        $fuelTypes = ['super', 'gasoil', 'gazole', 'essence pirogue', 'diesel'];
        
        foreach($fuelTypes as $fuelType) {
            // Filtrer les jaugeages par type de carburant
            $levelsOfType = $recentLevels->filter(function($level) use ($fuelType) {
                return $level->tank && stripos($level->tank->fuel_type, $fuelType) !== false;
            });
            
            $latestLevel = $levelsOfType->first();
            
            if ($latestLevel) {
                // Récupérer le nom de l'utilisateur SANS utiliser la relation measuredBy
                $measuredByName = 'N/A';
                if ($latestLevel->measured_by) {
                    $user = User::find($latestLevel->measured_by);
                    $measuredByName = $user ? $user->name : 'N/A';
                }
                
                $stocks[$fuelType] = [
                    'theoretical_stock' => $latestLevel->theoretical_stock ?? 0,
                    'physical_stock' => $latestLevel->volume_liters,
                    'difference_liters' => $latestLevel->difference ?? 0,
                    'difference_per_mille' => $latestLevel->difference_percentage ?? 0,
                    'last_measurement_date' => $latestLevel->measurement_date,
                    'is_acceptable' => $latestLevel->is_acceptable ?? true,
                    'tolerance_threshold' => $latestLevel->tolerance_threshold ?? 5,
                    'measured_by_name' => $measuredByName,
                    'tank_number' => $latestLevel->tank_number ?? 'N/A',
                    'fuel_type_display' => strtoupper($fuelType),
                ];
            } else {
                $stocks[$fuelType] = $this->createEmptyStockData($fuelType);
            }
        }
        
        // Récupérer les 5 derniers jaugeages pour l'historique
        $latestTankLevels = TankLevel::where('station_id', $stationId)
            ->with('tank')
            ->orderBy('measurement_date', 'desc')
            ->limit(5)
            ->get();
        
        // Pour chaque jaugeage, ajouter manuellement le nom de l'utilisateur
        foreach($latestTankLevels as $level) {
            if ($level->measured_by) {
                $user = User::find($level->measured_by);
                $level->measured_by_name = $user ? $user->name : 'N/A';
            } else {
                $level->measured_by_name = 'N/A';
            }
        }
        
        // Récupérer les mouvements récents de CETTE station
        $latestMovements = StockMovement::where('station_id', $stationId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Générer les alertes
        $alerts = $this->generateAlertsFromGaugings($stocks);
        
        return view('manager.stocks.dashboard', compact(
            'stocks', 
            'tanks', 
            'latestTankLevels',
            'latestMovements',
            'alerts',
            'station'
        ));
    }
    
    private function createEmptyStockData($fuelType)
    {
        return [
            'theoretical_stock' => 0,
            'physical_stock' => null,
            'difference_liters' => 0,
            'difference_per_mille' => 0,
            'last_measurement_date' => null,
            'is_acceptable' => true,
            'tolerance_threshold' => 5,
            'tank_number' => 'N/A',
            'measured_by_name' => 'N/A',
            'fuel_type_display' => $this->getFuelTypeDisplayName($fuelType),
        ];
    }
    
    private function getFuelTypeDisplayName($fuelType)
    {
        $displayNames = [
            'super' => 'SUPER',
            'gazole' => 'GAZOLE',
            'gasoil' => 'GAZOLE',
            'essence pirogue' => 'ESSENCE PIROGUE',
            'essence' => 'ESSENCE',
        ];
        
        return $displayNames[strtolower($fuelType)] ?? strtoupper($fuelType);
    }
    
    /**
     * Afficher le formulaire d'enregistrement de réception
     */
    public function create()
    {
        try {
            $user = Auth::user();
            $stationId = $user->station_id;
            
            // Récupérer les cuves de CETTE station uniquement
            $tanks = Tank::where('station_id', $stationId)
                ->orderBy('number')
                ->get()
                ->mapWithKeys(function($tank) {
                    return [
                        $tank->id => "Cuve {$tank->number} - " . strtoupper($tank->fuel_type) . 
                                    " (Capacité: " . number_format($tank->capacity, 0, ',', ' ') . 
                                    "L, Disponible: " . number_format($tank->capacity - $tank->current_volume, 0, ',', ' ') . "L)"
                    ];
                });
            
            // Types de carburant disponibles dans cette station
            $fuelTypes = Tank::where('station_id', $stationId)
                ->pluck('fuel_type')
                ->unique()
                ->mapWithKeys(function($fuelType) {
                    $displayNames = [
                        'super' => 'SUPER (Essence)',
                        'gasoil' => 'GAZOLE (Diesel)',
                        'gazole' => 'GAZOLE',
                        'essence pirogue' => 'ESSENCE PIROGUE',
                        'essence' => 'ESSENCE',
                    ];
                    return [$fuelType => $displayNames[$fuelType] ?? strtoupper($fuelType)];
                });
            
            return view('manager.stocks.receptions.create', [
                'fuelTypes' => $fuelTypes,
                'tanks' => $tanks
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur création formulaire réception', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('manager.stocks.receptions.create', [
                'fuelTypes' => [],
                'tanks' => [],
                'error' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Enregistrer un jaugeage de cuve
     */
    public function storeTankLevel(Request $request)
    {
        $validated = $request->validate([
            'measurement_date' => 'required|date',
            'tank_number' => 'required|string',
            'level_cm' => 'required|numeric|min:0',
            'temperature_c' => 'nullable|numeric|min:-50|max:100',
            'theoretical_stock' => 'required|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        
        try {
            $user = Auth::user();
            $stationId = $user->station_id;
            
            // Vérifier que la cuve appartient à la station
            $tank = Tank::where('number', $validated['tank_number'])
                ->where('station_id', $stationId)
                ->first();
            
            if (!$tank) {
                throw new \Exception("La cuve n'existe pas ou n'appartient pas à votre station.");
            }
            
            // Calculer le volume approximativement
            $measuredVolume = $this->calculateVolumeFromLevel($validated['level_cm']);
            
            // Appliquer la correction de température
            if (!empty($validated['temperature_c']) && $validated['temperature_c'] != 20) {
                $coefficient = $this->getExpansionCoefficient($tank->fuel_type);
                $correctionFactor = 1 + (($validated['temperature_c'] - 20) * $coefficient);
                $measuredVolume = $measuredVolume * $correctionFactor;
            }
            
            // Calculer les écarts
            $difference = $measuredVolume - $validated['theoretical_stock'];
            $differencePercentage = $validated['theoretical_stock'] > 0 
                ? ($difference / $validated['theoretical_stock']) * 1000
                : 0;
            
            // Tolérance par défaut
            $toleranceThreshold = 3; // 3‰
            $isAcceptable = abs($differencePercentage) <= $toleranceThreshold;
            
            // Créer l'enregistrement
            $tankLevel = TankLevel::create([
                'measurement_date' => $validated['measurement_date'],
                'tank_number' => $validated['tank_number'],
                'fuel_type' => $tank->fuel_type,
                'level_cm' => $validated['level_cm'],
                'temperature_c' => $validated['temperature_c'] ?? 20,
                'volume_liters' => $measuredVolume,
                'theoretical_stock' => $validated['theoretical_stock'],
                'physical_stock' => $measuredVolume,
                'difference' => $difference,
                'difference_percentage' => $differencePercentage,
                'observations' => $validated['observations'],
                'measured_by' => $user->id,
                'station_id' => $stationId,
                'is_acceptable' => $isAcceptable,
            ]);
            
            DB::commit();
            
            $message = 'Jaugeage enregistré!';
            $alertType = 'success';
            
            if (!$isAcceptable) {
                $message .= ' Écart détecté, surveillance requise.';
                $alertType = 'warning';
            }
            
            return redirect()->route('manager.stocks.dashboard')
                ->with($alertType, $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur jaugeage', [
                'error' => $e->getMessage(),
                'station_id' => Auth::user()->station_id ?? null
            ]);
            return back()->withInput()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
    
    private function generateAlertsFromGaugings($stocks)
    {
        $alerts = [];
        
        foreach($stocks as $type => $data) {
            if ($data['physical_stock'] !== null && $data['difference_per_mille'] != 0) {
                $ecartAbsolu = abs($data['difference_per_mille']);
                
                // Seuils en pour mille (‰)
                if ($ecartAbsolu > 10) {
                    $alerts[] = [
                        'severity' => 'danger',
                        'icon' => 'fas fa-times-circle',
                        'message' => sprintf(
                            'ÉCART MAJEUR sur %s: %.1f‰ (Théo: %sL | Phys: %sL)',
                            strtoupper($type),
                            $data['difference_per_mille'],
                            number_format($data['theoretical_stock'], 0, ',', ' '),
                            number_format($data['physical_stock'], 0, ',', ' ')
                        )
                    ];
                } elseif ($ecartAbsolu > 5) {
                    $alerts[] = [
                        'severity' => 'warning',
                        'icon' => 'fas fa-exclamation-triangle',
                        'message' => sprintf(
                            'Écart significatif sur %s: %.1f‰',
                            strtoupper($type),
                            $data['difference_per_mille']
                        )
                    ];
                }
            }
        }
        
        return $alerts;
    }
    
    private function calculateVolumeFromLevel($levelCm)
    {
        // Formule simple : 1 cm = 200 litres (à adapter selon vos besoins)
        return $levelCm * 200;
    }
    
    private function getExpansionCoefficient($fuelType)
    {
        $coefficients = [
            'super' => 0.0011,
            'essence' => 0.0011,
            'gasoil' => 0.0008,
            'gazole' => 0.0008,
            'diesel' => 0.0008,
        ];
        
        return $coefficients[strtolower($fuelType)] ?? 0.0010;
    }
    
    /**
     * Enregistrer une réception
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $stationId = $user->station_id;
        
        $validated = $request->validate([
            'tank_id' => 'required|exists:tanks,id',
            'quantity_liters' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
            'delivery_date' => 'required|date',
            'supplier' => 'required|string|max:255',
            'invoice_number' => 'required|string|max:100',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Vérifier que la cuve appartient à la station de l'utilisateur
            $tank = Tank::where('id', $validated['tank_id'])
                ->where('station_id', $stationId)
                ->firstOrFail();
            
            // Vérifier capacité
            $availableCapacity = $tank->capacity - $tank->current_volume;
            if ($request->quantity_liters > $availableCapacity) {
                return back()->with('error', 'Capacité insuffisante: ' . number_format($availableCapacity, 0) . ' L disponible');
            }
            
            // Calculs
            $totalAmount = $request->quantity_liters * $request->unit_price;
            $stockBefore = $tank->current_volume;
            $stockAfter = $stockBefore + $request->quantity_liters;
            
            // Données pour stock_movements
            $movementData = [
                'station_id' => $stationId,
                'movement_date' => $request->delivery_date,
                'fuel_type' => $tank->fuel_type,
                'movement_type' => 'reception',
                'quantity' => $request->quantity_liters,
                'unit_price' => $request->unit_price,
                'total_amount' => $totalAmount,
                'supplier_name' => $request->supplier,
                'invoice_number' => $request->invoice_number,
                'tank_number' => $tank->number,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'recorded_by' => $user->id,
                'verified_by' => $user->id,
                'verified_at' => now(),
                'auto_generated' => false,
            ];
            
            // Ajouter les champs optionnels seulement s'ils sont remplis
            if ($request->filled('notes')) {
                $movementData['notes'] = $request->notes;
            }
            
            if ($request->filled('driver_name')) {
                $movementData['driver_name'] = $request->driver_name;
            }
            
            // Créer le mouvement
            StockMovement::create($movementData);
            
            // Mettre à jour la cuve
            $tank->update([
                'current_volume' => $stockAfter,
                'current_level_cm' => ($stockAfter / $tank->capacity) * 250,
            ]);
            
            DB::commit();
            
            return redirect()->route('manager.stocks.dashboard')
                ->with('success', 'Réception de ' . number_format($request->quantity_liters, 0) . 
                       ' L enregistrée pour la cuve ' . $tank->number);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur réception stock', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'station_id' => $stationId,
                'data' => $request->all()
            ]);
            
            return back()->with('error', 'Erreur: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Afficher l'historique complet des mouvements
     */
    public function movementHistory(Request $request)
    {
        $user = Auth::user();
        $stationId = $user->station_id;
        
        $fuelType = $request->input('fuel_type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        $query = StockMovement::with(['recorder', 'shift'])
            ->where('station_id', $stationId);
            
        if ($fuelType) {
            $query->where('fuel_type', $fuelType);
        }
        
        if ($startDate) {
            $query->where('movement_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('movement_date', '<=', $endDate);
        }
        
        $movements = $query->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        // Calculer les totaux
        $totals = [
            'receptions' => $movements->where('movement_type', 'reception')->sum('quantity'),
            'sales' => abs($movements->where('movement_type', 'vente')->sum('quantity')),
            'adjustments' => $movements->where('movement_type', 'ajustement')->sum('quantity'),
        ];
        
        $fuelTypes = $this->getFuelTypesForStation($stationId);
        
        return view('manager.stocks.history', compact(
            'movements', 'fuelTypes', 'fuelType', 
            'startDate', 'endDate', 'totals'
        ));
    }
    
    /**
     * API pour calculer le volume
     */
    public function calculateVolumeApi(Request $request)
    {
        $request->validate([
            'tank_number' => 'required|string',
            'height_cm' => 'required|numeric|min:0',
            'temperature_c' => 'nullable|numeric'
        ]);
        
        try {
            $user = Auth::user();
            $stationId = $user->station_id;
            
            $tank = Tank::where('number', $request->tank_number)
                ->where('station_id', $stationId)
                ->firstOrFail();
            
            $volume = $tank->calculateVolumeFromHeight($request->height_cm);
            
            // Correction température
            if ($request->has('temperature_c') && $request->temperature_c != 20) {
                $coefficient = $this->getExpansionCoefficient($tank->fuel_type);
                $correctionFactor = 1 + (($request->temperature_c - 20) * $coefficient);
                $volume = $volume * $correctionFactor;
            }
            
            return response()->json([
                'success' => true,
                'volume' => round($volume, 2),
                'tank' => [
                    'number' => $tank->number,
                    'fuel_type' => $tank->fuel_type,
                    'capacity' => $tank->capacity,
                    'tolerance_threshold' => ($tank->tolerance_threshold ?? 0.003) * 1000,
                    'product_category' => $tank->product_category
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Afficher le bilan détaillé
     */
    public function stockBalance()
    {
        try {
            $user = Auth::user();
            $stationId = $user->station_id;
            
            $balance = $this->stockService->getStockBalance();
            
            // Récupérer les derniers jaugeages pour chaque type
            $latestLevels = [];
            
            $fuelTypes = $this->getFuelTypesForStation($stationId);
            foreach ($fuelTypes as $key => $name) {
                $latestLevel = TankLevel::where('fuel_type', $key)
                    ->where('station_id', $stationId)
                    ->orderBy('measurement_date', 'desc')
                    ->first();
                
                if ($latestLevel) {
                    $latestLevels[$key] = $latestLevel;
                }
            }
            
            return view('manager.stocks.balance', compact('balance', 'latestLevels'));
            
        } catch (\Exception $e) {
            Log::error('Erreur dans stockBalance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'station_id' => Auth::user()->station_id ?? null
            ]);
            
            return redirect()->back()
                ->with('error', 'Erreur lors du chargement du bilan: ' . $e->getMessage());
        }
    }
    
    /**
     * Formulaire d'ajustement manuel
     */
    public function createAdjustment()
    {
        $user = Auth::user();
        $stationId = $user->station_id;
        
        $fuelTypes = $this->getFuelTypesForStation($stationId);
        $currentStocks = [];
        
        foreach ($fuelTypes as $key => $name) {
            $currentStocks[$key] = StockMovement::currentStock($key, $stationId);
        }
        
        // Récupérer les cuves disponibles de CETTE station
        $tanks = Tank::where('station_id', $stationId)->get();
        
        return view('manager.stocks.adjustments.create', compact('fuelTypes', 'currentStocks', 'tanks'));
    }
    
    /**
     * Enregistrer un ajustement
     */
    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'fuel_type' => 'required|string',
            'tank_number' => 'required|string',
            'quantity' => 'required|numeric',
            'reason' => 'required|string|max:500',
            'adjustment_date' => 'required|date',
        ]);

        try {
            $user = Auth::user();
            $stationId = $user->station_id;
            
            // Vérifier que la cuve appartient à la station
            $tank = Tank::where('number', $validated['tank_number'])
                ->where('station_id', $stationId)
                ->firstOrFail();
            
            // Calculer le stock avant/après
            $stockBefore = StockMovement::currentStock($validated['fuel_type'], $stationId);
            $stockAfter = $stockBefore + $validated['quantity'];
            
            // Créer le mouvement d'ajustement
            $movement = StockMovement::create([
                'movement_date' => $validated['adjustment_date'],
                'fuel_type' => $validated['fuel_type'],
                'movement_type' => 'ajustement',
                'quantity' => $validated['quantity'],
                'tank_number' => $validated['tank_number'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $validated['reason'],
                'recorded_by' => $user->id,
                'station_id' => $stationId,
            ]);
            
            // Mettre à jour la cuve si l'ajustement affecte son volume
            if ($tank->fuel_type == $validated['fuel_type']) {
                $newVolume = $tank->current_volume + $validated['quantity'];
                $tank->update(['current_volume' => $newVolume]);
            }
            
            $sign = $validated['quantity'] >= 0 ? '+' : '';
            return redirect()->route('manager.stocks.dashboard')
                ->with('success', "Ajustement de {$sign}{$validated['quantity']} L de {$validated['fuel_type']} enregistré!")
                ->with('info', "Stock: {$stockBefore} → {$stockAfter} L");
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }
    
    /**
     * Rapport de réconciliation
     */
    public function reconciliationReport(Request $request)
    {
        $user = Auth::user();
        $stationId = $user->station_id;
        
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $fuelType = $request->input('fuel_type');
        
        $query = StockMovement::with(['recorder', 'verifier'])
            ->where('station_id', $stationId)
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->orderBy('movement_date');
            
        if ($fuelType) {
            $query->where('fuel_type', $fuelType);
        }
        
        $movements = $query->get();
        
        // Récupérer les jaugeages sur la période de CETTE station
        $tankLevels = TankLevel::with(['measurer', 'tank'])
            ->where('station_id', $stationId)
            ->whereBetween('measurement_date', [$startDate, $endDate])
            ->get();
        
        // Calculer les totaux
        $totals = [
            'receptions' => $movements->where('movement_type', 'reception')->sum('quantity'),
            'sales' => abs($movements->where('movement_type', 'vente')->sum('quantity')),
            'adjustments' => $movements->where('movement_type', 'ajustement')->sum('quantity'),
            'inventory_differences' => $tankLevels->sum('difference'),
        ];
        
        $fuelTypes = $this->getFuelTypesForStation($stationId);
        
        return view('manager.stocks.reports.reconciliation', compact(
            'movements', 'totals', 'fuelTypes', 'startDate', 'endDate', 'fuelType', 'tankLevels'
        ));
    }
    
    /**
     * Rapport d'inventaire
     */
    public function inventoryReport(Request $request)
    {
        $user = Auth::user();
        $stationId = $user->station_id;
        
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        
        $tankLevels = TankLevel::with(['measurer', 'verifier', 'tank'])
            ->where('station_id', $stationId)
            ->whereBetween('measurement_date', [$startDate, $endDate])
            ->orderBy('measurement_date', 'desc')
            ->get();
            
        // Statistiques
        $stats = [
            'total_measurements' => $tankLevels->count(),
            'average_difference' => $tankLevels->avg('difference_percentage'),
            'max_difference' => $tankLevels->max('difference_percentage'),
            'min_difference' => $tankLevels->min('difference_percentage'),
            'within_tolerance' => $tankLevels->where('is_acceptable', true)->count(),
            'outside_tolerance' => $tankLevels->where('is_acceptable', false)->count(),
        ];
        
        // Group par cuve
        $tankStats = [];
        foreach ($tankLevels->groupBy('tank_number') as $tankNumber => $levels) {
            $tankStats[$tankNumber] = [
                'count' => $levels->count(),
                'avg_difference' => $levels->avg('difference_percentage'),
                'within_tolerance' => $levels->where('is_acceptable', true)->count(),
            ];
        }
        
        return view('manager.stocks.reports.inventory', compact('tankLevels', 'stats', 'tankStats', 'startDate', 'endDate'));
    }
    
    /**
     * Gestion des tables de calibration
     */
    public function calibrationManagement()
    {
        $user = Auth::user();
        $stationId = $user->station_id;
        
        $tanks = Tank::where('station_id', $stationId)->get();
        
        return view('manager.stocks.calibration.management', compact('tanks'));
    }
    
    /**
     * Importer une table de calibration
     */
    public function importCalibration(Request $request)
    {
        $validated = $request->validate([
            'tank_id' => 'required|exists:tanks,id',
            'capacity' => 'required|integer|min:1000',
            'calibration_file' => 'required|file|mimes:csv,txt',
        ]);
        
        try {
            $user = Auth::user();
            $stationId = $user->station_id;
            
            // Vérifier que la cuve appartient à la station
            $tank = Tank::where('id', $validated['tank_id'])
                ->where('station_id', $stationId)
                ->firstOrFail();
            
            // Lire le fichier CSV
            $file = $request->file('calibration_file');
            $table = [];
            
            if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (count($data) >= 2) {
                        $volume = (int) trim($data[0]);
                        $height = (float) trim($data[1]);
                        $table[$volume] = $height;
                    }
                }
                fclose($handle);
            }
            
            if (empty($table)) {
                throw new \Exception('Le fichier CSV est vide ou mal formaté');
            }
            
            // Mettre à jour la cuve
            $tank->update([
                'calibration_table' => $table,
                'calibration_date' => now(),
                'capacity' => $validated['capacity'],
            ]);
            
            return redirect()->back()
                ->with('success', 'Table de calibration importée avec succès!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'import: ' . $e->getMessage());
        }
    }
    
    /**
     * API pour récupérer les cuves par type de carburant
     */
    public function getTanksByFuelType(Request $request)
    {
        try {
            $user = Auth::user();
            $stationId = $user->station_id;
            $fuelType = $request->input('fuel_type');
            
            if (!$fuelType) {
                return response()->json(['error' => 'Type de carburant requis'], 400);
            }
            
            // Récupérer les cuves de CETTE station pour ce type de carburant
            $tanks = Tank::where('station_id', $stationId)
                ->where('fuel_type', $fuelType)
                ->get()
                ->map(function($tank) {
                    $availableCapacity = $tank->capacity - $tank->current_volume;
                    $fillPercentage = $tank->capacity > 0 ? ($tank->current_volume / $tank->capacity) * 100 : 0;
                    
                    return [
                        'id' => $tank->id,
                        'number' => $tank->number,
                        'description' => "Cuve {$tank->number} - " . strtoupper($tank->fuel_type),
                        'fuel_type' => $tank->fuel_type,
                        'available_capacity' => $availableCapacity,
                        'current_volume' => $tank->current_volume,
                        'fill_percentage' => round($fillPercentage, 1),
                        'capacity' => $tank->capacity,
                        'text' => "Cuve {$tank->number} - " . strtoupper($tank->fuel_type) . 
                                 " - Capacité: " . number_format($tank->capacity, 0, ',', ' ') . 
                                 "L, Disponible: " . number_format($availableCapacity, 0, ',', ' ') . "L"
                    ];
                });
            
            return response()->json($tanks);
            
        } catch (\Exception $e) {
            \Log::error('Erreur API getTanksByFuelType', [
                'error' => $e->getMessage(),
                'station_id' => Auth::user()->station_id ?? null,
                'fuel_type' => $request->input('fuel_type')
            ]);
            
            return response()->json([
                'error' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Formulaire de création de jaugeage
     */
    public function createTankLevel()
    {
        try {
            $user = Auth::user();
            $stationId = $user->station_id;
            
            // Récupérer les cuves de CETTE station
            $tanks = Tank::where('station_id', $stationId)
                ->get()
                ->map(function($tank) {
                    return [
                        'value' => $tank->number,
                        'description' => "Cuve {$tank->number} - " . strtoupper($tank->fuel_type),
                        'details' => [
                            'fuel_type' => $tank->fuel_type,
                            'capacity' => $tank->capacity,
                            'current_level_cm' => $tank->current_level_cm,
                            'current_volume' => $tank->current_volume,
                            'theoretical_stock' => $tank->current_volume
                        ]
                    ];
                })
                ->toArray();
            
            return view('manager.stocks.tank-levels.create', compact('tanks'));
            
        } catch (\Exception $e) {
            Log::error('Erreur création jaugeage', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'station_id' => auth()->user()->station_id ?? null
            ]);
            
            return view('manager.stocks.tank-levels.create', [
                'tanks' => [],
                'error' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Récupérer les types de carburant disponibles pour une station
     */
    private function getFuelTypesForStation($stationId)
    {
        // Récupérer les types de carburant présents dans les cuves de la station
        $fuelTypes = Tank::where('station_id', $stationId)
            ->pluck('fuel_type')
            ->unique()
            ->mapWithKeys(function($fuelType) {
                $displayNames = [
                    'super' => 'SUPER (Essence)',
                    'gasoil' => 'GAZOLE (Diesel)',
                    'gazole' => 'GAZOLE',
                    'essence pirogue' => 'ESSENCE PIROGUE',
                    'essence' => 'ESSENCE',
                ];
                return [$fuelType => $displayNames[$fuelType] ?? strtoupper($fuelType)];
            })
            ->toArray();
        
        return $fuelTypes;
    }
    
    /**
     * API pour récupérer les stocks courants
     */
    public function apiCurrentStocks(Request $request)
    {
        try {
            $user = Auth::user();
            $stationId = $user->station_id;
            
            // Récupérer les cuves de CETTE station
            $tanks = Tank::where('station_id', $stationId)->get();
            
            // Calculer les stocks par type
            $data = [
                'super' => $tanks->where('fuel_type', 'super')->sum('current_volume'),
                'gasoil' => $tanks->whereIn('fuel_type', ['gasoil', 'gazole', 'diesel'])->sum('current_volume'),
                'total' => $tanks->sum('current_volume'),
                'tanks_count' => $tanks->count(),
                'station_name' => optional($user->station)->name ?? 'Station ' . $stationId,
            ];
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API pour récupérer l'historique des stocks
     */
    public function apiStockHistory($fuelType = null)
    {
        $user = Auth::user();
        $stationId = $user->station_id;
        $startDate = now()->subDays(30)->format('Y-m-d');
        
        $query = StockMovement::where('station_id', $stationId)
            ->where('movement_date', '>=', $startDate);
            
        if ($fuelType && $fuelType != 'all') {
            $query->where('fuel_type', $fuelType);
        }
        
        $history = $query->orderBy('movement_date')
            ->get()
            ->groupBy(function($item) {
                return $item->movement_date->format('Y-m-d');
            })
            ->map(function($items) {
                return $items->sum('quantity');
            });
        
        return response()->json($history);
    }
    
    /**
     * Obtenir les alertes de stock
     */
    private function getStockAlerts($stationId)
    {
        $alerts = [];
        
        // Vérifier les stocks bas
        $fuelTypes = $this->getFuelTypesForStation($stationId);
        foreach ($fuelTypes as $key => $name) {
            $currentStock = StockMovement::currentStock($key, $stationId);
            
            // Seuils d'alerte (10% de la capacité typique)
            $thresholds = [
                'super' => 3000,
                'gasoil' => 5000,
                'gazole' => 5000,
                'diesel' => 5000,
                'essence' => 3000,
            ];
            
            $threshold = $thresholds[$key] ?? 3000;
            
            if ($currentStock < $threshold) {
                $alerts[] = [
                    'type' => 'low_stock',
                    'fuel_type' => $name,
                    'current_stock' => $currentStock,
                    'message' => "Stock bas de {$name}: " . number_format($currentStock, 0, ',', ' ') . " L (seuil: {$threshold} L)",
                    'severity' => 'warning'
                ];
            }
            
            // Vérifier les écarts de jaugeage récents
            $latestTankLevel = TankLevel::where('fuel_type', $key)
                ->where('station_id', $stationId)
                ->orderBy('measurement_date', 'desc')
                ->first();
                
            if ($latestTankLevel && !$latestTankLevel->is_acceptable) {
                $threshold = $latestTankLevel->tolerance_threshold ?? 5;
                $alerts[] = [
                    'type' => 'major_discrepancy',
                    'fuel_type' => $name,
                    'difference' => $latestTankLevel->difference_percentage,
                    'message' => sprintf(
                        'Écart de %s‰ sur %s (tolérance: %s‰)',
                        number_format($latestTankLevel->difference_percentage, 1),
                        $name,
                        number_format($threshold, 1)
                    ),
                    'severity' => abs($latestTankLevel->difference_percentage) > ($threshold * 1.5) ? 'danger' : 'warning'
                ];
            }
        }
        
        // Vérifier les cuves presque pleines
        $tanks = Tank::where('station_id', $stationId)->get();
        
        foreach ($tanks as $tank) {
            $percentage = $tank->current_volume > 0 ? ($tank->current_volume / $tank->capacity) * 100 : 0;
            
            if ($percentage > 90) {
                $alerts[] = [
                    'type' => 'tank_almost_full',
                    'tank' => $tank->number,
                    'percentage' => $percentage,
                    'message' => "Cuve {$tank->number} à " . round($percentage, 1) . "% de sa capacité",
                    'severity' => 'info'
                ];
            }
        }
        
        return $alerts;
    }
    
    /**
     * Générer les alertes
     */
    private function generateAlerts($stationId, $tanks, $stocks)
    {
        $alerts = [];
        
        // 1. Alertes d'écart de jaugeage
        foreach ($stocks as $fuelType => $data) {
            if ($data['physical_stock'] !== null && abs($data['difference_percentage']) > 2.0) {
                $alerts[] = [
                    'type' => 'major_discrepancy',
                    'severity' => 'danger',
                    'message' => "Écart majeur détecté pour le " . strtoupper($fuelType) . 
                                ": " . round($data['difference_percentage'], 2) . "%",
                    'fuel_type' => $fuelType,
                ];
            } elseif ($data['physical_stock'] !== null && abs($data['difference_percentage']) > 1.0) {
                $alerts[] = [
                    'type' => 'discrepancy',
                    'severity' => 'warning',
                    'message' => "Écart détecté pour le " . strtoupper($fuelType) . 
                                ": " . round($data['difference_percentage'], 2) . "%",
                    'fuel_type' => $fuelType,
                ];
            }
        }
        
        // 2. Alertes de stock bas dans les cuves
        foreach ($tanks as $tank) {
            $fillPercentage = $tank->capacity > 0 ? (($tank->current_volume ?? 0) / $tank->capacity) * 100 : 0;
            
            if ($fillPercentage < 10) {
                $alerts[] = [
                    'type' => 'low_stock',
                    'severity' => 'danger',
                    'message' => "Stock bas sur la cuve " . $tank->number . 
                                " (" . strtoupper($tank->fuel_type) . "): " . 
                                round($fillPercentage, 1) . "% de remplissage",
                ];
            } elseif ($fillPercentage < 20) {
                $alerts[] = [
                    'type' => 'low_stock',
                    'severity' => 'warning',
                    'message' => "Stock faible sur la cuve " . $tank->number . 
                                " (" . strtoupper($tank->fuel_type) . "): " . 
                                round($fillPercentage, 1) . "% de remplissage",
                ];
            }
            
            // Cuves presque pleines
            if ($fillPercentage > 90) {
                $alerts[] = [
                    'type' => 'tank_almost_full',
                    'severity' => 'info',
                    'message' => "Cuve " . $tank->number . " à " . round($fillPercentage, 1) . "% de sa capacité",
                ];
            }
        }
        
        // 3. Alertes de jaugeage ancien (> 7 jours)
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        foreach ($tanks as $tank) {
            $lastLevel = TankLevel::where('station_id', $stationId)
                ->where('tank_number', $tank->number)
                ->orderBy('measurement_date', 'desc')
                ->first();
            
            if (!$lastLevel) {
                $alerts[] = [
                    'type' => 'no_recent_measurement',
                    'severity' => 'warning',
                    'message' => "Aucun jaugeage pour la cuve " . $tank->number . 
                                " (" . strtoupper($tank->fuel_type) . ")",
                ];
            } elseif ($lastLevel->measurement_date < $sevenDaysAgo) {
                $daysAgo = $lastLevel->measurement_date->diffInDays(Carbon::now());
                $alerts[] = [
                    'type' => 'old_measurement',
                    'severity' => 'warning',
                    'message' => "Dernier jaugeage ancien pour la cuve " . $tank->number . 
                                " (" . strtoupper($tank->fuel_type) . "): " . 
                                $daysAgo . " jours",
                ];
            }
        }
        
        return $alerts;
    }
}