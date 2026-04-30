<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToTankLevelsTable extends Migration
{
    public function up()
    {
        Schema::table('tank_levels', function (Blueprint $table) {
            if (!Schema::hasColumn('tank_levels', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down()
    {
        Schema::table('tank_levels', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}