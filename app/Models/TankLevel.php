<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TankLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'measurement_date',
        'tank_number',
        'fuel_type',
        'level_cm',
        'temperature_c',
        'volume_liters',
        'theoretical_stock',
        'physical_stock',
        'difference',
        'difference_percentage',
        'observations',
        'measured_by',
        'verified_by'
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'level_cm' => 'decimal:2',
        'temperature_c' => 'decimal:2',
        'volume_liters' => 'decimal:2',
        'theoretical_stock' => 'decimal:2',
        'physical_stock' => 'decimal:2',
        'difference' => 'decimal:2',
        'difference_percentage' => 'decimal:2'
    ];

    /**
     * Relation avec l'utilisateur qui a mesuré
     */
    public function measurer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'measured_by');
    }

    /**
     * Relation avec l'utilisateur qui a vérifié
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope pour les dernières mesures
     */
    public function scopeLatestMeasurements($query, $limit = 10)
    {
        return $query->orderBy('measurement_date', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit);
    }

    /**
     * Calculer les écarts de stock
     */
    public static function stockDiscrepancies($threshold = 1.0) // 1% par défaut
    {
        return self::where('difference_percentage', '>', $threshold)
                    ->orWhere('difference_percentage', '<', -$threshold)
                    ->orderBy('difference_percentage', 'desc')
                    ->get();
    }
}