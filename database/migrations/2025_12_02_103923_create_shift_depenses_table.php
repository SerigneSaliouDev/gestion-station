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
   public function up()
{
    Schema::create('shift_depenses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('shift_saisie_id')->constrained()->onDelete('cascade');
        $table->string('type_depense'); // carburant véhicule, nourriture, maintenance, etc.
        $table->decimal('montant', 10, 2);
        $table->text('description')->nullable();
        $table->string('justificatif')->nullable(); // numéro de pièce
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
        Schema::dropIfExists('shift_depenses');
    }
};
