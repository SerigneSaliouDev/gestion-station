<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'fuel_type',
        'quantity',
        'unit_price',
        'total_amount',
        'sale_date',
        'customer_name',
        'customer_type',
        'payment_method',
        'pump_number',
        'shift_id',
        'tank_id',           // NOUVEAU
        'tank_number',       // NOUVEAU
        'pump_id',
        'recorded_by',
        'verified_by',
        'verified_at',
        'cancelled_at',
        'notes'
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    /**
     * Types de carburant disponibles
     */
    public static function getFuelTypes()
    {
        return [
            'super' => 'Super',
            'gazole' => 'Gazole',
            'essence pirogue' => 'Essence Pirogue'
        ];
    }

    /**
     * Relation avec la cuve
     */
    public function tank()
    {
        return $this->belongsTo(Tank::class, 'tank_id');
    }

    /**
     * Relation avec la station
     */
    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Relation avec l'utilisateur qui a enregistré
     */
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Relation avec l'utilisateur qui a vérifié
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Événements du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Après création d'une vente, mettre à jour le stock de la cuve
        static::created(function ($sale) {
            if ($sale->tank_id) {
                $sale->updateTankStock();
            }
        });

        // Après annulation d'une vente, restaurer le stock
        static::updated(function ($sale) {
            if ($sale->wasChanged('cancelled_at') && $sale->cancelled_at) {
                $sale->restoreTankStock();
            }
        });
    }

    /**
     * Mettre à jour le stock de la cuve après une vente
     */
    public function updateTankStock()
    {
        if (!$this->tank_id || $this->cancelled_at) {
            return;
        }

        $tank = Tank::find($this->tank_id);
        
        if ($tank) {
            // Calculer le nouveau volume
            $newVolume = max(0, $tank->current_volume - $this->quantity);
            
            // Mettre à jour la cuve
            $tank->update([
                'current_volume' => $newVolume,
                'current_level_cm' => $tank->capacity > 0 
                    ? ($newVolume / $tank->capacity) * 250 
                    : 0,
                'last_measurement_date' => now()
            ]);
            
            // Journaliser
            \Log::info('Stock de cuve mis à jour après vente', [
                'sale_id' => $this->id,
                'tank_id' => $tank->id,
                'tank_number' => $tank->number,
                'fuel_type' => $tank->fuel_type,
                'quantity_sold' => $this->quantity,
                'volume_before' => $tank->current_volume + $this->quantity,
                'volume_after' => $newVolume,
                'station_id' => $this->station_id
            ]);
        }
    }

    /**
     * Restaurer le stock de la cuve après annulation
     */
    public function restoreTankStock()
    {
        if (!$this->tank_id) {
            return;
        }

        $tank = Tank::find($this->tank_id);
        
        if ($tank) {
            // Restaurer le volume
            $newVolume = $tank->current_volume + $this->quantity;
            
            $tank->update([
                'current_volume' => $newVolume,
                'current_level_cm' => $tank->capacity > 0 
                    ? ($newVolume / $tank->capacity) * 250 
                    : 0,
                'last_measurement_date' => now()
            ]);
            
            \Log::info('Stock de cuve restauré après annulation de vente', [
                'sale_id' => $this->id,
                'tank_id' => $tank->id,
                'quantity_restored' => $this->quantity,
                'new_volume' => $newVolume
            ]);
        }
    }

    /**
     * Vérifier si la vente peut être effectuée (stock suffisant)
     */
    public static function canSell($tankId, $quantity)
    {
        $tank = Tank::find($tankId);
        
        if (!$tank) {
            return false;
        }

        return $tank->current_volume >= $quantity;
    }

    /**
     * Obtenir l'affichage formaté du type de carburant
     */
    public function getFuelTypeDisplayAttribute()
    {
        $types = [
            'super' => 'SUPER',
            'gazole' => 'GAZOLE',
            'essence pirogue' => 'ESSENCE PIROGUE'
        ];
        
        return $types[strtolower($this->fuel_type)] ?? strtoupper($this->fuel_type);
    }

    /**
     * Obtenir la couleur du badge selon le type de carburant
     */
    public function getFuelTypeBadgeClassAttribute()
    {
        $classes = [
            'super' => 'badge-danger',
            'gazole' => 'badge-success',
            'essence pirogue' => 'badge-warning'
        ];
        
        return $classes[strtolower($this->fuel_type)] ?? 'badge-secondary';
    }
}