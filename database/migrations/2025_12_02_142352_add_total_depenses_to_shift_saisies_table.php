<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalDepensesToShiftSaisiesTable extends Migration
{
    public function up()
    {
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->decimal('total_depenses', 12, 2)->default(0)->after('versement');
            $table->decimal('ecart_final', 12, 2)->default(0)->after('ecart');
        });
    }

    public function down()
    {
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->dropColumn(['total_depenses', 'ecart_final']);
        });
    }
}