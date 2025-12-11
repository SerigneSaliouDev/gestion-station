<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // ÉTAPE 1: D'abord, rendre la colonne nullable si elle ne l'est pas
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->date('date_shift')->nullable()->change();
        });
        
        // ÉTAPE 2: Nettoyer les valeurs invalides étape par étape
        
        // 2a. Convertir les chaînes vides en NULL
        DB::statement("
            UPDATE shift_saisies 
            SET date_shift = NULL 
            WHERE date_shift = '' 
               OR date_shift IS NULL
        ");
        
        // 2b. Convertir les dates au format français (dd/mm/yyyy) si nécessaire
        DB::statement("
            UPDATE shift_saisies 
            SET date_shift = STR_TO_DATE(date_shift, '%d/%m/%Y')
            WHERE date_shift REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$'
        ");
        
        // 2c. Pour les dates invalides qui restent, les mettre à NULL
        DB::statement("
            UPDATE shift_saisies 
            SET date_shift = NULL 
            WHERE date_shift IS NOT NULL 
              AND date_shift NOT REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
        ");
        
        // ÉTAPE 3: Remplir les NULL avec des dates valides
        DB::statement("
            UPDATE shift_saisies 
            SET date_shift = COALESCE(
                DATE(created_at),
                CURDATE()
            )
            WHERE date_shift IS NULL
        ");
        
        // ÉTAPE 4: Maintenant, vous pouvez mettre NOT NULL si nécessaire
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->date('date_shift')->nullable(false)->change();
        });
    }

    public function down()
    {
        // Pour rollback, rendre nullable
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->date('date_shift')->nullable()->change();
        });
    }
};