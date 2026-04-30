<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
// Dans la migration
public function up()
{
    Schema::table('stock_movements', function (Blueprint $table) {
        // Empêche les doublons : un seul mouvement par shift + type de carburant
        $table->unique(['shift_saisie_id', 'fuel_type'], 'unique_shift_fuel_movement');
    });
}

public function down()
{
    Schema::table('stock_movements', function (Blueprint $table) {
        $table->dropUnique('unique_shift_fuel_movement');
    });
}
};
