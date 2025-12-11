<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter la colonne role si elle n'existe pas
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'manager', 'chief', 'user'])->default('user')->after('email');
            }
            
            // Ajouter la colonne station_id si elle n'existe pas
            if (!Schema::hasColumn('users', 'station_id')) {
                $table->foreignId('station_id')->nullable()->after('role')->constrained('stations');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'station_id']);
        });
    }
};