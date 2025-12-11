<?php

namespace App\Models;

use App\Models\TankLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Station extends Model
{
    use HasFactory;
    
    // Si vous n'avez pas de $fillable, utilisez $guarded pour autoriser l'affectation de masse.
    // protected $fillable = ['nom', 'code', 'adresse', 'etc.']; 
   protected $fillable = [
    'nom', 
    'name', 
    // Ajoutez ici TOUTES les autres colonnes NOT NULL de votre table
];

    // **********************************************
    // * RELATIONS POUR withCount et with *
    // **********************************************

    /**
     * Une station a plusieurs shifts de saisie (utilisé par withCount(['shifts']))
     */
    public function shifts(): HasMany
    {
        // La clé étrangère est 'station_id' dans le modèle ShiftSaisie
        return $this->hasMany(ShiftSaisie::class, 'station_id');
    }

    /**
     * Une station a plusieurs cuves (utilisé par withCount(['tanks']))
     */
    public function tanks(): HasMany
    {
        // La clé étrangère est 'station_id' dans le modèle Tank
        return $this->hasMany(TankLevel::class, 'station_id');
    }

    // **********************************************
    // * AUTRES RELATIONS UTILISÉES DANS LE CONTRÔLEUR *
    // **********************************************
    
    /**
     * Une station a plusieurs prix de carburant (utilisé par showStation)
     */
    public function fuelPrices(): HasMany
    {
        return $this->hasMany(FuelPrice::class, 'station_id');
    }
    
    /**
     * Une station reçoit plusieurs livraisons de carburant (utilisé par showStation)
     */
    public function fuelDeliveries(): HasMany
    {
        // Assurez-vous que FuelDelivery existe et a une FK 'station_id'
        return $this->hasMany(FuelDelivery::class, 'station_id');
    }

    // **********************************************
    // * VOS MÉTHODES D'ACCESSEUR DE STATS (Exemple) *
    // **********************************************
    
    // NOTE: Pour que le contrôleur fonctionne, les modèles Tank, FuelPrice, 
    // FuelDelivery doivent exister, ainsi que les méthodes suivantes :
    
    // public function getFillPercentage(string $type): float { /* ... */ }
    // public function getDaysOfSupply(string $type): int { /* ... */ }
    // public function getCurrentPrice(string $type): ?FuelPrice { /* ... */ }
    // public function getTotalStock(string $type): float { /* ... */ }
    // public function getTotalCapacity(string $type): float { /* ... */ }
    // public function getTotalSales(\DateTime $start, \DateTime $end): float { /* ... */ }
    // public function getTotalVolume(\DateTime $start, \DateTime $end): float { /* ... */ }
}