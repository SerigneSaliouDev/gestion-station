<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Tank;
use App\Traits\FuelTypeTrait;
use App\Models\StockMovement;
use App\Models\ShiftSaisie;
use Carbon\Carbon;

class SaleController extends Controller
{
    /**
     * Afficher le formulaire d'enregistrement de vente
     */
       public function create()
    {
        $user = Auth::user();
        $stationId = $user->station_id;
        
        // Récupérer uniquement les cuves RÉELLES de la station
        $tanks = Tank::where('station_id', $stationId)
            ->where('current_volume', '>', 0)
            ->orderBy('fuel_type')
            ->orderBy('number')
            ->get()
            ->map(function ($tank) {
                $tank->fuel_type_display = $this->getFuelTypeDisplay($tank->fuel_type);
                $tank->badge_class = $this->getFuelTypeBadgeClass($tank->fuel_type);
                return $tank;
            });
        
        // Extraire les types de carburant normalisés
        $fuelTypes = $tanks->pluck('fuel_type')
            ->map(function($type) {
                return $this->normalizeFuelType($type);
            })
            ->unique()
            ->mapWithKeys(function($type) {
                return [$type => $this->getFuelTypeDisplay($type)];
            });
        
        // Autres données
        $paymentMethods = [
            'cash' => 'Espèces',
            'card' => 'Carte bancaire',
            'mobile_money' => 'Mobile Money',
            'credit' => 'Crédit'
        ];
        
        $customerTypes = [
            'retail' => 'Détail',
            'wholesale' => 'Gros',
            'corporate' => 'Entreprise'
        ];
        
        $currentShifts = ShiftSaisie::where('user_id', Auth::id())
            ->where('statut', 'en_attente')
            ->orderBy('date_shift', 'desc')
            ->get();
        
        return view('manager.sales.create', compact(
            'fuelTypes', 
            'paymentMethods', 
            'customerTypes',
            'currentShifts',
            'tanks'
        ));
    }

        /**
     * Enregistrer une vente
     */

