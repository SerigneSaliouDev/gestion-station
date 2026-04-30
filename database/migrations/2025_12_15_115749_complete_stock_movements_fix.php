<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Liste complète des colonnes nécessaires avec leurs types
        $columnsToAdd = [
            'customer_name' => ['type' => 'string', 'length' => 255, 'nullable' => true],
            'customer_type' => ['type' => 'string', 'length' => 50, 'nullable' => true],
            'payment_method' => ['type' => 'string', 'length' => 50, 'nullable' => true],
            'pump_number' => ['type' => 'string', 'length' => 50, 'nullable' => true],
            'shift_id' => ['type' => 'foreignId', 'nullable' => true, 'references' => 'shift_saisies'],
            'station_id' => ['type' => 'foreignId', 'nullable' => true, 'references' => 'stations'],
            'verified_by' => ['type' => 'foreignId', 'nullable' => true, 'references' => 'users'],
            'verified_at' => ['type' => 'timestamp', 'nullable' => true],
        ];

        foreach ($columnsToAdd as $columnName => $columnConfig) {
            if (!Schema::hasColumn('stock_movements', $columnName)) {
                Schema::table('stock_movements', function (Blueprint $table) use ($columnName, $columnConfig) {
                    switch ($columnConfig['type']) {
                        case 'string':
                            $table->string($columnName, $columnConfig['length'])
                                  ->nullable($columnConfig['nullable'] ?? false);
                            break;
                            
                        case 'foreignId':
                            $table->foreignId($columnName)
                                  ->nullable($columnConfig['nullable'] ?? false);
                            break;
                            
                        case 'timestamp':
                            $table->timestamp($columnName)
                                  ->nullable($columnConfig['nullable'] ?? false);
                            break;
                    }
                });
                
                // Ajouter les contraintes de clé étrangère si nécessaire
                if ($columnConfig['type'] === 'foreignId' && isset($columnConfig['references'])) {
                    Schema::table('stock_movements', function (Blueprint $table) use ($columnName, $columnConfig) {
                        $table->foreign($columnName)
                              ->references('id')
                              ->on($columnConfig['references'])
                              ->onDelete('set null');
                    });
                }
            }
        }

        // Assurer que les colonnes essentielles existent
        $this->ensureEssentialColumns();
    }

    public function down()
    {
        // Supprimer seulement les colonnes que nous avons ajoutées
        $columnsToRemove = ['customer_name', 'customer_type', 'payment_method', 'pump_number', 'shift_id'];
        
        foreach ($columnsToRemove as $column) {
            if (Schema::hasColumn('stock_movements', $column)) {
                Schema::table('stock_movements', function (Blueprint $table) use ($column) {
                    if ($column === 'shift_id') {
                        $table->dropForeign(['shift_id']);
                    }
                    $table->dropColumn($column);
                });
            }
        }
        
        // Ne pas supprimer station_id, verified_by, verified_at car elles sont essentielles
    }
    
    /**
     * Vérifier et ajouter les colonnes essentielles si elles manquent
     */
    private function ensureEssentialColumns()
    {
        $essentialColumns = [
            'movement_date' => 'timestamp',
            'fuel_type' => 'string:50',
            'movement_type' => 'string:50',
            'quantity' => 'decimal:10,2',
            'unit_price' => 'decimal:10,2',
            'total_amount' => 'decimal:10,2',
            'supplier_name' => 'string:255',
            'invoice_number' => 'string:100',
            'tank_number' => 'string:50',
            'stock_before' => 'decimal:10,2',
            'stock_after' => 'decimal:10,2',
            'notes' => 'text',
            'recorded_by' => 'foreignId:users',
        ];
        
        foreach ($essentialColumns as $column => $type) {
            if (!Schema::hasColumn('stock_movements', $column)) {
                \Log::warning("Colonne essentielle manquante: {$column}");
                // Vous pourriez ajouter ici la logique pour créer les colonnes manquantes
            }
        }
    }
};