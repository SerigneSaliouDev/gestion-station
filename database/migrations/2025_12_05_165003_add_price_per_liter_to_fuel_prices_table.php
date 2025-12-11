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
    Schema::table('fuel_prices', function (Blueprint $table) {
        $table->decimal('price_per_liter', 8, 2)->after('fuel_type');
    });
}

public function down()
{
    Schema::table('fuel_prices', function (Blueprint $table) {
        $table->dropColumn('price_per_liter');
    });
}
};