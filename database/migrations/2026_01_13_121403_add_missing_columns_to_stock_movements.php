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
    Schema::table('stock_movements', function (Blueprint $table) {
        $table->string('driver_name')->nullable()->after('supplier_name');
        $table->decimal('temperature_c', 5, 2)->nullable()->after('notes');
        $table->string('delivery_note_number')->nullable()->after('invoice_number');
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['driver_name', 'temperature_c', 'delivery_note_number']);
        });
    }
};
