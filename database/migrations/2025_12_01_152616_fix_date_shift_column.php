<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // D'abord, vérifiez et nettoyez les données
        DB::table('shift_saisies')
            ->whereNotNull('date_shift')
            ->where('date_shift', '!=', '')
            ->update([
                'date_shift' => DB::raw("CASE 
                    WHEN date_shift REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN date_shift
                    WHEN date_shift REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' THEN STR_TO_DATE(date_shift, '%d/%m/%Y')
                    ELSE NULL 
                END")
            ]);

        // Ensuite, modifiez la colonne
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->date('date_shift')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('shift_saisies', function (Blueprint $table) {
            // Si vous aviez une colonne string avant
            $table->string('date_shift', 20)->nullable()->change();
        });
    }
};