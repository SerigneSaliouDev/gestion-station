<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Vérifiez si la colonne existe déjà avant de l'ajouter
        if (!Schema::hasColumn('stock_movements', 'station_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreignId('station_id')->nullable()->after('recorded_by')->constrained('stations')->nullOnDelete();
            });
        }
        
        // Si vous avez des colonnes enum, convertissez-les en string
        $this->convertEnumColumns();
    }

    public function down()
    {
        if (Schema::hasColumn('stock_movements', 'station_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropForeign(['station_id']);
                $table->dropColumn('station_id');
            });
        }
    }
    
    /**
     * Convertir les colonnes enum en string si nécessaire
     */
    private function convertEnumColumns()
    {
        // Liste des colonnes enum potentielles
        $enumColumns = ['movement_type', 'fuel_type', 'customer_type', 'payment_method'];
        
        foreach ($enumColumns as $column) {
            if (Schema::hasColumn('stock_movements', $column)) {
                Schema::table('stock_movements', function (Blueprint $table) use ($column) {
                    // Modifier la colonne en string si c'est un enum
                    $table->string($column)->nullable()->change();
                });
            }
        }
    }
};