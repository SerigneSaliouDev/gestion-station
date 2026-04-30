<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixTankLevelsColumns extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tank_levels', function (Blueprint $table) {
            // 1. Ajouter un index composite pour les recherches fréquentes
            $table->index(['station_id', 'measurement_date'], 'idx_station_date');
            $table->index(['tank_number', 'measurement_date'], 'idx_tank_date');
            
            // 2. Ajouter une colonne pour suivre la méthode de calcul utilisée
            if (!Schema::hasColumn('tank_levels', 'calculation_method')) {
                $table->string('calculation_method', 50)->nullable()
                    ->after('uncorrected_volume')
                    ->comment('table_calibration ou generic_formula');
            }
            
            // 3. Ajouter une colonne pour les notes de correction
            if (!Schema::hasColumn('tank_levels', 'correction_notes')) {
                $table->text('correction_notes')->nullable()
                    ->after('observations')
                    ->comment('Notes sur les corrections appliquées');
            }
        });
        
        // 4. Nettoyer les données existantes qui pourraient être incohérentes
        DB::table('tank_levels')
            ->where('difference_percentage', '>', 10000) // Valeurs trop élevées
            ->update(['difference_percentage' => 0, 'is_acceptable' => 0]);
        
        // 5. Recalculer is_acceptable pour les anciennes données si nécessaire
        DB::table('tank_levels')
            ->where('tolerance_threshold', '>', 0)
            ->update([
                'is_acceptable' => DB::raw('ABS(difference_percentage) <= tolerance_threshold')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('tank_levels', function (Blueprint $table) {
            $table->dropIndex('idx_station_date');
            $table->dropIndex('idx_tank_date');
            
            if (Schema::hasColumn('tank_levels', 'calculation_method')) {
                $table->dropColumn('calculation_method');
            }
            
            if (Schema::hasColumn('tank_levels', 'correction_notes')) {
                $table->dropColumn('correction_notes');
            }
        });
    }
}