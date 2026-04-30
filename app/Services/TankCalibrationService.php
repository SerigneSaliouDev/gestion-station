<?php

namespace App\Services;

class TankCalibrationService
{
    // Seuils de tolérance en POUR MILLE (‰)
    private static $toleranceThresholds = [
        'super' => 5.0,           // 5‰ = 0.5%
        'gazole' => 3.0,          // 3‰ = 0.3%
        'essence piroge' => 5.0,  // 5‰ = 0.5%
        'kerosene' => 4.0,        // 4‰ = 0.4%
    ];

    // Tables de calibration CORRECTES (hauteur_mm => volume_litres)
    // IMPORTANT: Les hauteurs sont en mm et doivent être croissantes
    private static $calibrationTables = [
        '5000' => [
            0 => 0,
            100 => 200,
            200 => 400,
            300 => 600,
            400 => 800,
            500 => 1000,
            600 => 1200,
            700 => 1400,
            800 => 1600,
            900 => 1800,
            1000 => 2000,
            1100 => 2200,
            1200 => 2400,
            1300 => 2600,
            1400 => 2800,
            1500 => 3000,
            1600 => 3200,
            1700 => 3400,
            1800 => 3600,
            1900 => 3800,
            2000 => 4000,
            2100 => 4200,
            2200 => 4400,
            2300 => 4600,
            2400 => 4800,
            2500 => 5000
        ],
        '10000' => [
            0 => 0,
            100 => 400,
            200 => 800,
            300 => 1200,
            400 => 1600,
            500 => 2000,
            600 => 2400,
            700 => 2800,
            800 => 3200,
            900 => 3600,
            1000 => 4000,
            1100 => 4400,
            1200 => 4800,
            1300 => 5200,
            1400 => 5600,
            1500 => 6000,
            1600 => 6400,
            1700 => 6800,
            1800 => 7200,
            1900 => 7600,
            2000 => 8000,
            2100 => 8400,
            2200 => 8800,
            2300 => 9200,
            2400 => 9600,
            2500 => 10000
        ],
        '15000' => [
            0 => 0,
            100 => 600,
            200 => 1200,
            300 => 1800,
            400 => 2400,
            500 => 3000,
            600 => 3600,
            700 => 4200,
            800 => 4800,
            900 => 5400,
            1000 => 6000,
            1100 => 6600,
            1200 => 7200,
            1300 => 7800,
            1400 => 8400,
            1500 => 9000,
            1600 => 9600,
            1700 => 10200,
            1800 => 10800,
            1900 => 11400,
            2000 => 12000,
            2100 => 12600,
            2200 => 13200,
            2300 => 13800,
            2400 => 14400,
            2500 => 15000
        ],
        '20000' => [
            0 => 0,
            100 => 800,
            200 => 1600,
            300 => 2400,
            400 => 3200,
            500 => 4000,
            600 => 4800,
            700 => 5600,
            800 => 6400,
            900 => 7200,
            1000 => 8000,
            1100 => 8800,
            1200 => 9600,
            1300 => 10400,
            1400 => 11200,
            1500 => 12000,
            1600 => 12800,
            1700 => 13600,
            1800 => 14400,
            1900 => 15200,
            2000 => 16000,
            2100 => 16800,
            2200 => 17600,
            2300 => 18400,
            2400 => 19200,
            2500 => 20000
        ],
    ];

    /**
     * Récupère la table de calibration pour une capacité donnée
     */
    public static function getCalibrationTable($tankCapacity)
    {
        $capacityKey = (string) $tankCapacity;
        
        if (isset(self::$calibrationTables[$capacityKey])) {
            return self::$calibrationTables[$capacityKey];
        }
        
        // Chercher la capacité la plus proche
        $standardCapacities = ['5000', '10000', '15000', '20000'];
        $closestCapacity = null;
        $minDifference = PHP_INT_MAX;
        
        foreach ($standardCapacities as $standard) {
            $difference = abs((int)$standard - $tankCapacity);
            if ($difference < $minDifference) {
                $minDifference = $difference;
                $closestCapacity = $standard;
            }
        }
        
        if ($closestCapacity !== null) {
            return self::$calibrationTables[$closestCapacity];
        }
        
        return null;
    }

