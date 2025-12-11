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
// database/migrations/2025_12_01_103446_create_shift_pompe_details_table.php
public function up()
{
    Schema::create('shift_pompe_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('shift_saisie_id')->constrained()->onDelete('cascade');
        $table->string('pompe_nom');
        $table->string('carburant');
        $table->decimal('prix_unitaire', 10, 2);
        $table->decimal('index_ouverture', 10, 2);
        $table->decimal('index_fermeture', 10, 2);
        $table->decimal('retour_litres', 10, 2);
        $table->decimal('litrage_vendu', 10, 2);
        $table->decimal('montant_ventes', 10, 2);
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift_pompe_details');
    }
};
