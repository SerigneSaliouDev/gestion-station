<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManagerIdToStationsTable extends Migration
{
    public function up()
    {
        Schema::table('stations', function (Blueprint $table) {
            // Ajouter la colonne manager_id (nullable car une station peut ne pas avoir de manager)
            $table->foreignId('manager_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('users')
                  ->nullOnDelete(); // Si le manager est supprimé, manager_id devient NULL
        });
    }

    public function down()
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }
}