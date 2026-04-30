<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToTanksTable extends Migration
{
    public function up()
    {
        Schema::table('tanks', function (Blueprint $table) {
            // Ajouter la colonne status si elle n'existe pas
            if (!Schema::hasColumn('tanks', 'status')) {
                $table->string('status')->default('active')->comment('active, maintenance, out_of_service')->after('product_category');
            }
            
            // Ajouter la colonne shape si elle n'existe pas
            if (!Schema::hasColumn('tanks', 'shape')) {
                $table->string('shape')->default('cylindrical')->comment('cylindrical, rectangular, spherical')->after('length_cm');
            }
            
            // Ajouter la colonne installation_date si elle n'existe pas
            if (!Schema::hasColumn('tanks', 'installation_date')) {
                $table->date('installation_date')->nullable()->after('serial_number');
            }
            
            // Ajouter la colonne last_maintenance_date si elle n'existe pas
            if (!Schema::hasColumn('tanks', 'last_maintenance_date')) {
                $table->date('last_maintenance_date')->nullable()->after('installation_date');
            }
        });
    }

    public function down()
    {
        Schema::table('tanks', function (Blueprint $table) {
            $table->dropColumn(['status', 'shape', 'installation_date', 'last_maintenance_date']);
        });
    }
}