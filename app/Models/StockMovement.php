<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'movement_date',
        'fuel_type',
        'movement_type',
        'quantity',
        'unit_price',
        'total_amount',
        'supplier_name',
        'invoice_number',
        'tank_number',
        'stock_before',
        'stock_after',
        'pump_number',
        'payment_method',
        'customer_type',
        'customer_name',
        'shift_id',
        'notes',
        'recorded_by',
        'verified_by',
        'verified_at',
        'station_id'
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
     * Relation avec l'utilisateur qui a enregistré le mouvement (recorded_by)
     * CORRECTION : enregistreur -> recorder
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
     * Calculer le stock actuel pour un type de carburant
     */
    public static function currentStock($fuelType)
    {
        $lastMovement = self::where('fuel_type', $fuelType)
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
            
        return $lastMovement ? $lastMovement->stock_after : 0;
    }

    /**
     * Vérifier si une vente peut être effectuée
     */
    public static function canSell($fuelType, $quantity)
    {
        $currentStock = self::currentStock($fuelType);
        return $currentStock >= $quantity;
    }
}