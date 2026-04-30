<?php

namespace App\Traits;

use App\Models\Tank;
use App\Services\TankCalibrationService;

trait TankLevelTrait
{
    /**
     * Calcule le volume à partir de la hauteur (cm)
     * Retourne toutes les informations nécessaires pour le jaugeage
     */
    public function calculateVolumeFromHeight(Tank $tank, float $heightCm, ?float $temperature = null): array
    {
        // Conversion CM → MM pour la table de calibration
        $heightMm = $heightCm * 10;
        
        // Volume brut (sans correction température)
        $rawVolume = TankCalibrationService::getVolumeFromHeight(
            $tank->capacity,
            $heightMm
        );
        
        // Volume corrigé (avec température)
        $correctedVolume = $rawVolume;
        if ($temperature !== null) {
            $correctedVolume = $this->applyTemperatureCorrection($rawVolume, $temperature, $tank->fuel_type);
        }
        
        // Stock théorique (avant jaugeage)
        $theoreticalStock = $tank->current_volume ?? 0;
        
        // Différence (Physique - Théorique)
        $difference = $correctedVolume - $theoreticalStock;
        
        // Écart en pour mille (‰)
        $differencePerMille = $theoreticalStock > 0 
            ? ($difference / $theoreticalStock) * 1000 
            : 0;
        
        // Tolérance en pour mille (‰)
        $tolerance = TankCalibrationService::getToleranceThreshold($tank->fuel_type);
        
        // Acceptabilité
        $isAcceptable = abs($differencePerMille) <= $tolerance;
        
        // Pourcentage de remplissage
        $fillPercentage = $tank->capacity > 0 
            ? ($correctedVolume / $tank->capacity) * 100 
            : 0;
        
        return [
            'raw_volume' => round($rawVolume, 2),
            'corrected_volume' => round($correctedVolume, 2),
            'theoretical_stock' => $theoreticalStock,
            'difference' => round($difference, 2),
            'difference_per_mille' => round($differencePerMille, 2),
            'tolerance' => $tolerance,
            'is_acceptable' => $isAcceptable,
            'fill_percentage' => round($fillPercentage, 1),
            'height_cm' => $heightCm,
            'height_mm' => $heightMm,
            'temperature' => $temperature ?? 20,
        ];
    }

    /**
     * Applique la correction de température
     */
    private function applyTemperatureCorrection(float $volume, float $temperature, string $fuelType): float
    {
        $coefficient = $this->getExpansionCoefficient($fuelType);
        $correctionFactor = 1 + (($temperature - 20) * $coefficient);
        return $volume * $correctionFactor;
    }

    /**
     * Coefficient d'expansion thermique
     */
    private function getExpansionCoefficient(string $fuelType): float
    {
        $coefficients = [
            'diesel' => 0.0007,
            'gasoil' => 0.0007,
            'gazole' => 0.0007,
            'super' => 0.0012,
            'essence' => 0.0012,
            'essence piroge' => 0.0012,
            'essence pirogue' => 0.0012,
            'kerosene' => 0.0009,
        ];
        
        $normalized = strtolower(trim($fuelType));
        return $coefficients[$normalized] ?? 0.0010;
    }

    /**
     * Calcule le facteur de correction de température
     */
    public function calculateCorrectionFactor(float $temperature, string $fuelType): float
    {
        $coefficient = $this->getExpansionCoefficient($fuelType);
        return 1 + (($temperature - 20) * $coefficient);
    }

