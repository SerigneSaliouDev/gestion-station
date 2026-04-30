<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use App\Models\ShiftSaisie;
use App\Models\Tank;
use App\Models\StockMovement; 
use App\Models\ShiftPompeDetail;
use App\Services\StockOperationService;
use App\Models\Depense;
use App\Models\Station;
use App\Models\User;

class ManagerController extends Controller
{
     protected $stockService;
     
       public function __construct(StockOperationService $stockService)
        {
            $this->middleware('auth');
            $this->middleware('checkStation')->except(['showIndexForm']);
            $this->middleware('role:manager')->except(['login', 'logout']);
            $this->middleware('stock.guard:shift')->only(['storeIndex']);
            
            $this->stockService = $stockService;
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
            ['name' => 'Pompe 3', 'fuel_type' => 'Essence Pirogue', 'unit_price' => 900, 'pump_id' => 3],
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

        DB::beginTransaction();
        
        try {
            $totalLiters = 0;
            $totalSales = 0;
            
            // Regrouper les ventes par type de carburant
            $salesByFuelType = [];

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
                
                $fuelType = $this->normalizeFuelType($pump['fuel_type']);
                if (!isset($salesByFuelType[$fuelType])) {
                    $salesByFuelType[$fuelType] = [
                        'quantity' => 0,
                        'total_amount' => 0,
                        'pumps' => []
                    ];
                }
                $salesByFuelType[$fuelType]['quantity'] += $literage;
                $salesByFuelType[$fuelType]['total_amount'] += $literage * $unitPrice;
                $salesByFuelType[$fuelType]['pumps'][] = $pump['name'];
            }

            $cashDeposit = floatval($request->cash_deposit_amount);
            $ecartInitial = $cashDeposit - $totalSales;

            // Créer le shift
            $shift = ShiftSaisie::create([
                'date_shift' => $request->shift_date,
                'shift' => $request->shift_time,
                'responsable' => $request->responsible_name,
                'total_litres' => $totalLiters,
                'total_ventes' => $totalSales,
                'versement' => $cashDeposit,
                'ecart' => $ecartInitial,
                'user_id' => auth()->id(),
                'station_id' => $user->station_id,
                'statut' => 'en_attente',
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

            
            
            $stockUpdates = [];
            
            foreach ($salesByFuelType as $fuelType => $saleData) {
                if ($saleData['quantity'] > 0) {
                    try {
                        // 1. Trouver la cuve appropriée
                        $tank = Tank::where('station_id', $user->station_id)
                            ->where(function($query) use ($fuelType) {
                                $query->where('fuel_type', 'LIKE', $fuelType)
                                      ->orWhere('fuel_type', 'LIKE', '%' . $fuelType . '%');
                                      
                                if (in_array($fuelType, ['gasoil', 'gazole', 'diesel'])) {
                                    $query->orWhere('fuel_type', 'LIKE', '%gasoil%')
                                          ->orWhere('fuel_type', 'LIKE', '%gazole%')
                                          ->orWhere('fuel_type', 'LIKE', '%diesel%');
                                }
                            })
                            ->orderBy('current_volume', 'desc')
                            ->first();

                        if (!$tank) {
                            throw new \Exception(sprintf(
                                "Aucune cuve trouvée pour: %s",
                                ucfirst($fuelType)
                            ));
                        }

                        // 2. Vérifier le stock
                        if ($tank->current_volume < $saleData['quantity']) {
                            throw new \Exception(sprintf(
                                "Stock insuffisant dans la cuve %s (%s). Disponible: %s L, Vendu: %s L",
                                $tank->number,
                                $tank->fuel_type,
                                number_format($tank->current_volume, 2),
                                number_format($saleData['quantity'], 2)
                            ));
                        }

                        $ancienStock = $tank->current_volume;
                        $nouveauStock = $ancienStock - $saleData['quantity'];
                        
                        // 3. CRÉER UN SEUL STOCK MOVEMENT via le service
                        $avgPrice = $saleData['total_amount'] / $saleData['quantity'];
                        
                        $movementResult = $this->stockService->registerSale([
                            'station_id' => $user->station_id,
                            'fuel_type' => $fuelType,
                            'quantity' => $saleData['quantity'],
                            'unit_price' => $avgPrice,
                            'customer_name' => $request->responsible_name . ' (Shift)',
                            'payment_method' => 'cash',
                            'tank_number' => $tank->number,
                            'recorded_by' => $user->id,
                            'movement_date' => $request->shift_date,
                            'shift_saisie_id' => $shift->id,
                            'auto_generated' => true
                        ]);
                        
                        // 4. Mettre à jour la cuve DIRECTEMENT (pas via un nouveau mouvement)
                        $tank->current_volume = $nouveauStock;
                        $tank->current_level_cm = $tank->capacity > 0 
                            ? ($nouveauStock / $tank->capacity) * 250 
                            : 0;
                        $tank->last_measurement_date = now();
                        $tank->save();
                        
                        \Log::info('Stock mis à jour pour shift', [
                            'shift_id' => $shift->id,
                            'fuel_type' => $fuelType,
                            'tank_id' => $tank->id,
                            'movement_id' => $movementResult['movement']->id,
                            'quantite' => $saleData['quantity'],
                            'avant' => $ancienStock,
                            'apres' => $nouveauStock
                        ]);
                        
                        $stockUpdates[$fuelType] = [
                            'tank_number' => $tank->number,
                            'tank_fuel_type' => $tank->fuel_type,
                            'quantity' => $saleData['quantity'],
                            'before' => $ancienStock,
                            'after' => $nouveauStock,
                            'movement_id' => $movementResult['movement']->id
                        ];
                        
                    } catch (\Exception $e) {
                        \Log::error('Erreur traitement stock shift', [
                            'fuel_type' => $fuelType,
                            'shift_id' => $shift->id,
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }
                }
            }

            // Gestion des dépenses
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

            $ecartFinal = $cashDeposit - ($totalSales - $totalDepenses);
            
            $shift->update([
                'total_depenses' => $totalDepenses,
                'ecart_final' => $ecartFinal
            ]);

            // ========== VÉRIFICATION ANTI-DOUBLON ==========
            $movementsCount = StockMovement::where('shift_saisie_id', $shift->id)->count();
            $expectedCount = count(array_filter($salesByFuelType, fn($s) => $s['quantity'] > 0));

            \Log::info('VÉRIFICATION STOCK MOVEMENTS', [
                'shift_id' => $shift->id,
                'mouvements_créés' => $movementsCount,
                'mouvements_attendus' => $expectedCount,
                'ok' => $movementsCount === $expectedCount
            ]);

            if ($movementsCount > $expectedCount) {
                \Log::error('DOUBLONS DÉTECTÉS!', [
                    'shift_id' => $shift->id,
                    'attendu' => $expectedCount,
                    'obtenu' => $movementsCount,
                    'doublons' => $movementsCount - $expectedCount
                ]);
                
                throw new \Exception('Doublons de mouvements de stock détectés!');
            }

            DB::commit();

            $stockMessage = '';
            foreach ($stockUpdates as $fuelType => $update) {
                $stockMessage .= 
                    ucfirst($fuelType) . " (Cuve {$update['tank_number']}): " .
                    "-" . number_format($update['quantity'], 2) . " L " .
                    "(Stock: " . number_format($update['before'], 2) . " → " . 
                    number_format($update['after'], 2) . " L). ";
            }

            return redirect()->route('manager.history')
                ->with('success', 'Saisie enregistrée avec succès! En attente de validation.')
                ->with('info', $stockMessage ? "Stock mis à jour: $stockMessage" : '');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur storeIndex', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

private function processShiftStockSales($shift, $salesByFuelType, $request)
{
    $results = [];
    $user = Auth::user();
    
    foreach ($salesByFuelType as $fuelType => $saleData) {
        if ($saleData['quantity'] > 0) {
            try {
                // Préparer les données pour le service de synchronisation
                $saleParams = [
                    'station_id' => $user->station_id,
                    'fuel_type' => $fuelType,
                    'quantity' => $saleData['quantity'],
                    'unit_price' => $saleData['total_amount'] / $saleData['quantity'],
                    'recorded_by' => $user->id,
                    'shift_id' => $shift->id,
                    'customer_name' => $request->responsible_name . ' (Shift)',
                    'sale_date' => $request->shift_date
                ];
                
                // Utiliser le service de synchronisation
                $result = app(StockSyncService::class)->safeSaleRegistration(
                    $saleParams, 
                    'shift'
                );
                
                if ($result['success']) {
                    $results[$fuelType] = [
                        'success' => true,
                        'sale_id' => $result['sale_id'],
                        'movement_id' => $result['movement_id']
                    ];
                }
                
            } catch (\Exception $e) {
                \Log::error('Erreur traitement shift', [
                    'fuel_type' => $fuelType,
                    'error' => $e->getMessage(),
                    'shift_id' => $shift->id
                ]);
                
                $results[$fuelType] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                throw $e;
            }
        }
    }
    
    return $results;
}
    /**
 * Trouver une cuve adaptée pour un type de carburant
 */
    private function findTankForFuelType($stationId, $fuelType)
    {
        $normalizedType = $this->normalizeFuelType($fuelType);  // ICI
        
        $tank = Tank::where('station_id', $stationId)
            ->where(function($query) use ($normalizedType, $fuelType) {
                $query->where('fuel_type', 'LIKE', $normalizedType)
                      ->orWhere('fuel_type', 'LIKE', $fuelType);
            })
            ->orderBy('current_volume', 'desc')
            ->first();
        
        return $tank;
    }

    public function checkStockForType(Request $request)
    {
        try {
            $user = Auth::user();
            $stationId = $user->station_id;
            
            if (!$stationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non assigné à une station'
                ], 400);
            }

            $fuelType = strtolower(trim($request->input('fuel_type')));
            $quantity = (float) $request->input('quantity');
            
            if (!$fuelType || $quantity <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides'
                ], 400);
            }

            // ========== GESTION DES ALIAS ==========
            $fuelAliases = [
                // Format: alias => type principal
                'gasoil' => 'gasoil',
                'gazole' => 'gasoil',
                
                
                'super' => 'super',
            
                
                
                'essence pirogue' => 'essence pirogue',
                
            ];
            
            // Normaliser le type de carburant
            $normalizedType = $this->normalizeFuelType($fuelType);
            
            \Log::info('Recherche cuve', [
                'type_original' => $fuelType,
                'type_normalise' => $normalizedType,
                'station_id' => $stationId,
                'quantite' => $quantity
            ]);
            
            // Chercher la cuve avec le type normalisé
            $tank = Tank::where('station_id', $stationId)
                ->where(function($query) use ($normalizedType, $fuelType) {
                    // 1. Chercher avec le type normalisé
                    $query->where('fuel_type', 'LIKE', $normalizedType);
                    
                    // 2. Chercher avec l'original (au cas où)
                    $query->orWhere('fuel_type', 'LIKE', $fuelType);
                    
                    // 3. Chercher avec recherche partielle
                    $query->orWhere('fuel_type', 'LIKE', '%' . $normalizedType . '%');
                    $query->orWhere('fuel_type', 'LIKE', '%' . $fuelType . '%');
                })
                ->orderBy('current_volume', 'desc')
                ->first();

            // Debug: Voir toutes les cuves disponibles
            if (!$tank) {
                $allTanks = Tank::where('station_id', $stationId)
                    ->select('id', 'number', 'fuel_type', 'current_volume', 'capacity')
                    ->get();
                
                \Log::warning('Cuve non trouvée après recherche', [
                    'type_recherche' => $fuelType,
                    'type_normalise' => $normalizedType,
                    'cuves_disponibles' => $allTanks->toArray(),
                    'requete_sql' => Tank::where('station_id', $stationId)
                        ->where(function($query) use ($normalizedType, $fuelType) {
                            $query->where('fuel_type', 'LIKE', $normalizedType)
                                ->orWhere('fuel_type', 'LIKE', $fuelType);
                        })
                        ->toSql()
                ]);
            }

            if (!$tank) {
                // Essayer une recherche plus large
                $tank = Tank::where('station_id', $stationId)
                    ->where('current_volume', '>', 0)
                    ->first();
                    
                if ($tank) {
                    \Log::info('Cuve trouvée avec recherche large', [
                        'cuve_trouvee' => $tank->toArray(),
                        'type_recherche' => $fuelType
                    ]);
                }
            }

            if (!$tank) {
                $availableTanks = Tank::where('station_id', $stationId)
                    ->select('fuel_type', 'number', 'current_volume')
                    ->get();
                
                return response()->json([
                    'success' => false,
                    'message' => sprintf(
                        "Aucune cuve trouvée pour: %s. Cuves disponibles: %s",
                        ucfirst($fuelType),
                        $availableTanks->isEmpty() 
                            ? 'Aucune cuve' 
                            : implode(', ', $availableTanks->map(fn($t) => "Cuve {$t->number} ({$t->fuel_type})")->toArray())
                    ),
                    'debug' => [
                        'recherche_originale' => $fuelType,
                        'recherche_normalisee' => $normalizedType,
                        'cuves_disponibles' => $availableTanks
                    ]
                ]);
            }

            // Vérifier le stock
            $availableStock = (float) $tank->current_volume;
            $isAvailable = $availableStock >= $quantity;
            
            if (!$isAvailable) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf(
                        "Stock insuffisant dans la cuve %s (%s). Disponible: %s L, Requis: %s L",
                        $tank->number,
                        ucfirst($tank->fuel_type),
                        number_format($availableStock, 2),
                        number_format($quantity, 2)
                    ),
                    'available' => false,
                    'current_stock' => $availableStock
                ]);
            }

