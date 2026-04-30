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
    Schema::table('stations', function (Blueprint $table) {
        $table->decimal('capacite_super', 10, 2)->default(0);
        $table->decimal('capacite_gazole', 10, 2)->default(0);
        $table->decimal('capacite_essence_pirogue', 10, 2)->default(0);
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stations', function (Blueprint $table) {
            //
        });
    }
};
