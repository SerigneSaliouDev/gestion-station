<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'shift_saisie_id')) {
                $table->unsignedBigInteger('shift_saisie_id')->nullable()->after('shift_id');
            }
            if (!Schema::hasColumn('sales', 'fuel_type_display')) {
                $table->string('fuel_type_display')->nullable()->after('fuel_type');
            }
            if (!Schema::hasColumn('sales', 'tank_number')) {
                $table->string('tank_number')->nullable()->after('tank_id');
            }
            if (!Schema::hasColumn('sales', 'source')) {
                $table->string('source')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['shift_saisie_id', 'fuel_type_display', 'tank_number', 'source']);
        });
    }
};