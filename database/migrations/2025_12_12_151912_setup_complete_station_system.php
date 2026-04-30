<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Créer la table stations si elle n'existe pas
        if (!Schema::hasTable('stations')) {
            Schema::create('stations', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('nom');
                $table->string('ville');
                $table->string('adresse')->nullable();
                $table->string('telephone')->nullable();
                $table->string('email')->nullable();
                $table->string('responsable')->nullable();
                $table->boolean('actif')->default(true);
                $table->timestamps();
            });
            
            // NE PAS insérer de station par défaut - vous en avez déjà une
            // L'insertion se fera via votre système existant ou seeds
        }
        
        // 2. Ajouter station_id à shift_saisies
        Schema::table('shift_saisies', function (Blueprint $table) {
            if (!Schema::hasColumn('shift_saisies', 'station_id')) {
                $table->foreignId('station_id')->after('user_id')->nullable();
                
                if (Schema::hasTable('stations')) {
                    $table->foreign('station_id')->references('id')->on('stations')->onDelete('set null');
                }
            }
            
            // Ajouter les autres champs si nécessaire
            if (!Schema::hasColumn('shift_saisies', 'statut')) {
                $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            }
            
            if (!Schema::hasColumn('shift_saisies', 'validated_by')) {
                $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('shift_saisies', 'validation_date')) {
                $table->timestamp('validation_date')->nullable();
            }
            
            if (!Schema::hasColumn('shift_saisies', 'notes_validation')) {
                $table->text('notes_validation')->nullable();
            }
            
            if (!Schema::hasColumn('shift_saisies', 'converted_to_stock')) {
                $table->boolean('converted_to_stock')->default(false);
            }
            
            if (!Schema::hasColumn('shift_saisies', 'converted_at')) {
                $table->timestamp('converted_at')->nullable();
            }
        });
        
        // 3. Ajouter station_id à stock_movements
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_movements', 'station_id')) {
                $table->foreignId('station_id')->after('recorded_by')->nullable();
                
                if (Schema::hasTable('stations')) {
                    $table->foreign('station_id')->references('id')->on('stations')->onDelete('set null');
                }
            }
            
            // Ajouter d'autres champs si nécessaire
            if (!Schema::hasColumn('stock_movements', 'shift_saisie_id')) {
                $table->foreignId('shift_saisie_id')->nullable()->constrained('shift_saisies')->onDelete('cascade');
            }
            
            if (!Schema::hasColumn('stock_movements', 'auto_generated')) {
                $table->boolean('auto_generated')->default(false);
            }
        });
        
        // 4. Ajouter station_id aux users (si nécessaire)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'station_id')) {
                $table->foreignId('station_id')->after('remember_token')->nullable();
                
                if (Schema::hasTable('stations')) {
                    $table->foreign('station_id')->references('id')->on('stations')->onDelete('set null');
                }
            }
        });
    }

    public function down()
    {
        // Supprimer les contraintes et colonnes ajoutées
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'station_id')) {
                $table->dropForeign(['station_id']);
                $table->dropColumn('station_id');
            }
        });
        
        Schema::table('stock_movements', function (Blueprint $table) {
            $columnsToDrop = ['station_id', 'shift_saisie_id', 'auto_generated'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('stock_movements', $column)) {
                    if ($column === 'station_id' || $column === 'shift_saisie_id') {
                        $table->dropForeign([$column]);
                    }
                    $table->dropColumn($column);
                }
            }
        });
        
        Schema::table('shift_saisies', function (Blueprint $table) {
            $columnsToDrop = [
                'station_id', 'statut', 'validated_by', 
                'validation_date', 'notes_validation',
                'converted_to_stock', 'converted_at'
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('shift_saisies', $column)) {
                    if (in_array($column, ['station_id', 'validated_by'])) {
                        $table->dropForeign([$column]);
                    }
                    $table->dropColumn($column);
                }
            }
        });
        
        // NE PAS supprimer la table stations - elle contient vos données
    }
};