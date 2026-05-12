<?php

namespace App\Traits;

trait FuelTypeTrait
{
    /**
     * Normaliser le type de carburant
     */
    public function normalizeFuelType(string $fuelType): string
    {
        $fuelType = strtolower(trim($fuelType));

        $mappings = [
            // Gasoil / Diesel
            'gasoil'         => 'gasoil',
            'gazole'         => 'gasoil',
            'diesel'         => 'gasoil',
            'gas oil'        => 'gasoil',
            'go'             => 'gasoil',
            'gasoïl'         => 'gasoil',

            // Super / Essence
            'super'          => 'super',
            'essence'        => 'super',
            'sp95'           => 'super',
            'sp 95'          => 'super',
            'sans plomb'     => 'super',
            'essence super'  => 'super',

            // Essence Pirogue
            'pirogue'             => 'essence_pirogue',
            'essence pirogue'     => 'essence_pirogue',
            'essence_pirogue'     => 'essence_pirogue',
            'essencepirogue'      => 'essence_pirogue',

            // DDO
            'ddo'            => 'ddo',
            'distillat'      => 'ddo',

            // Pétrole
            'petrole'        => 'petrole',
            'pétrole'        => 'petrole',
            'kerosene'       => 'petrole',
            'kérosène'       => 'petrole',
        ];

        return $mappings[$fuelType] ?? $fuelType;
    }

    /**
     * Obtenir le libellé d'affichage du type de carburant
     */
    public function getFuelTypeDisplay(string $fuelType): string
    {
        $normalized = $this->normalizeFuelType($fuelType);

        $displays = [
            'gasoil'          => 'Gasoil',
            'super'           => 'Super',
            'essence_pirogue' => 'Essence Pirogue',
            'ddo'             => 'DDO',
            'petrole'         => 'Pétrole',
        ];

        return $displays[$normalized] ?? ucfirst($fuelType);
    }

    /**
     * Obtenir la classe CSS badge pour le type de carburant
     */
    public function getFuelTypeBadgeClass(string $fuelType): string
    {
        $normalized = $this->normalizeFuelType($fuelType);

        $classes = [
            'gasoil'          => 'badge-warning',
            'super'           => 'badge-success',
            'essence_pirogue' => 'badge-info',
            'ddo'             => 'badge-secondary',
            'petrole'         => 'badge-dark',
        ];

        return $classes[$normalized] ?? 'badge-primary';
    }

    /**
     * Construire la requête de filtre par type de carburant
     */
    public function buildFuelTypeQuery($query, string $column, string $fuelType)
    {
        $normalized = $this->normalizeFuelType($fuelType);

        // Récupérer tous les alias possibles
        $aliases = $this->getFuelTypeAliases($normalized);

        return $query->where(function ($q) use ($column, $aliases) {
            foreach ($aliases as $alias) {
                $q->orWhere($column, 'LIKE', '%' . $alias . '%');
            }
        });
    }

    /**
     * Obtenir tous les alias d'un type de carburant normalisé
     */
    public function getFuelTypeAliases(string $normalizedType): array
    {
        $aliases = [
            'gasoil' => ['gasoil', 'gazole', 'diesel', 'go'],
            'super'  => ['super', 'essence', 'sp95', 'sans plomb'],
            'essence_pirogue' => ['pirogue', 'essence pirogue', 'essence_pirogue'],
            'ddo'    => ['ddo', 'distillat'],
            'petrole' => ['petrole', 'pétrole', 'kerosene', 'kérosène'],
        ];

        return $aliases[$normalizedType] ?? [$normalizedType];
    }

    /**
     * Obtenir la couleur hex du type de carburant
     */
    public function getFuelTypeColor(string $fuelType): string
    {
        $normalized = $this->normalizeFuelType($fuelType);

        $colors = [
            'gasoil'          => '#f59e0b',
            'super'           => '#10b981',
            'essence_pirogue' => '#3b82f6',
            'ddo'             => '#6b7280',
            'petrole'         => '#1f2937',
        ];

        return $colors[$normalized] ?? '#6366f1';
    }
}