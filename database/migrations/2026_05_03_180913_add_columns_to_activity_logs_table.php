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
        Schema::table('activity_logs', function (Blueprint $table) {
            // Ajoute la colonne 'action' après 'user_id'
            // On la met en nullable au cas où des anciens logs existent déjà
            if (!Schema::hasColumn('activity_logs', 'action')) {
                $table->string('action')->after('user_id')->nullable();
            }
            
            // Vérification et ajout des autres colonnes potentiellement manquantes 
            // basées sur votre code (description, details)
            if (!Schema::hasColumn('activity_logs', 'description')) {
                $table->text('description')->after('action')->nullable();
            }
            
            if (!Schema::hasColumn('activity_logs', 'details')) {
                $table->json('details')->after('description')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['action', 'description', 'details']);
        });
    }
};