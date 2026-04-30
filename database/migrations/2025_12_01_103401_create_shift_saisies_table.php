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
    Schema::create('shift_saisies', function (Blueprint $table) {
        $table->id();
        $table->date('date_shift')->nullable(); 
        $table->string('shift')->nullable();
        $table->string('responsable');
        $table->decimal('total_litres', 10, 2);
        $table->decimal('total_ventes', 12, 2);
        $table->decimal('versement', 12, 2);
        $table->decimal('ecart', 12, 2);
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('shift_saisies');
    }
};
