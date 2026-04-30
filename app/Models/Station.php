<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nom',
        'code',
        'ville',
        'adresse',
        'telephone',
        'email',
        'capacite_super',
        'capacite_gazole',
        'capacite_essence_pirogue', // Nouveau
        'manager_id',
        'statut',
        'notes'
    ];
    
    protected $casts = [
        'capacite_super' => 'decimal:2',
        'capacite_gazole' => 'decimal:2',
        'capacite_essence_pirogue' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Accesseurs pour les capacités formatées
     */
    public function getCapaciteSuperFormattedAttribute()
    {
        return number_format($this->capacite_super ?? 0, 0, ',', ' ') . ' L';
    }
    
    public function getCapaciteGazoleFormattedAttribute()
    {
        return number_format($this->capacite_gazole ?? 0, 0, ',', ' ') . ' L';
    }
    
    public function getCapaciteEssencePirogueFormattedAttribute()
    {
        return number_format($this->capacite_essence_pirogue ?? 0, 0, ',', ' ') . ' L';
    }
    
    public function getCapaciteTotaleAttribute()
    {
        return ($this->capacite_super ?? 0) + 
               ($this->capacite_gazole ?? 0) + 
               ($this->capacite_essence_pirogue ?? 0);
    }
    
    public function getCapaciteTotaleFormattedAttribute()
    {
        return number_format($this->capacite_totale, 0, ',', ' ') . ' L';
    }
    
    /**
     * Obtenir la capacité d'un type spécifique
     */
    public function getCapacite($type)
    {
        switch (strtolower($type)) {
            case 'super':
                return $this->capacite_super ?? 0;
            case 'gazole':
                return $this->capacite_gazole ?? 0;
            case 'essence_pirogue':
            case 'essence pirogue':
                return $this->capacite_essence_pirogue ?? 0;
            default:
                return 0;
        }
    }
    
    /**
     * Manager de la station
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    
    /**
     * Shifts de la station
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(ShiftSaisie::class);
    }
    
    /**
     * Tank levels (niveaux des cuves)
     */
    public function tankLevels(): HasMany
    {
        return $this->hasMany(TankLevel::class);
    }
    
    /**
     * Scope pour les stations actives
     */
    public function scopeActive($query)
    {
        return $query->where('statut', 'actif');
    }
    
    /**
     * Vérifier si la station peut accepter une livraison
     */
    public function canAcceptDelivery($fuelType, $quantity)
    {
        $capacity = $this->getCapacite($fuelType);
        $currentStock = $this->getCurrentStock($fuelType);
        
        return ($currentStock + $quantity) <= $capacity;
    }
    
    /**
     * Obtenir le stock actuel
     */
    public function getCurrentStock($fuelType)
    {
        $tankLevel = $this->tankLevels()
            ->where('fuel_type', $fuelType)
            ->orderBy('measurement_date', 'desc')
            ->first();
            
        return $tankLevel ? $tankLevel->physical_stock : 0;
    }
    
    /**
     * Obtenir le taux de remplissage
     */
    public function getFillPercentage($fuelType)
    {
        $capacity = $this->getCapacite($fuelType);
        $currentStock = $this->getCurrentStock($fuelType);
        
        if ($capacity <= 0) {
            return 0;
        }
        
        return ($currentStock / $capacity) * 100;
    }
        public function getStatusBadgeColor()
    {
        return $this->statut === 'active' ? 'success' : 
               ($this->statut === 'maintenance' ? 'warning' : 'danger');
    }
      public function getChiffreAffairesAttribute()
    {
        return $this->shifts()->sum('total_ventes');
    }
        public function sales()
    {
        return $this->hasMany(Sale::class);
    }
        public function users()
    {
        return $this->hasMany(User::class);
    }
        public function setStatutAttribute($value)
    {
        $this->attributes['statut'] = $value;
    }

        public function getTotalCapaciteAttribute()
    {
        // Priorité 1: Utiliser les capacités stockées dans la station
        if ($this->capacite_super > 0 || $this->capacite_gazole > 0) {
            return $this->capacite_super + $this->capacite_gazole;
        }
        
        // Priorité 2: Calculer à partir des cuves si les capacités ne sont pas stockées
        return $this->tanks()->sum('capacity');
    }
    
        public function getCapaciteSuperCalculatedAttribute()
    {
        if ($this->capacite_super > 0) {
            return $this->capacite_super;
        }
        
        return $this->tanks()->where('fuel_type', 'super')->sum('capacity');
    }

        public function getCapaciteGazoleCalculatedAttribute()
    {
        if ($this->capacite_gazole > 0) {
            return $this->capacite_gazole;
        }
        
        return $this->tanks()
            ->whereIn('fuel_type', ['gasoil', 'gazole', 'diesel'])
            ->sum('capacity');
    }

        public function tanks()
    {
        return $this->hasMany(Tank::class);
    }
        public function hasManager(): bool
    {
        return !is_null($this->manager_id);
    }
    
    // Récupérer le nom du manager
    public function getManagerName(): string
    {
        return $this->manager ? $this->manager->name : 'Non assigné';
    }

        public function scopeAvailableManagers($query)
    {
        return \App\Models\User::role('manager')
            ->where(function($q) {
                $q->whereNull('station_id')
                  ->orWhere('station_id', 0);
            });
    }
  
}