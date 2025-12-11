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
        Schema::table('users', function (Blueprint $table) {
            // Ajout de la colonne pour stocker la station active sélectionnée par l'utilisateur
            if (!Schema::hasColumn('users', 'active_station_id')) {
                $table->foreignId('active_station_id')
                      ->nullable() // Doit être nullable car la station est sélectionnée APRES la connexion
                      ->after('station_id') // Placé logiquement après l'affectation permanente
                      ->constrained('stations')
                      ->onDelete('set null'); // Si une station est supprimée, l'ID est mis à null
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'active_station_id')) {
                // Supprimer la clé étrangère avant de supprimer la colonne
                $table->dropConstrainedForeignId('active_station_id');
            }
        });
    }
};