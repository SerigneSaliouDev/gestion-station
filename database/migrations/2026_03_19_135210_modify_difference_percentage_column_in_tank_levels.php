<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyDifferencePercentageColumnInTankLevels extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tank_levels', function (Blueprint $table) {
            // Augmenter la capacité de la colonne pour accepter de grandes valeurs
            // DECIMAL(10,2) = maximum 99,999,999.99
            $table->decimal('difference_percentage', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('tank_levels', function (Blueprint $table) {
            // Revenir à l'ancienne taille (si nécessaire)
            $table->decimal('difference_percentage', 5, 2)->change();
        });
    }
}