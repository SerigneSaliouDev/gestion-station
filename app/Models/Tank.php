<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tank extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
    'number',
    'description',
    'fuel_type',
    'capacity',
    'current_level_cm',
    'current_volume',
    'calibration_table',
    'tolerance_threshold',
    'product_category',
    'diameter_cm',
    'length_cm',
    'min_safe_level',
    'max_safe_level',
    'calibration_date',
    'calibration_certificate',
    'manufacturer',
    'serial_number',
    'last_measurement_date',
    'station_id',
    // Colonnes conditionnelles
    'status',
    'shape',
    'installation_date',
    'last_maintenance_date',
];

    protected $casts = [
        'calibration_table' => 'array',
        'current_level_cm' => 'decimal:2',
        'current_volume' => 'decimal:2',
        'capacity' => 'integer',
        'tolerance_threshold' => 'decimal:3',
        'diameter_cm' => 'decimal:2',
        'length_cm' => 'decimal:2',
        'min_safe_level' => 'decimal:2',
        'max_safe_level' => 'decimal:2',
        'calibration_date' => 'date',
        'installation_date' => 'date',
        'last_maintenance_date' => 'date',
    ];

    /**
     * Relation avec la station
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Relation avec les jaugeages
     */
    public function levels(): HasMany
    {
        return $this->hasMany(TankLevel::class, 'tank_number', 'number');
    }

    /**
     * Dernier jaugeage
     */
    public function latestLevel()
    {
        return $this->hasOne(TankLevel::class, 'tank_number', 'number')
            ->where('station_id', $this->station_id)
            ->latest('measurement_date');
    }

    /**
     * Scope pour les cuves actives
     */
    public function scopeActive($query)
    {
        
        return $query->where('status', 'active');
    }

    /**
     * Calculer le volume disponible
     */
    public function getAvailableCapacityAttribute()
    {
        return $this->capacity - $this->current_volume;
    }

    /**
     * Calculer le pourcentage de remplissage
     */
    public function getFillPercentageAttribute()
    {
        if ($this->capacity <= 0) {
            return 0;
        }
        return ($this->current_volume / $this->capacity) * 100;
    }

    /**
     * Calculer le volume à partir de la hauteur
     */
    public function calculateVolumeFromHeight($heightCm)
    {
        // Si une table de jaugeage existe, l'utiliser
        if ($this->calibration_table && is_array($this->calibration_table)) {
            return $this->interpolateFromCalibrationTable($heightCm);
        }
        
        // Sinon, calculer selon la forme
        return $this->calculateVolumeFromShape($heightCm);
    }

    /**
     * Calculer la hauteur à partir du volume
     */
    public function calculateHeightFromVolume($volumeLiters)
    {
        if ($this->calibration_table && is_array($this->calibration_table)) {
            return $this->interpolateHeightFromVolume($volumeLiters);
        }
        
        return $this->calculateHeightFromShape($volumeLiters);
    }

    /**
     * Interpolation depuis la table de calibration
     */
    private function interpolateFromCalibrationTable($heightCm)
    {
        $table = $this->calibration_table;
        if (empty($table)) {
            return 0;
        }
        
        ksort($table);
        
        $prevHeight = null;
        $prevVolume = null;
        
        foreach ($table as $volume => $tableHeight) {
            if ($tableHeight >= $heightCm) {
                if ($prevHeight === null) {
                    return $volume;
                }
                
                // Interpolation linéaire
                $ratio = ($heightCm - $prevHeight) / ($tableHeight - $prevHeight);
                return $prevVolume + $ratio * ($volume - $prevVolume);
            }
            
            $prevHeight = $tableHeight;
            $prevVolume = $volume;
        }
        
        return $prevVolume; // Dernier volume si hors table
    }

    /**
     * Calculer le volume selon la forme
     */
    private function calculateVolumeFromShape($heightCm)
    {
        if ($heightCm <= 0) return 0;
        
        if ($this->shape === 'cylindrical' && $this->diameter_cm) {
            $radius = $this->diameter_cm / 2;
            $baseArea = pi() * pow($radius, 2);
            return ($baseArea * $heightCm) / 1000; // Conversion en litres
        }
        
        if ($this->shape === 'rectangular' && $this->length_cm && $this->diameter_cm) {
            $baseArea = $this->length_cm * $this->diameter_cm;
            return ($baseArea * $heightCm) / 1000;
        }
        
        // Calcul approximatif pour capacité standard
        return ($heightCm / 100) * $this->capacity;
    }

    /**
     * Seuil de tolérance en pour-mille
     */
    public function getTolerancePermilleAttribute()
    {
        return $this->tolerance_threshold * 1000;
    }
        public function tankLevels()
    {
        return $this->hasMany(TankLevel::class, 'tank_number', 'number');
    }
        public function lastTankLevel()
    {
        return $this->hasOne(TankLevel::class, 'tank_number', 'number')
            ->latestOfMany('measurement_date');
    }
        public function getLastLevelAttribute()
    {
        
        
        // Méthode 2: Requête directe (plus fiable)
        return TankLevel::where('tank_number', $this->number)
            ->latest('measurement_date')
            ->first();
    }
}