            return response()->json([
                'success' => true,
                'available' => true,
                'current_stock' => $availableStock,
                'remaining_after' => $availableStock - $quantity,
                'tank_info' => [
                    'id' => $tank->id,
                    'number' => $tank->number,
                    'fuel_type' => $tank->fuel_type,
                    'capacity' => $tank->capacity,
                    'current_volume' => $tank->current_volume,
                    'fill_percentage' => $tank->capacity > 0 
                        ? round(($tank->current_volume / $tank->capacity) * 100, 1) 
                        : 0
                ],
                'message' => sprintf(
                    "Cuve %s (%s): Stock disponible %s L",
                    $tank->number,
                    ucfirst($tank->fuel_type),
                    number_format($availableStock, 2)
                )
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur checkStockForType', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }


public function checkStockBeforeSave(Request $request)
{
    try {
        $salesByFuelType = [
            'super' => 0,
            'gazole' => 0,
        ];
        
        // Calculer les quantités par type de carburant
        foreach ($request->pumps as $pump) {
            $opening = floatval($pump['opening_index']);
            $closing = floatval($pump['closing_index']);
            $returnVal = floatval($pump['total_return'] ?? 0);
            
            if ($closing >= $opening) {
                $literage = ($closing - $opening) - $returnVal;
                if ($literage < 0) $literage = 0;
            } else {
                $literage = 0;
            }
            
            $fuelType = strtolower($pump['fuel_type']);
            // Normaliser le nom du carburant
            if ($fuelType == 'gasoil' || $fuelType == 'diesel') {
                $fuelType = 'gazole';
            }
            
            if (isset($salesByFuelType[$fuelType])) {
                $salesByFuelType[$fuelType] += $literage;
            }
        }
        
        // Vérifier chaque type de carburant
        $results = [];
        $canProceed = true;
        $messages = [];
        
        foreach ($salesByFuelType as $fuelType => $quantity) {
            if ($quantity > 0) {
                $currentStock = \App\Models\StockMovement::currentStock($fuelType);
                $canSell = $currentStock >= $quantity;
                
                $results[$fuelType] = [
                    'current_stock' => $currentStock,
                    'quantity' => $quantity,
                    'can_sell' => $canSell,
                    'remaining' => $canSell ? $currentStock - $quantity : $currentStock,
                ];
                
                if (!$canSell) {
                    $canProceed = false;
                    $fuelName = $fuelType == 'super' ? 'Super' : 'Gasoil';
                    $messages[] = "Stock insuffisant pour $fuelName: Stock actuel: {$currentStock}L, Vente demandée: {$quantity}L";
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'can_proceed' => $canProceed,
            'results' => $results,
            'message' => $canProceed ? 
                'Stock suffisant pour toutes les ventes' : 
                implode(' | ', $messages)
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la vérification du stock: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Helper pour obtenir le prix moyen d'un carburant
 */
private function getAveragePriceForFuel($fuelType, $pumps)
{
    $totalPrice = 0;
    $count = 0;
    
    foreach ($pumps as $pump) {
        if (strtolower($pump['fuel_type']) === $fuelType) {
            $totalPrice += floatval($pump['unit_price']);
            $count++;
        }
    }
    
    return $count > 0 ? $totalPrice / $count : 0;
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
    
    $query = ShiftSaisie::with(['pompeDetails', 'depenses', 'station'])
        ->where('user_id', auth()->id());
    
    if ($user->isManager()) {
        $query->where('station_id', $user->station_id);
    }
    
    // AJOUTEZ CECI POUR DÉBOGUER
    $totalCount = $query->count();
    $latestShift = $query->latest()->first();
    
    \Log::info('Historique des saisies', [
        'total_saisies' => $totalCount,
        'derniere_saisie_id' => $latestShift?->id,
        'derniere_saisie_date' => $latestShift?->date_shift,
        'derniere_saisie_created_at' => $latestShift?->created_at
    ]);
    
    $saisies = $query->orderBy('date_shift', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate(10);
        
    // Afficher la page actuelle
    \Log::info('Pagination', [
        'page_actuelle' => $saisies->currentPage(),
        'par_page' => $saisies->perPage(),
        'total' => $saisies->total(),
        'premiere_page' => $saisies->onFirstPage()
    ]);

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
    
        private function normalizeFuelType($fuelType)
    {
        $type = strtolower(trim($fuelType));
        
        $mapping = [
            'gasoil' => 'gazole',
            'gazole' => 'gazole',
            
            'super' => 'super',
            'essence' => 'super',
            'essence-pirogue' => 'essence pirogue',
        ];
        
        return $mapping[$type] ?? $type;
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