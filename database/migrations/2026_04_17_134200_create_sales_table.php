<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->id();
                $table->foreignId('station_id')->constrained()->onDelete('cascade');
                $table->foreignId('tank_id')->nullable()->constrained()->onDelete('set null');
                $table->string('fuel_type');
                $table->string('fuel_type_display')->nullable();
                $table->decimal('quantity', 15, 2);
                $table->decimal('unit_price', 15, 2);
                $table->decimal('total_amount', 15, 2);
                $table->dateTime('sale_date');
                $table->string('payment_method')->default('cash');
                $table->string('customer_type')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('vehicle_number')->nullable();
                $table->string('tank_number')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->constrained('users');
                $table->foreignId('shift_saisie_id')->nullable()->constrained()->onDelete('set null');
                $table->timestamp('cancelled_at')->nullable();
                $table->foreignId('cancelled_by')->nullable()->constrained('users');
                $table->timestamps();
                
                $table->index(['station_id', 'sale_date']);
                $table->index(['fuel_type', 'sale_date']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
};