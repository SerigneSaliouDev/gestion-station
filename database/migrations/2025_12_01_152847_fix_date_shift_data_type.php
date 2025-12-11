<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // ÉTAPE 1: Rendre la colonne nullable temporairement
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->date('date_shift')->nullable()->change();
        });
        
        // ÉTAPE 2: Nettoyer les données invalides
        DB::statement("
            UPDATE shift_saisies 
            SET date_shift = NULL 
            WHERE date_shift = '' 
               OR date_shift IS NULL 
               OR date_shift NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
        ");
        
        // ÉTAPE 3: Convertir les dates au format français si nécessaire
        DB::statement("
            UPDATE shift_saisies 
            SET date_shift = STR_TO_DATE(date_shift, '%d/%m/%Y')
            WHERE date_shift REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$'
        ");
        
        // ÉTAPE 4: Remplir les dates NULL avec une date par défaut
        DB::table('shift_saisies')
            ->whereNull('date_shift')
            ->update(['date_shift' => '2025-01-01']); // Date par défaut
        
        // ÉTAPE 5: Maintenant vous pouvez mettre NOT NULL
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->date('date_shift')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->date('date_shift')->nullable()->change();
        });
    }
};