    public function store(Request $request)
    {
        \Log::info('=== DÉBUT VENTE ===', $request->all());
        
        // 1. VALIDATION
        $data = $request->validate([
            'sale_date' => 'required|date',
            'tank_id' => 'required|exists:tanks,id',
            'quantity' => 'required|numeric|min:0.1|max:10000',
            'unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card,mobile_money,credit',
            'customer_type' => 'nullable|string|in:retail,wholesale,corporate',
            'shift_id' => 'nullable|exists:shift_saisies,id',
            'customer_name' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500'
        ]);
        
        // 2. VÉRIFICATION RENFORCÉE DES DOUBLONS AVEC NORMALISATION
        $tank = Tank::find($data['tank_id']);
        $normalizedFuelType = $this->normalizeFuelType($tank->fuel_type);
        
        $existingSale = Sale::where('tank_id', $data['tank_id'])
            ->where('quantity', $data['quantity'])
            ->where('fuel_type', $normalizedFuelType)
            ->where('sale_date', Carbon::parse($data['sale_date'])->format('Y-m-d H:i:s'))
            ->where('recorded_by', Auth::id())
            ->where('created_at', '>=', now()->subSeconds(30))
            ->first();
        
        if ($existingSale) {
            \Log::warning('Vente en double détectée et rejetée', [
                'existing_id' => $existingSale->id,
                'user_id' => Auth::id(),
                'tank_id' => $data['tank_id'],
                'fuel_type' => $normalizedFuelType
            ]);
            
            return redirect()->route('manager.sales.index')
                ->with('warning', 'Cette vente a déjà été enregistrée.');
        }
        
        // 3. GESTION AVANCÉE DES REQUÊTES PARALLÈLES
        $lockKey = 'sale_lock_' . Auth::id() . '_' . $data['tank_id'];
        
        if (!$lock = Cache::lock($lockKey, 3)) {
            \Log::warning('Impossible d\'obtenir le verrou pour la vente');
            return back()->with('error', 'Une vente est déjà en cours pour cette cuve.');
        }
        
        try {
            // 4. TRANSACTION AVEC INTÉGRATION DU TRAIT
            DB::transaction(function() use ($data, $tank, $normalizedFuelType) {
                
                // Vérifier le stock
                if (bccomp($tank->current_volume, $data['quantity'], 2) < 0) {
                    throw new \Exception(sprintf(
                        'Stock insuffisant dans la cuve %s (%s). Disponible: %s L, Demande: %s L',
                        $tank->number,
                        $this->getFuelTypeDisplay($tank->fuel_type),
                        number_format($tank->current_volume, 2),
                        number_format($data['quantity'], 2)
                    ));
                }
                
                // Mettre à jour la cuve
                $ancienVolume = $tank->current_volume;
                $nouveauVolume = $ancienVolume - $data['quantity'];
                
                $tank->update([
                    'current_volume' => $nouveauVolume,
                    'current_level_cm' => $tank->capacity > 0 ? ($nouveauVolume / $tank->capacity) * 250 : 0,
                    'last_measurement_date' => now()
                ]);
                
                // Enregistrer la vente
                $data['total_amount'] = $data['quantity'] * $data['unit_price'];
                $data['recorded_by'] = Auth::id();
                $data['station_id'] = Auth::user()->station_id;
                $data['fuel_type'] = $normalizedFuelType;
                $data['fuel_type_display'] = $this->getFuelTypeDisplay($normalizedFuelType);
                $data['tank_number'] = $tank->number;
                
                $sale = Sale::create($data);
                
                // Enregistrer le mouvement de stock
                StockMovement::create([
                    'station_id' => Auth::user()->station_id,
                    'tank_id' => $tank->id,
                    'tank_number' => $tank->number,
                    'movement_date' => $data['sale_date'],
                    'fuel_type' => $normalizedFuelType,
                    'movement_type' => 'vente',
                    'quantity' => -$data['quantity'],
                    'unit_price' => $data['unit_price'],
                    'total_amount' => $data['total_amount'],
                    'customer_name' => $data['customer_name'] ?? null,
                    'customer_type' => $data['customer_type'] ?? 'retail',
                    'payment_method' => $data['payment_method'],
                    'stock_before' => $ancienVolume,
                    'stock_after' => $nouveauVolume,
                    'notes' => $data['notes'] ?? null,
                    'recorded_by' => Auth::id(),
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                ]);
            }, 5);
            
            $lock->release();
            
            // 5. RÉPONSE DE SUCCÈS
            $tank = Tank::find($data['tank_id']);
            $pourcentage = $tank->capacity > 0 ? ($tank->current_volume / $tank->capacity) * 100 : 0;
            
            $message = "
                <div class='alert alert-success'>
                    <h5><i class='fas fa-check-circle'></i> VENTE RÉUSSIE!</h5>
                    <hr>
                    <p><strong>Type:</strong> {$this->getFuelTypeDisplay($normalizedFuelType)}</p>
                    <p><strong>Cuve:</strong> {$tank->number}</p>
                    <p><strong>Quantité vendue:</strong> " . number_format($data['quantity'], 2) . " L</p>
                    <p><strong>Montant total:</strong> " . number_format($data['total_amount'], 0) . " FCFA</p>
                    <hr>
                    <p><strong>Stock après vente:</strong> " . number_format($tank->current_volume, 2) . " L</p>
                </div>
            ";
            
            return redirect()->route('manager.sales.index')->with('success', $message);
            
        } catch (\Exception $e) {
            if (isset($lock)) $lock->release();
            
            \Log::error('❌ ERREUR VENTE:', [
                'message' => $e->getMessage(),
                'fuel_type' => $normalizedFuelType ?? 'inconnu'
            ]);
            
            return back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
        }
    }
    /**
     * Afficher la liste des ventes
     */
        public function index(Request $request)
    {
        $user = Auth::user();
        $stationId = $user->station_id;
        
        $query = Sale::with('tank')
            ->where('station_id', $stationId)
            ->whereNull('cancelled_at')
            ->orderBy('sale_date', 'desc');
            
        $sales = $query->paginate(20);
        
        // Cuves pour filtre
        $tanks = Tank::where('station_id', $stationId)->get();
        
        return view('manager.sales.index', compact('sales', 'tanks', 'request'));
    }
    public function cancel($id)
    {
        $sale = Sale::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            if ($sale->tank_id) {
                $tank = Tank::find($sale->tank_id);
                if ($tank) {
                    // Restaurer le stock
                    $tank->current_volume += $sale->quantity;
                    $tank->save();
                }
            }
            
            $sale->cancelled_at = now();
            $sale->cancelled_by = Auth::id();
            $sale->save();
            
            DB::commit();
            
            return redirect()->route('manager.sales.index')
                ->with('success', 'Vente annulée. Stock restauré.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    
    
    /**
     * API pour récupérer les cuves par type de carburant
     */
      public function getTanksByFuelType(Request $request)
    {
        $fuelType = $request->input('fuel_type');
        $stationId = Auth::user()->station_id;
        
        if (!$fuelType) {
            return response()->json(['error' => 'Type de carburant requis'], 400);
        }
        
        // Normaliser le type
        $normalizedType = $this->normalizeFuelType($fuelType);
        
        $tanks = Tank::where('station_id', $stationId)
            ->where(function($query) use ($normalizedType) {
                return $this->buildFuelTypeQuery($query, 'fuel_type', $normalizedType);
            })
            ->where('current_volume', '>', 0)
            ->orderBy('number')
            ->get()
            ->map(function ($tank) {
                $fillPercentage = $tank->capacity > 0 
                    ? ($tank->current_volume / $tank->capacity) * 100 
                    : 0;
                
                return [
                    'id' => $tank->id,
                    'number' => $tank->number,
                    'description' => $tank->description ?? 'Cuve ' . $tank->number,
                    'fuel_type' => $tank->fuel_type,
                    'fuel_type_display' => $this->getFuelTypeDisplay($tank->fuel_type),
                    'current_volume' => $tank->current_volume,
                    'capacity' => $tank->capacity,
                    'fill_percentage' => $fillPercentage,
                    'available_capacity' => $tank->capacity - $tank->current_volume,
                    'badge_class' => $this->getFuelTypeBadgeClass($tank->fuel_type),
                ];
            });
        
        return response()->json($tanks); 
    }

    
    /**
     * API pour vérifier le stock d'une cuve
     */
      public function checkTankStock(Request $request)
    {
        $tankId = $request->input('tank_id');
        $quantity = $request->input('quantity');
        
        $tank = Tank::find($tankId);
        
        if (!$tank) {
            return response()->json([
                'success' => false,
                'message' => 'Cuve non trouvée'
            ], 404);
        }
        
        // Vérifier que la cuve appartient à la station
        if ($tank->station_id != Auth::user()->station_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cuve non autorisée'
            ], 403);
        }
        
        $available = $tank->current_volume >= $quantity;
        $normalizedType = $this->normalizeFuelType($tank->fuel_type);
        $displayType = $this->getFuelTypeDisplay($tank->fuel_type);
        
        return response()->json([
            'success' => true,
            'available' => $available,
            'current_stock' => $tank->current_volume,
            'remaining_after' => $tank->current_volume - $quantity,
            'fill_percentage_after' => $tank->capacity > 0 
                ? (($tank->current_volume - $quantity) / $tank->capacity) * 100 
                : 0,
            'tank_info' => [
                'number' => $tank->number,
                'original_fuel_type' => $tank->fuel_type,
                'normalized_fuel_type' => $normalizedType,
                'fuel_type_display' => $displayType,
                'capacity' => $tank->capacity
            ],
            'message' => $available 
                ? 'Stock suffisant'
                : 'Stock insuffisant'
        ]);
    }

    
 
}