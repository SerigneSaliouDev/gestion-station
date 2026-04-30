<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class TankLevel extends Model
{
    use HasFactory, SoftDeletes;

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
        'verified_by',
        'station_id',
        'is_acceptable',
        'temperature_corrected',
        'correction_factor',
        'uncorrected_volume',
        'tolerance_threshold',
        'product_category',
    ];

    protected $casts = [
        'measurement_date' => 'datetime',
        'level_cm' => 'decimal:2',
        'temperature_c' => 'decimal:2',
        'volume_liters' => 'decimal:2',
        'theoretical_stock' => 'decimal:2',
        'physical_stock' => 'decimal:2',
        'difference' => 'decimal:2',
        'difference_percentage' => 'decimal:2',
        'is_acceptable' => 'boolean',
        'temperature_corrected' => 'boolean',
        'correction_factor' => 'decimal:5',
        'uncorrected_volume' => 'decimal:2',
        'tolerance_threshold' => 'decimal:3',
        'water_level_cm' => 'decimal:2',
        'density' => 'decimal:4',
        'uncertainty' => 'decimal:3',
    ];

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tankLevel) {
            // Définir automatiquement measured_by si non fourni
            if (empty($tankLevel->measured_by) && Auth::check()) {
                $tankLevel->measured_by = Auth::id();
            }

            // Définir station_id depuis la cuve si non fourni
            if (empty($tankLevel->station_id) && $tankLevel->tank) {
                $tankLevel->station_id = $tankLevel->tank->station_id;
            }

            // Définir fuel_type depuis la cuve si non fourni
            if (empty($tankLevel->fuel_type) && $tankLevel->tank) {
                $tankLevel->fuel_type = $tankLevel->tank->fuel_type;
            }

            // Définir product_category si non fourni
            if (empty($tankLevel->product_category) && $tankLevel->fuel_type) {
                $tankLevel->product_category = self::getProductCategoryFromFuelType($tankLevel->fuel_type);
            }
        });
    }

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
     * Relation avec la station
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    /**
     * Relation avec la cuve
     */
    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class, 'tank_number', 'number');
    }

    /**
     * Relation avec la cuve via tank_id
     */
    public function tankById(): BelongsTo
    {
        return $this->belongsTo(Tank::class, 'tank_id');
    }

    /**
     * Relation avec l'utilisateur via user_id
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
     * Scope pour les écarts significatifs
     */
    public function scopeSignificantDiscrepancies($query)
    {
        return $query->where('is_acceptable', false)
                    ->orWhere(function($q) {
                        $q->where('difference_percentage', '>', 5); // > 5‰
                    });
    }

    /**
     * Scope pour une cuve spécifique
     */
    public function scopeForTank($query, $tankNumber)
    {
        return $query->where('tank_number', $tankNumber);
    }

    /**
     * Scope pour une période
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('measurement_date', [$startDate, $endDate]);
    }

    /**
     * Scope pour les mesures acceptables
     */
    public function scopeAcceptable($query)
    {
        return $query->where('is_acceptable', true);
    }

    /**
     * Scope pour les mesures inacceptables
     */
    public function scopeUnacceptable($query)
    {
        return $query->where('is_acceptable', false);
    }

    /**
     * Scope pour les mesures corrigées en température
     */
    public function scopeTemperatureCorrected($query)
    {
        return $query->where('temperature_corrected', true);
    }

    /**
     * Scope pour un type de carburant spécifique
     */
    public function scopeForFuelType($query, $fuelType)
    {
        return $query->where('fuel_type', $fuelType);
    }

    /**
     * Scope pour une catégorie de produit spécifique
     */
    public function scopeForProductCategory($query, $category)
    {
        return $query->where('product_category', $category);
    }

    /**
     * Scope pour les mesures récentes (derniers 30 jours)
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('measurement_date', '>=', now()->subDays($days));
    }

    /**
     * Formatter la date de mesure
     */
    public function getFormattedDateAttribute()
    {
        return $this->measurement_date->format('d/m/Y H:i');
    }

    /**
     * Formatter la date au format ISO
     */
    public function getIsoDateAttribute()
    {
        return $this->measurement_date->format('Y-m-d\TH:i:s');
    }

    /**
     * Obtenir la couleur selon l'écart
     */
    public function getStatusColorAttribute()
    {
        if (!$this->tolerance_threshold) {
            return 'secondary';
        }
        
        $absDiff = abs($this->difference_percentage);
        $threshold = $this->tolerance_threshold;
        
        if ($absDiff <= $threshold) {
            return 'success'; // Vert
        } elseif ($absDiff <= ($threshold * 1.5)) {
            return 'warning'; // Orange
        } else {
            return 'danger'; // Rouge
        }
    }

    /**
     * Obtenir l'icône selon l'écart
     */
    public function getStatusIconAttribute()
    {
        if (!$this->tolerance_threshold) {
            return 'question-circle';
        }
        
        $absDiff = abs($this->difference_percentage);
        $threshold = $this->tolerance_threshold;
        
        if ($absDiff <= $threshold) {
            return 'check-circle';
        } elseif ($absDiff <= ($threshold * 1.5)) {
            return 'exclamation-triangle';
        } else {
            return 'times-circle';
        }
    }

    /**
     * Vérifier si l'écart nécessite une alerte
     */
    public function getRequiresAlertAttribute()
    {
        if (!$this->tolerance_threshold) {
            return false;
        }
        
        return abs($this->difference_percentage) > $this->tolerance_threshold;
    }

    /**
     * Formatter la différence en pourcentage
     */
    public function getFormattedDifferencePercentageAttribute()
    {
        $sign = $this->difference_percentage >= 0 ? '+' : '';
        return $sign . number_format($this->difference_percentage, 2) . '‰';
    }

    /**
     * Formatter la différence en litres
     */
    public function getFormattedDifferenceAttribute()
    {
        $sign = $this->difference >= 0 ? '+' : '';
        return $sign . number_format($this->difference, 2) . ' L';
    }

    /**
     * Formatter le volume
     */
    public function getFormattedVolumeAttribute()
    {
        return number_format($this->volume_liters, 2) . ' L';
    }

    /**
     * Formatter le stock théorique
     */
    public function getFormattedTheoreticalStockAttribute()
    {
        return number_format($this->theoretical_stock, 2) . ' L';
    }

    /**
     * Formatter le stock physique
     */
    public function getFormattedPhysicalStockAttribute()
    {
        return number_format($this->physical_stock, 2) . ' L';
    }

    /**
     * Obtenir le statut d'acceptabilité formaté
     */
    public function getStatusTextAttribute()
    {
        if ($this->is_acceptable) {
            return 'Acceptable';
        } else {
            return 'Inacceptable';
        }
    }

    /**
     * Obtenir le badge HTML pour le statut
     */
    public function getStatusBadgeAttribute()
    {
        $color = $this->status_color;
        $text = $this->status_text;
        
        return "<span class='badge badge-{$color}'>{$text}</span>";
    }

    /**
     * Obtenir le pourcentage de remplissage
     */
    public function getFillPercentageAttribute()
    {
        if (!$this->tank || $this->tank->capacity <= 0) {
            return 0;
        }
        
        return ($this->volume_liters / $this->tank->capacity) * 100;
    }

    /**
     * Obtenir le coefficient d'expansion thermique selon le type de carburant
     */
    public static function getExpansionCoefficient($fuelType)
    {
        $coefficients = [
            'super' => 0.0012,
            'essence' => 0.0012,
            'gasoil' => 0.0007,
            'diesel' => 0.0007,
            'kerosene' => 0.0009,
        ];
        
        return $coefficients[strtolower($fuelType)] ?? 0.0010;
    }

    /**
     * Obtenir la catégorie de produit depuis le type de carburant
     */
    public static function getProductCategoryFromFuelType($fuelType)
    {
        $categories = [
            'super' => 'essence',
            'essence' => 'essence',
            'gasoil' => 'gazole',
            'diesel' => 'gazole',
            'kerosene' => 'kerosene',
        ];
        
        return $categories[strtolower($fuelType)] ?? 'autre';
    }

    /**
     * Obtenir la tolérance par défaut selon le type de carburant
     */
    public static function getDefaultTolerance($fuelType)
    {
        $tolerances = [
            'super' => 0.005,     // 0.5% = 5‰
            'essence' => 0.005,   // 0.5%
            'gasoil' => 0.003,    // 0.3%
            'diesel' => 0.003,    // 0.3%
            'kerosene' => 0.004,  // 0.4%
        ];
        
        return $tolerances[strtolower($fuelType)] ?? 0.004;
    }

    /**
     * Calculer le facteur de correction de température
     */
    public static function calculateTemperatureCorrectionFactor($temperature, $fuelType)
    {
        $coefficient = self::getExpansionCoefficient($fuelType);
        return 1 + (($temperature - 20) * $coefficient);
    }

    /**
     * Appliquer correction de température au volume
     */
    public static function applyTemperatureCorrection($volume, $temperature, $fuelType)
    {
        $correctionFactor = self::calculateTemperatureCorrectionFactor($temperature, $fuelType);
        return $volume * $correctionFactor;
    }

    /**
     * Calculer la différence entre stock théorique et physique
     */
    public static function calculateDifference($theoretical, $physical)
    {
        return $physical - $theoretical;
    }

    /**
     * Calculer la différence en pour mille
     */
    public static function calculateDifferencePercentage($theoretical, $physical)
    {
        if ($theoretical == 0) {
            return 0;
        }
        
        $difference = $physical - $theoretical;
        return ($difference / $theoretical) * 1000;
    }

    /**
     * Vérifier si l'écart est acceptable
     */
    public static function isDifferenceAcceptable($differencePercentage, $toleranceThreshold)
    {
        return abs($differencePercentage) <= ($toleranceThreshold * 1000);
    }

    /**
     * Obtenir la classe CSS pour la barre de progression selon le pourcentage de remplissage
     */
    public function getProgressBarClassAttribute()
    {
        $percentage = $this->fill_percentage;
        
        if ($percentage > 90) {
            return 'bg-danger'; // Rouge - presque plein
        } elseif ($percentage > 75) {
            return 'bg-warning'; // Orange - haut
        } elseif ($percentage > 25) {
            return 'bg-success'; // Vert - normal
        } else {
            return 'bg-info'; // Bleu - bas
        }
    }

    /**
     * Obtenir les statistiques de jaugeage pour une cuve
     */
    public static function getTankStatistics($tankNumber, $days = 30)
    {
        $measurements = self::forTank($tankNumber)
            ->recent($days)
            ->get();
        
        if ($measurements->isEmpty()) {
            return null;
        }
        
        $totalMeasurements = $measurements->count();
        $acceptable = $measurements->where('is_acceptable', true)->count();
        $unacceptable = $measurements->where('is_acceptable', false)->count();
        
        $avgDifference = $measurements->avg('difference_percentage');
        $maxDifference = $measurements->max('difference_percentage');
        $minDifference = $measurements->min('difference_percentage');
        
        $lastMeasurement = $measurements->first();
        
        return [
            'total_measurements' => $totalMeasurements,
            'acceptable' => $acceptable,
            'unacceptable' => $unacceptable,
            'acceptance_rate' => $totalMeasurements > 0 ? ($acceptable / $totalMeasurements) * 100 : 0,
            'avg_difference_percentage' => $avgDifference,
            'max_difference_percentage' => $maxDifference,
            'min_difference_percentage' => $minDifference,
            'last_measurement' => $lastMeasurement,
            'last_measurement_date' => $lastMeasurement->formatted_date,
            'days_analyzed' => $days,
        ];
    }

    /**
     * Générer un rapport de jaugeage
     */
    public function generateReport()
    {
        return [
            'id' => $this->id,
            'tank_number' => $this->tank_number,
            'fuel_type' => $this->fuel_type,
            'measurement_date' => $this->formatted_date,
            'level_cm' => $this->level_cm,
            'temperature_c' => $this->temperature_c,
            'volume_liters' => $this->volume_liters,
            'theoretical_stock' => $this->theoretical_stock,
            'physical_stock' => $this->physical_stock,
            'difference' => $this->difference,
            'difference_percentage' => $this->difference_percentage,
            'formatted_difference' => $this->formatted_difference,
            'formatted_difference_percentage' => $this->formatted_difference_percentage,
            'is_acceptable' => $this->is_acceptable,
            'status_text' => $this->status_text,
            'status_color' => $this->status_color,
            'tolerance_threshold' => $this->tolerance_threshold,
            'temperature_corrected' => $this->temperature_corrected,
            'correction_factor' => $this->correction_factor,
            'uncorrected_volume' => $this->uncorrected_volume,
            'product_category' => $this->product_category,
            'observations' => $this->observations,
            'measured_by' => $this->measurer ? $this->measurer->name : 'N/A',
            'verified_by' => $this->verifier ? $this->verifier->name : 'N/A',
            'requires_alert' => $this->requires_alert,
            'created_at' => $this->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Exporter les données au format CSV
     */
    public function toCsvArray()
    {
        return [
            'Date de mesure' => $this->formatted_date,
            'Numéro cuve' => $this->tank_number,
            'Type carburant' => strtoupper($this->fuel_type),
            'Niveau (cm)' => $this->level_cm,
            'Température (°C)' => $this->temperature_c,
            'Volume (L)' => $this->volume_liters,
            'Stock théorique (L)' => $this->theoretical_stock,
            'Stock physique (L)' => $this->physical_stock,
            'Différence (L)' => $this->difference,
            'Différence (‰)' => $this->difference_percentage,
            'Acceptable' => $this->is_acceptable ? 'Oui' : 'Non',
            'Seuil tolérance' => $this->tolerance_threshold,
            'Corrigé température' => $this->temperature_corrected ? 'Oui' : 'Non',
            'Facteur correction' => $this->correction_factor,
            'Catégorie produit' => $this->product_category,
            'Observations' => $this->observations,
            'Mesuré par' => $this->measurer ? $this->measurer->name : '',
            'Vérifié par' => $this->verifier ? $this->verifier->name : '',
        ];
    }
}