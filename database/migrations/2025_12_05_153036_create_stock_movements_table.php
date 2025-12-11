<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->date('movement_date'); // Date du mouvement
            $table->string('fuel_type'); // Type de carburant
            $table->enum('movement_type', ['reception', 'vente', 'ajustement', 'inventaire']); // Type de mouvement
            $table->decimal('quantity', 10, 2); // Quantité en litres (positif pour réception, négatif pour vente)
            $table->decimal('unit_price', 10, 2)->nullable(); // Prix unitaire
            $table->decimal('total_amount', 15, 2)->nullable(); // Montant total
            $table->string('supplier_name')->nullable(); // Fournisseur (pour réception)
            $table->string('invoice_number')->nullable(); // Numéro de facture
            $table->string('tank_number')->nullable(); // Numéro de cuve
            $table->decimal('stock_before', 10, 2); // Stock avant mouvement
            $table->decimal('stock_after', 10, 2); // Stock après mouvement
            $table->text('notes')->nullable(); // Notes supplémentaires
            $table->foreignId('recorded_by')->constrained('users'); // Enregistré par
            $table->foreignId('verified_by')->nullable()->constrained('users'); // Vérifié par
            $table->timestamp('verified_at')->nullable(); // Date de vérification
            $table->timestamps();
            
            $table->index('movement_date');
            $table->index('fuel_type');
            $table->index('movement_type');
        });
        
        // Table pour les niveaux de cuve
        Schema::create('tank_levels', function (Blueprint $table) {
            $table->id();
            $table->date('measurement_date'); // Date de mesure
            $table->string('tank_number'); // Numéro de cuve
            $table->string('fuel_type'); // Type de carburant
            $table->decimal('level_cm', 10, 2); // Niveau en cm
            $table->decimal('temperature_c', 10, 2)->nullable(); // Température en °C
            $table->decimal('volume_liters', 10, 2); // Volume calculé en litres
            $table->decimal('theoretical_stock', 10, 2); // Stock théorique
            $table->decimal('physical_stock', 10, 2); // Stock physique mesuré
            $table->decimal('difference', 10, 2); // Différence
            $table->decimal('difference_percentage', 5, 2); // Différence en pourcentage
            $table->text('observations')->nullable(); // Observations
            $table->foreignId('measured_by')->constrained('users'); // Mesuré par
            $table->foreignId('verified_by')->nullable()->constrained('users'); // Vérifié par
            $table->timestamps();
            
            $table->index('measurement_date');
            $table->index('tank_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tank_levels');
        Schema::dropIfExists('stock_movements');
    }
};