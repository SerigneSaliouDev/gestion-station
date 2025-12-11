<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Vérifier d'abord si la table existe
        if (Schema::hasTable('stations')) {
            Schema::table('stations', function (Blueprint $table) {
                // Ajouter les colonnes manquantes
                if (!Schema::hasColumn('stations', 'nom')) {
                    $table->string('nom')->after('id');
                }
                
                if (!Schema::hasColumn('stations', 'code')) {
                    $table->string('code')->unique()->after('nom');
                }
                
                if (!Schema::hasColumn('stations', 'adresse')) {
                    $table->text('adresse')->nullable()->after('code');
                }
                
                if (!Schema::hasColumn('stations', 'ville')) {
                    $table->string('ville')->nullable()->after('adresse');
                }
                
                if (!Schema::hasColumn('stations', 'telephone')) {
                    $table->string('telephone')->nullable()->after('ville');
                }
                
                if (!Schema::hasColumn('stations', 'responsable_id')) {
                    $table->foreignId('responsable_id')->nullable()->after('telephone')->constrained('users');
                }
                
                if (!Schema::hasColumn('stations', 'statut')) {
                    $table->enum('statut', ['actif', 'inactif'])->default('actif')->after('responsable_id');
                }
            });
        }
    }

    public function down()
    {
        // Optionnel : supprimer les colonnes ajoutées
        Schema::table('stations', function (Blueprint $table) {
            $columnsToDrop = ['nom', 'code', 'adresse', 'ville', 'telephone', 'responsable_id', 'statut'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('stations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};