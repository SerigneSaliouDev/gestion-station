<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuelPricesTable extends Migration
{
    public function up()
    {
        Schema::create('fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->string('fuel_type'); // super, gazole, premium, etc.
            $table->decimal('price', 10, 2); // Prix en FCFA
            $table->date('effective_date'); // Date d'effet
            $table->boolean('is_active')->default(true);
            $table->foreignId('station_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Index pour les recherches
            $table->index(['fuel_type', 'station_id']);
            $table->index(['effective_date', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('fuel_prices');
    }
}