    /**
     * Convertit le niveau en mm en volume en litres
     * @param int $tankCapacity Capacité totale de la cuve en litres
     * @param int $heightMm Hauteur mesurée en millimètres
     * @return float Volume en litres
     */
    public static function getVolumeFromHeight($tankCapacity, $heightMm)
    {
        $table = self::getCalibrationTable($tankCapacity);
        
        if (empty($table)) {
            return self::calculateGenericVolume($tankCapacity, $heightMm);
        }

        $heights = array_keys($table);
        $minHeight = min($heights);
        $maxHeight = max($heights);
        
        if ($heightMm <= $minHeight) {
            return $table[$minHeight] ?? 0;
        }
        
        if ($heightMm >= $maxHeight) {
            return $table[$maxHeight] ?? $tankCapacity;
        }

        // Recherche dans le tableau
        $closestHeightBelow = null;
        $closestVolumeBelow = null;
        $closestHeightAbove = null;
        $closestVolumeAbove = null;
        
        foreach ($table as $height => $volume) {
            if ($height == $heightMm) {
                return $volume;
            }
            
            if ($height < $heightMm) {
                if ($closestHeightBelow === null || $height > $closestHeightBelow) {
                    $closestHeightBelow = $height;
                    $closestVolumeBelow = $volume;
                }
            }
            
            if ($height > $heightMm && $closestHeightAbove === null) {
                $closestHeightAbove = $height;
                $closestVolumeAbove = $volume;
            }
        }
        
        // Interpolation linéaire
        if ($closestHeightBelow !== null && $closestHeightAbove !== null) {
            return self::interpolate(
                $closestHeightBelow, $closestVolumeBelow,
                $closestHeightAbove, $closestVolumeAbove,
                $heightMm
            );
        }
        
        return 0;
    }

    /**
     * Formule générique pour les cuves sans table
     */
    private static function calculateGenericVolume($capacity, $heightMm)
    {
        $maxHeight = self::estimateMaxHeight($capacity);
        $ratio = $heightMm / $maxHeight;
        $correctionFactor = 0.9 + (0.2 * $ratio);
        return min($capacity, $capacity * $ratio * $correctionFactor);
    }

    /**
     * Estime la hauteur maximale d'une cuve
     */
    private static function estimateMaxHeight($capacity)
    {
        $heights = [
            5000 => 2500,   // 250 cm
            10000 => 2500,  // 250 cm
            15000 => 2500,  // 250 cm
            20000 => 2500,  // 250 cm
            25000 => 2800,  // 280 cm
            30000 => 3000,  // 300 cm
            35000 => 3200,  // 320 cm
            40000 => 3500,  // 350 cm
            50000 => 3800,  // 380 cm
        ];
        
        return $heights[$capacity] ?? 2500;
    }

