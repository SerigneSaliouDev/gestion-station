<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuel_type',
        'price_per_liter',
        'changed_by',
        'change_reason'
    ];

    protected $casts = [
        'price_per_liter' => 'decimal:2',
    ];

    /**
     * Relation avec l'utilisateur qui a modifié le prix
     */
    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope pour obtenir le prix actuel d'un carburant
     */
    public function scopeCurrentPrice($query, $fuelType)
    {
        return $query->where('fuel_type', $fuelType)
                    ->latest()
                    ->first();
    }

    /**
     * Scope pour obtenir l'historique des prix
     */
    public function scopeHistory($query, $fuelType = null)
    {
        if ($fuelType) {
            $query->where('fuel_type', $fuelType);
        }
        
        return $query->orderBy('created_at', 'desc')
                    ->with('changer');
    }
}