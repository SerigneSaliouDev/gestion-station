<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            // 1. Vérifier si la contrainte existe et la supprimer
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('stations');
            $foreignKeys = $sm->listTableForeignKeys('stations');
            
            // Supprimer la contrainte de clé étrangère si elle existe
            foreach ($foreignKeys as $foreignKey) {
                if (in_array('responsable_id', $foreignKey->getColumns())) {
                    $table->dropForeign(['responsable_id']);
                    break;
                }
            }
            
            // 2. Supprimer la colonne
            if (Schema::hasColumn('stations', 'responsable_id')) {
                $table->dropColumn('responsable_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            // Recréer la colonne
            if (!Schema::hasColumn('stations', 'responsable_id')) {
                $table->unsignedBigInteger('responsable_id')->nullable()->after('manager_id');
                
                // Recréer la contrainte
                $table->foreign('responsable_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            }
        });
    }
};