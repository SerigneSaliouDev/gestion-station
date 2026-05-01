<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // ⚠️ 1. D'abord, nettoyer les données AVANT de toucher à la structure
        // Convertir les chaînes vides en NULL (en utilisant UPDATE direct)
        DB::statement("UPDATE shift_saisies SET date_shift = NULL WHERE date_shift = ''");
        
        // 2. Nettoyer les dates invalides
        DB::statement("
            UPDATE shift_saisies 
            SET date_shift = NULL 
            WHERE date_shift IS NOT NULL 
              AND date_shift NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
              AND date_shift NOT REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$'
        ");
        
        // 3. Convertir les dates au format français (dd/mm/YYYY) en format SQL (YYYY-mm-dd)
        DB::statement("
            UPDATE shift_saisies 
            SET date_shift = STR_TO_DATE(date_shift, '%d/%m/%Y')
            WHERE date_shift REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$'
        ");
        
        // 4. Remplir les dates NULL avec une valeur par défaut
        DB::table('shift_saisies')
            ->whereNull('date_shift')
            ->update(['date_shift' => '2025-01-01']);
        
        // 5. MAINTENANT, modifier la colonne (rendre nullable temporairement)
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->date('date_shift')->nullable()->change();
        });
        
        // 6. Enfin, repasser en NOT NULL
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