<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTanksTable extends Migration
{
    public function up()
    {
        Schema::create('tanks', function (Blueprint $table) {
            $table->id();
            $table->string('number')->comment('Numéro de la cuve (ex: C1, C2)');
            $table->string('description')->nullable()->comment('Description de la cuve');
            $table->string('fuel_type')->comment('Type de carburant: diesel, gasoil, essence, super, etc.');
            $table->integer('capacity')->comment('Capacité nominale en litres');
            $table->decimal('current_level_cm', 8, 2)->default(0)->comment('Niveau actuel en cm');
            $table->decimal('current_volume', 12, 2)->default(0)->comment('Volume actuel en litres');
            $table->json('calibration_table')->nullable()->comment('Table de jaugeage spécifique');
            $table->decimal('tolerance_threshold', 5, 3)->default(0.003)->comment('Seuil de tolérance (3‰ ou 5‰)');
            $table->string('product_category')->nullable()->comment('black, white, other');
            
            // Dimensions
            $table->decimal('diameter_cm', 8, 2)->nullable()->comment('Diamètre en cm');
            $table->decimal('length_cm', 8, 2)->nullable()->comment('Longueur en cm (cuves horizontales)');
            $table->string('shape')->default('cylindrical')->comment('cylindrical, rectangular, spherical');
            
            // Niveaux de sécurité
            $table->decimal('min_safe_level', 8, 2)->nullable()->comment('Niveau minimal de sécurité en cm');
            $table->decimal('max_safe_level', 8, 2)->nullable()->comment('Niveau maximal de sécurité en cm');
            
            // Informations de calibration
            $table->date('calibration_date')->nullable();
            $table->string('calibration_certificate')->nullable();
            
            // Informations fabricant
            $table->string('manufacturer')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('installation_date')->nullable();
            $table->date('last_maintenance_date')->nullable();
            
            // Statut
            $table->string('status')->default('active')->comment('active, maintenance, out_of_service');
            
            // Relation station
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index(['station_id', 'fuel_type']);
            $table->index(['station_id', 'number']);
            $table->unique(['station_id', 'number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tanks');
    }
}