<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model
{
    use HasFactory;

      protected $fillable = [
            'station_id',
            'tank_id',
            'movement_date',
            'fuel_type',
            'movement_type',
            'quantity',
            'unit_price',
            'total_amount',
            'supplier_name',
            'customer_name',
            'invoice_number',
            'tank_number',
            'pump_number',
            'shift_id',
            'stock_before',
            'stock_after',
            'notes',
            'recorded_by',
            'verified_by',
            'verified_at',
            'shift_saisie_id',
            'auto_generated',
            'customer_type',
            'payment_method',
            'delivery_note_number',
            'driver_name',
            'temperature_c'

    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after' => 'decimal:2',
        'verified_at' => 'datetime'
    ];

    /**
     * Événements du modèle
     */
      protected static function boot()
    {
        parent::boot();

        // Avant de créer un mouvement, calculer le stock_before et stock_after
        static::creating(function ($movement) {
            self::calculateStock($movement);
        });

        // Avant de mettre à jour un mouvement, recalculer
        static::updating(function ($movement) {
            self::calculateStock($movement);
        });
    }

    /**
     * Calculer les stocks avant et après
     */
  protected static function calculateStock(&$movement)
{
    if (!$movement->station_id) {
        throw new \Exception("station_id est requis pour calculer le stock");
    }
    
    // CORRECTION CRITIQUE : Toujours filtrer par station_id
    $latestMovement = self::where('fuel_type', $movement->fuel_type)
        ->where('station_id', $movement->station_id) // <-- C'EST LE PLUS IMPORTANT
        ->orderBy('movement_date', 'desc')
        ->orderBy('created_at', 'desc')
        ->first();

    $stockBefore = $latestMovement ? $latestMovement->stock_after : 0;
    $movement->stock_before = $stockBefore;

    // Calculer le stock après selon le type de mouvement
    if ($movement->movement_type == 'reception') {
        $movement->stock_after = $stockBefore + $movement->quantity;
    } elseif ($movement->movement_type == 'vente') {
        // IMPORTANT: $movement->quantity est négatif pour les ventes
        $movement->stock_after = $stockBefore + $movement->quantity;
        
        if ($movement->stock_after < 0) {
            throw new \Exception("Stock insuffisant. Disponible: {$stockBefore} L");
        }
    } elseif ($movement->movement_type == 'ajustement') {
        $movement->stock_after = $stockBefore + $movement->quantity;
    } elseif ($movement->movement_type == 'transfert') {
        $movement->stock_after = $stockBefore;
    }
}

    /**
     * Relation avec l'utilisateur qui a enregistré le mouvement (recorded_by)
     */
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Relation avec l'utilisateur qui a vérifié (verified_by)
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Relation avec le shift (si applicable)
     */
    public function shift()
    {
        return $this->belongsTo(ShiftSaisie::class, 'shift_id');
    }

    /**
     * Relation avec la station
     */
    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Scope pour les mouvements récents
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('movement_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit);
    }

    /**
     * Scope pour les réceptions
     */
    public function scopeReceptions($query)
    {
        return $query->where('movement_type', 'reception');
    }

    /**
     * Scope pour les ventes
     */
    public function scopeVentes($query)
    {
        return $query->where('movement_type', 'vente');
    }

    /**
     * Calculer le stock actuel pour un type de carburant
     */
public static function currentStock($fuelType, $stationId = null)
{
    // Normaliser le type de carburant
    $normalizedFuelType = strtolower(trim($fuelType));
    
    // Mapping des types similaires
    $typeMapping = [
        'gasoil' => ['gasoil', 'gazole'],
        'essence' => ['super', 'essence'],
        'essence pirogue' => ['essence pirogue']
    ];
    
    $query = self::query();
    
    // Chercher dans le mapping
    foreach ($typeMapping as $key => $types) {
        if ($normalizedFuelType === $key || in_array($normalizedFuelType, $types)) {
            $query->whereIn('fuel_type', $types);
            break;
        }
    }
    
    // Si non trouvé dans le mapping, chercher exactement
    if ($query->getQuery()->wheres === []) {
        $query->where('fuel_type', $normalizedFuelType);
    }
    
    // Filtrer par station
    if ($stationId) {
        $query->where('station_id', $stationId);
    }
    
    // Récupérer le dernier mouvement
    $latestMovement = $query->orderBy('movement_date', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->first();
    
    return $latestMovement ? (float) $latestMovement->stock_after : 0;
}
    /**
     * Vérifier si une vente peut être effectuée
     */
    public static function canSell($fuelType, $quantity, $stationId = null)
    {
        $currentStock = self::currentStock($fuelType, $stationId);
        return $currentStock >= $quantity;
    }

    /**
     * Enregistrer une vente
     */
public static function recordSale(array $data)
{
    // Vérifier que station_id est présent
    if (!isset($data['station_id'])) {
        throw new \Exception("station_id est requis pour enregistrer une vente");
    }

    // Normaliser le type de carburant (gasoil -> gazole)
    $fuelType = strtolower($data['fuel_type']);
    if ($fuelType == 'gasoil') {
        $fuelType = 'gazole';
    }
    
    // Vérifier le stock disponible
    $currentStock = self::currentStock($fuelType, $data['station_id']);
    $quantity = abs($data['quantity']);
    
    if ($currentStock < $quantity) {
        throw new \Exception("Stock insuffisant. Disponible: $currentStock L, Demandé: {$quantity} L");
    }

    // Créer le mouvement
    $movement = self::create(array_merge($data, [
        'movement_type' => 'vente',
        'fuel_type' => $fuelType, // Utiliser le type normalisé
        'movement_date' => $data['movement_date'] ?? now(),
        'quantity' => -$quantity, // Négatif pour vente
    ]));

    return $movement;
}


    /**
     * Enregistrer une réception
     */
    public static function recordReception(array $data)
    {
        // Vérifier que station_id est présent
        if (!isset($data['station_id'])) {
            throw new \Exception("station_id est requis pour enregistrer une réception");
        }

        // Créer le mouvement de réception
        $movement = self::create(array_merge($data, [
            'movement_type' => 'reception',
            'movement_date' => $data['movement_date'] ?? now(),
            'quantity' => abs($data['quantity']), // Stock positif pour réception
        ]));

        return $movement;
    }

    /**
     * Obtenir l'historique des stocks pour une période
     */
    public static function stockHistory($fuelType, $stationId, $startDate = null, $endDate = null)
    {
        $query = self::where('fuel_type', $fuelType)
                    ->where('station_id', $stationId)
                    ->orderBy('movement_date', 'asc')
                    ->orderBy('created_at', 'asc');

        if ($startDate) {
            $query->where('movement_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('movement_date', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Obtenir le résumé des mouvements pour une période
     */
    public static function movementSummary($stationId, $startDate = null, $endDate = null)
    {
        $query = self::where('station_id', $stationId);

        if ($startDate) {
            $query->where('movement_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('movement_date', '<=', $endDate);
        }

        return $query->selectRaw('
                fuel_type,
                movement_type,
                SUM(quantity) as total_quantity,
                SUM(total_amount) as total_amount,
                COUNT(*) as movement_count
            ')
            ->groupBy('fuel_type', 'movement_type')
            ->get();
    }
        public function tank()
    {
        return $this->belongsTo(Tank::class);
    }
}