<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTankIdToSalesTable extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Ajouter tank_id (clé étrangère vers tanks)
            $table->foreignId('tank_id')->nullable()->after('station_id')
                  ->constrained('tanks')->onDelete('set null');
            
            // Ajouter tank_number pour référence rapide
            $table->string('tank_number')->nullable()->after('tank_id');
            
            // Index pour optimiser les recherches
            $table->index(['tank_id', 'sale_date']);
            $table->index(['fuel_type', 'sale_date']);
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['tank_id']);
            $table->dropIndex(['tank_id', 'sale_date']);
            $table->dropIndex(['fuel_type', 'sale_date']);
            $table->dropColumn(['tank_id', 'tank_number']);
        });
    }
}