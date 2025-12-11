<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Si la table n'existe pas, la créer
        if (!Schema::hasTable('stations')) {
            Schema::create('stations', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('code')->unique();
                $table->text('adresse')->nullable();
                $table->string('ville')->nullable();
                $table->string('telephone')->nullable();
                $table->foreignId('responsable_id')->nullable()->constrained('users');
                $table->enum('statut', ['actif', 'inactif'])->default('actif');
                $table->timestamps();
            });
            
            echo "✅ Table 'stations' créée.\n";
        } else {
            // Ajouter les colonnes manquantes à la table existante
            Schema::table('stations', function (Blueprint $table) {
                $columnsToAdd = [
                    'nom' => ['type' => 'string', 'after' => 'id'],
                    'code' => ['type' => 'string', 'unique' => true, 'after' => 'nom'],
                    'adresse' => ['type' => 'text', 'nullable' => true, 'after' => 'code'],
                    'ville' => ['type' => 'string', 'nullable' => true, 'after' => 'adresse'],
                    'telephone' => ['type' => 'string', 'nullable' => true, 'after' => 'ville'],
                    'statut' => ['type' => 'enum', 'values' => ['actif', 'inactif'], 'default' => 'actif'],
                ];
                
                foreach ($columnsToAdd as $columnName => $columnSpecs) {
                    if (!Schema::hasColumn('stations', $columnName)) {
                        if ($columnName === 'nom') {
                            $table->string($columnName)->after('id');
                        } elseif ($columnName === 'code') {
                            $table->string($columnName)->unique()->after('nom');
                        } elseif ($columnName === 'statut') {
                            $table->enum($columnName, $columnSpecs['values'])->default($columnSpecs['default']);
                        } else {
                            $table->{$columnSpecs['type']}($columnName)->nullable();
                        }
                        echo "✅ Colonne '$columnName' ajoutée.\n";
                    }
                }
                
                // Ajouter la clé étrangère responsable_id
                if (!Schema::hasColumn('stations', 'responsable_id')) {
                    $table->foreignId('responsable_id')->nullable()->constrained('users');
                    echo "✅ Colonne 'responsable_id' ajoutée.\n";
                }
            });
            
            echo "✅ Table 'stations' mise à jour.\n";
        }
    }

    public function down()
    {
        // Pas de rollback pour ne pas perdre de données
    }
};