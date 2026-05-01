<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Vérifier d'abord si la colonne existe et est déjà au bon format
        if (Schema::hasColumn('shift_saisies', 'date_shift')) {
            // Rendre nullable temporairement
            Schema::table('shift_saisies', function (Blueprint $table) {
                $table->date('date_shift')->nullable()->change();
            });
            
            // Repasser en NOT NULL
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