    /**
     * Valide une mesure de jaugeage
     */
    public function validateGauging(float $heightCm, float $maxHeight = 400): array
    {
        $errors = [];
        
        if ($heightCm < 0) {
            $errors[] = 'La hauteur ne peut pas être négative';
        }
        
        if ($heightCm > $maxHeight) {
            $errors[] = "La hauteur ne peut pas dépasser {$maxHeight} cm";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Formate un écart pour l'affichage
     */
    public function formatDifference(float $difference, float $perMille): string
    {
        $sign = $difference >= 0 ? '+' : '';
        return sprintf(
            '%s%s L (%s%s‰)',
            $sign,
            number_format(abs($difference), 0, ',', ' '),
            $perMille >= 0 ? '+' : '',
            number_format(abs($perMille), 1)
        );
    }

    /**
     * Obtient la classe CSS pour un écart
     */
    public function getDifferenceStatusClass(float $perMille, float $tolerance): string
    {
        $absPerMille = abs($perMille);
        
        if ($absPerMille <= $tolerance) {
            return 'success';
        }
        
        if ($absPerMille <= $tolerance * 2) {
            return 'warning';
        }
        
        return 'danger';
    }

    /**
     * Obtient la catégorie produit
     */
    public function getProductCategory(string $fuelType): string
    {
        $categories = [
            'super' => 'essence',
            'essence' => 'essence',
            'gazole' => 'gasoil',
            'gasoil' => 'gasoil',
            'diesel' => 'gasoil',
            'essence piroge' => 'essence',
            'essence pirogue' => 'essence',
            'kerosene' => 'kerosene',
        ];
        
        $normalized = strtolower(trim($fuelType));
        return $categories[$normalized] ?? 'autre';
    }

    /**
     * Construit le message de succès pour un jaugeage
     */
    public function buildGaugingSuccessMessage($tank, array $calculation, array $request): string
    {
        $message = sprintf(
            "✅ **Jaugeage enregistré - Cuve %s (%s)**\n\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
            "📏 **Hauteur mesurée** : %s cm\n" .
            "🌡️ **Température** : %s °C\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
            "📦 **Stock théorique** : %s L\n" .
            "📊 **Stock physique** : %s L\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
            "📈 **Écart** : %s\n" .
            "🎯 **Tolérance autorisée** : ±%s‰\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n",
            $tank->number,
            strtoupper($tank->fuel_type),
            number_format($request['level_cm'], 1),
            $request['temperature_c'] ?? 20,
            number_format($calculation['theoretical_stock'], 0, ',', ' '),
            number_format($calculation['corrected_volume'], 0, ',', ' '),
            $this->formatDifference($calculation['difference'], $calculation['difference_per_mille']),
            number_format($calculation['tolerance'], 1)
        );
        
        // Analyse de l'écart
        $absDifference = abs($calculation['difference']);
        $absPerMille = abs($calculation['difference_per_mille']);
        
        if ($absPerMille <= $calculation['tolerance']) {
            $message .= "✅ **Écart ACCEPTABLE** : Dans les limites de tolérance\n";
        } elseif ($calculation['difference'] > 0) {
            $message .= "⚠️ **Écart POSITIF** : Plus de stock que prévu (+" . number_format($absDifference, 0, ',', ' ') . " L)\n";
            $message .= "   → Causes possibles :\n";
            $message .= "      • Ventes non enregistrées\n";
            $message .= "      • Erreur de saisie des ventes\n";
            $message .= "      • Problème de calibration\n";
        } elseif ($calculation['difference'] < 0) {
            $message .= "⚠️ **Écart NÉGATIF** : Moins de stock que prévu (-" . number_format($absDifference, 0, ',', ' ') . " L)\n";
            $message .= "   → Causes possibles :\n";
            $message .= "      • Fuites\n";
            $message .= "      • Vols ou pertes\n";
            $message .= "      • Erreur de mesure\n";
            $message .= "      • Ventes non comptabilisées\n";
        }
        
        if (abs($calculation['difference_per_mille']) > $calculation['tolerance']) {
            $message .= sprintf(
                "\n❌ **HORS TOLÉRANCE** : Écart de %s‰ > tolérance de %s‰\n" .
                "   → **ACTION REQUISE** : Enquête sur la cause de l'écart\n",
                number_format(abs($calculation['difference_per_mille']), 1),
                number_format($calculation['tolerance'], 1)
            );
        }
        
        $message .= sprintf(
            "\n🔄 **Mise à jour** : Stock théorique mis à %s L\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
            number_format($calculation['corrected_volume'], 0, ',', ' ')
        );
        
        return $message;
    }
    
    /**
 * Construit les notes de correction
 */
public function buildCorrectionNotes($request, $calculation): ?string
{
    $notes = [];
    
    if ($request->filled('temperature_c')) {
        $notes[] = sprintf(
            'Correction température: %s°C (facteur: %s)',
            $request->temperature_c,
            number_format($this->calculateCorrectionFactor($request->temperature_c, $calculation['fuel_type'] ?? 'super'), 4)
        );
    }
    
    if ($calculation['difference'] != 0) {
        $notes[] = sprintf(
            'Écart: %s L (%s‰)',
            $calculation['difference'] > 0 ? '+' . number_format($calculation['difference'], 0) : number_format($calculation['difference'], 0),
            $calculation['difference_per_mille'] > 0 ? '+' . number_format($calculation['difference_per_mille'], 1) : number_format($calculation['difference_per_mille'], 1)
        );
        
        if (!$calculation['is_acceptable']) {
            $notes[] = sprintf(
                '⚠️ Écart hors tolérance (tolérance: %s‰)',
                number_format($calculation['tolerance'], 1)
            );
        }
    }
    
    return !empty($notes) ? implode(' | ', $notes) : null;
}

    /**
     * Construit le message d'erreur pour un écart hors tolérance
     */
    public function buildToleranceErrorMessage($tank, array $calculation): string
    {
        $direction = $calculation['difference'] > 0 ? 'excédent' : 'manquant';
        $color = $calculation['difference'] > 0 ? 'orange' : 'red';
        
        return sprintf(
            "⚠️ **Écart %s détecté sur la cuve %s**\n\n" .
            "• Écart : %s\n" .
            "• Tolérance max : ±%s‰\n" .
            "• Écart constaté : %s%s‰\n\n" .
            "Cette variation anormale nécessite une investigation.",
            $direction,
            $tank->number,
            $this->formatDifference($calculation['difference'], $calculation['difference_per_mille']),
            number_format($calculation['tolerance'], 1),
            $calculation['difference_per_mille'] >= 0 ? '+' : '',
            number_format(abs($calculation['difference_per_mille']), 1)
        );
    }
}