    /**
     * Interpolation linéaire
     */
    private static function interpolate($x1, $y1, $x2, $y2, $x)
    {
        if ($x2 == $x1) {
            return $y1;
        }
        return $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);
    }

    /**
     * Récupère le seuil de tolérance en POUR MILLE (‰)
     */
    public static function getToleranceThreshold($fuelType)
    {
        $normalizedType = self::normalizeFuelType($fuelType);
        return self::$toleranceThresholds[$normalizedType] ?? 4.0; // 4‰ par défaut
    }

    /**
     * Vérifie si un écart est acceptable (écart en ‰, seuil en ‰)
     */
    public static function isDiscrepancyAcceptable($fuelType, $differencePerMille)
    {
        $threshold = self::getToleranceThreshold($fuelType);
        return abs($differencePerMille) <= $threshold;
    }

    /**
     * Normalise le type de carburant
     */
    public static function normalizeFuelType(string $fuelType): string
    {
        $type = strtolower(trim($fuelType));
        
        $mapping = [
            'gasoil' => 'gazole',
            'gazole' => 'gazole',
           
            'super' => 'super',
           
            
           
            'essence pirogue' => 'essence piroge',
            
           
        ];
        
        return $mapping[$type] ?? $type;
    }

    /**
     * Obtenir la classe CSS du badge
     */
    public static function getFuelTypeBadgeClass(string $fuelType): string
    {
        $normalized = self::normalizeFuelType($fuelType);
        
        $classes = [
            'super' => 'badge-danger',
            'gazole' => 'badge-success',
            'essence piroge' => 'badge-warning',
            
        ];
        
        return $classes[$normalized] ?? 'badge-secondary';
    }

    /**
     * Obtenir la classe CSS pour la barre de progression
     */
    public static function getProgressBarClass(float $percentage): string
    {
        if ($percentage > 90) return 'danger';
        if ($percentage > 75) return 'warning';
        if ($percentage > 25) return 'success';
        return 'info';
    }

    /**
     * Obtenir la colonne de capacité dans la table stations
     */
    public static function getStationCapacityColumn(string $fuelType): ?string
    {
        $normalized = self::normalizeFuelType($fuelType);
        
        $mapping = [
            'super' => 'capacite_super',
            'gazole' => 'capacite_gazole',
            'essence piroge' => 'capacite_pirogue',
            
        ];
        
        return $mapping[$normalized] ?? null;
    }

    /**
     * Obtenir les types de carburant pour les formulaires
     */
    public static function getFuelTypes(): array
    {
        return [
            'super' => 'Super',
            'gazole' => 'Gazole',
            'essence piroge' => 'Essence Pirogue',
            
        ];
    }

    /**
     * Obtient la couleur associée à un type de carburant
     */
    public static function getFuelTypeColor($fuelType)
    {
        $colors = [
            'super' => '#FF7F00',
            'gazole' => '#28a745',
            'essence piroge' => '#dc3545',
            'kerosene' => '#17a2b8'
        ];
        
        $normalized = self::normalizeFuelType($fuelType);
        return $colors[$normalized] ?? '#6c757d';
    }

    /**
     * Obtient le nom d'affichage formaté
     */
    public static function getDisplayName($fuelType)
    {
        $names = [
            'super' => 'SUPER',
            'gazole' => 'GAZOLE',
            'essence piroge' => 'ESSENCE PIROGUE',
            
        ];
        
        $normalized = self::normalizeFuelType($fuelType);
        return $names[$normalized] ?? strtoupper($fuelType);
    }

    /**
     * Obtient l'icône associée à un type de carburant
     */
    public static function getFuelTypeIcon($fuelType)
    {
        $icons = [
            'super' => 'fas fa-gas-pump',
            'gazole' => 'fas fa-oil-can',
            'essence piroge' => 'fas fa-fire',
            
        ];
        
        $normalized = self::normalizeFuelType($fuelType);
        return $icons[$normalized] ?? 'fas fa-question-circle';
    }

    /**
     * Vérifie si une table existe
     */
    public static function hasCalibrationTable($tankCapacity)
    {
        $capacityKey = (string) $tankCapacity;
        return isset(self::$calibrationTables[$capacityKey]);
    }

    /**
     * Liste toutes les capacités disponibles
     */
    public static function listAvailableCapacities()
    {
        return array_keys(self::$calibrationTables);
    }

    /**
     * Obtenir les détails d'une table
     */
    public static function getTableDetails($capacity)
    {
        $table = self::getCalibrationTable($capacity);
        if (!$table) {
            return null;
        }
        
        $heights = array_keys($table);
        $volumes = array_values($table);
        
        return [
            'capacity' => $capacity,
            'min_height_mm' => min($heights),
            'max_height_mm' => max($heights),
            'min_height_cm' => min($heights) / 10,
            'max_height_cm' => max($heights) / 10,
            'min_volume' => min($volumes),
            'max_volume' => max($volumes),
            'entries_count' => count($table),
            'standard_table' => in_array((string)$capacity, ['5000', '10000', '15000', '20000'])
        ];
    }

    /**
     * Calculer le facteur de conversion
     */
    public static function calculateConversionFactor($tankCapacity, $heightCm)
    {
        $heightMm = $heightCm * 10;
        $volume = self::getVolumeFromHeight($tankCapacity, $heightMm);
        
        if ($heightCm > 0 && $volume > 0) {
            return $volume / $heightCm;
        }
        return 0;
    }

    /**
     * Vérifie la validité d'une mesure
     */
    public static function validateMeasurement($tankCapacity, $heightCm, $fuelType)
    {
        $heightMm = $heightCm * 10;
        $table = self::getCalibrationTable($tankCapacity);
        
        if (!$table) {
            return [
                'valid' => true,
                'message' => 'Utilisation de formule générique',
                'warning' => 'Table de calibration non disponible'
            ];
        }
        
        $heights = array_keys($table);
        $minHeight = min($heights);
        $maxHeight = max($heights);
        
        if ($heightMm < $minHeight) {
            return [
                'valid' => false,
                'message' => "Hauteur trop basse. Minimum: " . ($minHeight / 10) . " cm"
            ];
        }
        
        if ($heightMm > $maxHeight) {
            return [
                'valid' => false,
                'message' => "Hauteur trop élevée. Maximum: " . ($maxHeight / 10) . " cm"
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Mesure dans la plage valide',
            'range' => [
                'min_cm' => $minHeight / 10,
                'max_cm' => $maxHeight / 10,
                'min_mm' => $minHeight,
                'max_mm' => $maxHeight
            ]
        ];
    }

    /**
     * Génère un rapport de calibration
     */
    public static function generateCalibrationReport($tankCapacity)
    {
        $table = self::getCalibrationTable($tankCapacity);
        
        if (!$table) {
            return [
                'available' => false,
                'message' => "Pas de table de calibration pour $tankCapacity L"
            ];
        }
        
        $details = self::getTableDetails($tankCapacity);
        
        $sample = [];
        $i = 0;
        foreach ($table as $height => $volume) {
            if ($i++ >= 10) break;
            $sample[($height / 10) . ' cm'] = $volume . ' L';
        }
        
        return [
            'available' => true,
            'capacity' => $tankCapacity,
            'details' => $details,
            'sample_entries' => $sample,
            'total_entries' => count($table),
            'standard_capacity' => in_array((string)$tankCapacity, ['5000', '10000', '15000', '20000'])
        ];
    }
}