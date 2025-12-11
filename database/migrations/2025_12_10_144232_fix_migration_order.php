<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Créer la table stations si elle n'existe pas
        if (!Schema::hasTable('stations')) {
            Schema::create('stations', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('nom');
                $table->string('ville');
                $table->string('quartier')->nullable();
                $table->string('adresse')->nullable();
                $table->string('telephone')->nullable();
                $table->string('email')->nullable();
                $table->string('responsable')->nullable();
                $table->enum('statut', ['active', 'inactive', 'maintenance'])->default('active');
                $table->integer('nombre_pompes')->default(0);
                $table->decimal('capacite_total', 10, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Créer la table sales si elle n'existe pas
        if (!Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->id();
                $table->dateTime('sale_date');
                $table->enum('fuel_type', ['super', 'gazole']);
                $table->decimal('quantity', 10, 2);
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_amount', 15, 2);
                $table->string('pump_number')->nullable();
                $table->enum('payment_method', ['cash', 'card', 'mobile_money', 'credit']);
                $table->enum('customer_type', ['retail', 'wholesale', 'corporate'])->nullable();
                $table->string('customer_name')->nullable();
                $table->string('vehicle_number')->nullable();
                $table->foreignId('shift_id')->nullable()->constrained('shift_saisies')->onDelete('set null');
                $table->foreignId('station_id')->nullable()->constrained('stations')->onDelete('set null');
                $table->foreignId('recorded_by')->constrained('users');
                $table->timestamp('cancelled_at')->nullable();
                $table->foreignId('cancelled_by')->nullable()->constrained('users');
                $table->text('cancellation_reason')->nullable();
                $table->decimal('stock_before', 10, 2)->default(0);
                $table->decimal('stock_after', 10, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index('sale_date');
                $table->index('fuel_type');
                $table->index('payment_method');
            });
        }

        // Ajouter les colonnes manquantes à shift_saisies
        if (Schema::hasTable('shift_saisies')) {
            if (!Schema::hasColumn('shift_saisies', 'stock_synced_at')) {
                Schema::table('shift_saisies', function (Blueprint $table) {
                    $table->timestamp('stock_synced_at')->nullable()->after('ecart_final');
                });
            }
            
            if (!Schema::hasColumn('shift_saisies', 'stock_sync_notes')) {
                Schema::table('shift_saisies', function (Blueprint $table) {
                    $table->text('stock_sync_notes')->nullable()->after('stock_synced_at');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On ne supprime rien dans le down() pour éviter de casser la base
    }
};