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
        Schema::create('price_limits', function (Blueprint $table) {
        $table->id();
        $table->enum('fuel_type', ['super', 'gasoil']);
        $table->decimal('min_price', 10, 2);
        $table->decimal('max_price', 10, 2);
        $table->date('effective_from');
        $table->date('effective_to')->nullable();
        $table->foreignId('created_by')->constrained('users');
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
        Schema::dropIfExists('price_limits');
    }
};
