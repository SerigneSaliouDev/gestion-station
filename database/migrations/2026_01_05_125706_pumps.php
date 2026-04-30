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
        Schema::create('pumps', function (Blueprint $table) {
        $table->id();
        $table->foreignId('station_id')->constrained()->onDelete('cascade');
        $table->string('pump_number');
        $table->enum('fuel_type', ['super', 'gasoil']);
        $table->integer('nozzle_count')->default(2);
        $table->boolean('is_active')->default(true);
        $table->text('notes')->nullable();
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
        Schema::dropIfExists('pumps');
    }
};
