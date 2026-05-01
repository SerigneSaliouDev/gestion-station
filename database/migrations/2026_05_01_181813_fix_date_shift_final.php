<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Vérifier si la colonne date_shift existe
        if (Schema::hasColumn('shift_saisies', 'date_shift')) {
            
            // 1. Rendre la colonne nullable FIRST
            Schema::table('shift_saisies', function (Blueprint $table) {
                $table->date('date_shift')->nullable()->change();
            });
            
            // 2. MAINTENANT on peut mettre les chaînes vides à NULL
            DB::statement("UPDATE shift_saisies SET date_shift = NULL WHERE date_shift = ''");
            
            // 3. Mettre une date par défaut pour ceux qui sont NULL (optionnel)
            DB::statement("UPDATE shift_saisies SET date_shift = '2025-01-01' WHERE date_shift IS NULL");
            
            // 4. Repasser en NOT NULL
            Schema::table('shift_saisies', function (Blueprint $table) {
                $table->date('date_shift')->nullable(false)->change();
            });
        }
    }

    public function down()
    {
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->date('date_shift')->nullable()->change();
        });
    }
};