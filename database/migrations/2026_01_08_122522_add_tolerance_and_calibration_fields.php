<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddToleranceAndCalibrationFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Table tanks
        Schema::table('tanks', function (Blueprint $table) {
            if (!Schema::hasColumn('tanks', 'product_category')) {
                $table->string('product_category')->nullable()->after('fuel_type');
            }
            
            if (!Schema::hasColumn('tanks', 'tolerance_threshold')) {
                $table->decimal('tolerance_threshold', 5, 3)->default(0.003)->after('product_category')
                    ->comment('Seuil de tolérance en pour-mille (3‰ ou 5‰)');
            }
            
            if (!Schema::hasColumn('tanks', 'calibration_table')) {
                $table->json('calibration_table')->nullable()->after('capacity')
                    ->comment('Table de jaugeage spécifique (volume => hauteur)');
            }
            
            if (!Schema::hasColumn('tanks', 'calibration_date')) {
                $table->date('calibration_date')->nullable()->after('calibration_table');
            }
            
            if (!Schema::hasColumn('tanks', 'calibration_certificate')) {
                $table->string('calibration_certificate')->nullable()->after('calibration_date');
            }
            
            if (!Schema::hasColumn('tanks', 'manufacturer')) {
                $table->string('manufacturer')->nullable()->after('calibration_certificate');
            }
            
            if (!Schema::hasColumn('tanks', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('manufacturer');
            }
            
            if (!Schema::hasColumn('tanks', 'diameter_cm')) {
                $table->decimal('diameter_cm', 8, 2)->nullable()->after('serial_number')
                    ->comment('Diamètre de la cuve en cm');
            }
            
            if (!Schema::hasColumn('tanks', 'length_cm')) {
                $table->decimal('length_cm', 8, 2)->nullable()->after('diameter_cm')
                    ->comment('Longueur de la cuve en cm (pour cuves horizontales)');
            }
            
            if (!Schema::hasColumn('tanks', 'min_safe_level')) {
                $table->decimal('min_safe_level', 8, 2)->nullable()->after('length_cm')
                    ->comment('Niveau minimal de sécurité en cm');
            }
            
            if (!Schema::hasColumn('tanks', 'max_safe_level')) {
                $table->decimal('max_safe_level', 8, 2)->nullable()->after('min_safe_level')
                    ->comment('Niveau maximal de sécurité en cm');
            }
            
            if (!Schema::hasColumn('tanks', 'last_measurement_date')) {
                $table->dateTime('last_measurement_date')->nullable()->after('max_safe_level');
            }
        });

        // Table tank_levels
        Schema::table('tank_levels', function (Blueprint $table) {
            if (!Schema::hasColumn('tank_levels', 'is_acceptable')) {
                $table->boolean('is_acceptable')->default(true)->after('difference_percentage')
                    ->comment('Si l\'écart est dans les normes de tolérance');
            }
            
            if (!Schema::hasColumn('tank_levels', 'temperature_corrected')) {
                $table->boolean('temperature_corrected')->default(false)->after('is_acceptable')
                    ->comment('Si une correction de température a été appliquée');
            }
            
            if (!Schema::hasColumn('tank_levels', 'correction_factor')) {
                $table->decimal('correction_factor', 8, 5)->nullable()->after('temperature_corrected')
                    ->comment('Facteur de correction de température appliqué');
            }
            
            if (!Schema::hasColumn('tank_levels', 'uncorrected_volume')) {
                $table->decimal('uncorrected_volume', 12, 2)->nullable()->after('correction_factor')
                    ->comment('Volume avant correction de température');
            }
            
            if (!Schema::hasColumn('tank_levels', 'tolerance_threshold')) {
                $table->decimal('tolerance_threshold', 5, 3)->nullable()->after('uncorrected_volume')
                    ->comment('Seuil de tolérance applicable lors de la mesure');
            }
            
            if (!Schema::hasColumn('tank_levels', 'product_category')) {
                $table->string('product_category')->nullable()->after('tolerance_threshold')
                    ->comment('black ou white');
            }
            
            // Index pour améliorer les performances
            $table->index(['tank_number', 'measurement_date']);
            $table->index(['is_acceptable', 'measurement_date']);
            $table->index('difference_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Table tanks
        Schema::table('tanks', function (Blueprint $table) {
            $table->dropColumn([
                'product_category',
                'tolerance_threshold',
                'calibration_table',
                'calibration_date',
                'calibration_certificate',
                'manufacturer',
                'serial_number',
                'diameter_cm',
                'length_cm',
                'min_safe_level',
                'max_safe_level',
                'last_measurement_date'
            ]);
        });

        // Table tank_levels
        Schema::table('tank_levels', function (Blueprint $table) {
            $table->dropColumn([
                'is_acceptable',
                'temperature_corrected',
                'correction_factor',
                'uncorrected_volume',
                'tolerance_threshold',
                'product_category'
            ]);
            
            // Supprimer les index ajoutés
            $table->dropIndex(['tank_number', 'measurement_date']);
            $table->dropIndex(['is_acceptable', 'measurement_date']);
            $table->dropIndex(['difference_percentage']);
        });
    }
}