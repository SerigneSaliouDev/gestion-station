<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Ajouter tank_id s'il n'existe pas
            if (!Schema::hasColumn('stock_movements', 'tank_id')) {
                $table->foreignId('tank_id')->nullable()->after('station_id')->constrained('tanks')->onDelete('cascade');
            }
            
            // Vérifier aussi tank_number
            if (!Schema::hasColumn('stock_movements', 'tank_number')) {
                $table->string('tank_number', 50)->nullable()->after('tank_id');
            }
        });
    }

    public function down()
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['tank_id']);
            $table->dropColumn(['tank_id', 'tank_number']);
        });
    }
};