<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->dateTime('sale_date');
            $table->enum('fuel_type', ['super', 'gazole']);
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 15, 2);
            $table->string('pump_number')->nullable();
            $table->enum('payment_method', ['cash', 'card', 'mobile_money', 'credit']);
            $table->enum('customer_type', ['retail', 'wholesale', 'corporate'])->nullable();
            $table->string('customer_name')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->foreignId('shift_id')->nullable()->constrained('shift_saisies')->onDelete('set null');
            $table->foreignId('station_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->text('cancellation_reason')->nullable();
            $table->decimal('stock_before', 10, 2)->default(0);
            $table->decimal('stock_after', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
};