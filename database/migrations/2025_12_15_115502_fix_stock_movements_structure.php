<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Ajouter customer_name si elle n'existe pas
        if (!Schema::hasColumn('stock_movements', 'customer_name')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                // Placer après supplier_name si elle existe, sinon à la fin
                if (Schema::hasColumn('stock_movements', 'supplier_name')) {
                    $table->string('customer_name')->nullable()->after('supplier_name');
                } else {
                    $table->string('customer_name')->nullable();
                }
            });
        }
        
        // 2. Ajouter pump_number si elle n'existe pas (pour pouvoir placer shift_id après)
        if (!Schema::hasColumn('stock_movements', 'pump_number')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                // Placer après une colonne existante
                if (Schema::hasColumn('stock_movements', 'tank_number')) {
                    $table->string('pump_number')->nullable()->after('tank_number');
                } elseif (Schema::hasColumn('stock_movements', 'invoice_number')) {
                    $table->string('pump_number')->nullable()->after('invoice_number');
                } else {
                    $table->string('pump_number')->nullable();
                }
            });
        }
        
        // 3. Maintenant ajouter shift_id après pump_number
        if (!Schema::hasColumn('stock_movements', 'shift_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                if (Schema::hasColumn('stock_movements', 'pump_number')) {
                    $table->foreignId('shift_id')->nullable()->after('pump_number')->constrained('shift_saisies')->nullOnDelete();
                } else {
                    // Fallback: placer après tank_number ou customer_type
                    if (Schema::hasColumn('stock_movements', 'customer_type')) {
                        $table->foreignId('shift_id')->nullable()->after('customer_type')->constrained('shift_saisies')->nullOnDelete();
                    } else {
                        $table->foreignId('shift_id')->nullable()->constrained('shift_saisies')->nullOnDelete();
                    }
                }
            });
        }
        
        // 4. S'assurer que station_id existe (mais ne pas essayer de l'ajouter si elle existe déjà)
        if (!Schema::hasColumn('stock_movements', 'station_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                if (Schema::hasColumn('stock_movements', 'recorded_by')) {
                    $table->foreignId('station_id')->nullable()->after('recorded_by')->constrained('stations')->nullOnDelete();
                } else {
                    $table->foreignId('station_id')->nullable()->constrained('stations')->nullOnDelete();
                }
            });
        }
    }

    public function down()
    {
        // Supprimer les colonnes dans l'ordre inverse
        if (Schema::hasColumn('stock_movements', 'shift_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            });
        }
        
        if (Schema::hasColumn('stock_movements', 'pump_number')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropColumn('pump_number');
            });
        }
        
        if (Schema::hasColumn('stock_movements', 'customer_name')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropColumn('customer_name');
            });
        }
        
        // Note: on ne supprime pas station_id car elle est essentielle
    }
};