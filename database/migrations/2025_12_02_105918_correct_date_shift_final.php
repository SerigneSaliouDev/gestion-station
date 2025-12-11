<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // ÉTAPE 1: Vérifier si la colonne existe
        if (!Schema::hasColumn('shift_saisies', 'date_shift')) {
            // Si elle n'existe pas, la créer simplement
            Schema::table('shift_saisies', function (Blueprint $table) {
                $table->date('date_shift')->default('2025-12-02')->after('shift');
            });
        } else {
            // ÉTAPE 2: Vérifier le type de colonne actuel
            $columnInfo = DB::select("
                SHOW COLUMNS FROM shift_saisies WHERE Field = 'date_shift'
            ")[0] ?? null;
            
            if ($columnInfo) {
                $type = strtolower($columnInfo->Type);
                $isNullable = $columnInfo->Null === 'YES';
                
                // ÉTAPE 3: Si c'est déjà un DATE, juste s'assurer qu'il n'est pas nullable
                if (strpos($type, 'date') !== false) {
                    // Remplir les valeurs NULL
                    DB::table('shift_saisies')
                        ->whereNull('date_shift')
                        ->update(['date_shift' => '2025-12-02']);
                    
                    // Changer en NOT NULL
                    Schema::table('shift_saisies', function (Blueprint $table) {
                        $table->date('date_shift')->nullable(false)->change();
                    });
                } else {
                    // ÉTAPE 4: Si c'est un VARCHAR/TEXT, le convertir
                    $this->convertVarcharToDate();
                }
            }
        }
    }
    
    private function convertVarcharToDate()
    {
        // Désactiver temporairement les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Créer une nouvelle table temporaire
        $tempTable = 'shift_saisies_temp_' . time();
        
        DB::statement("
            CREATE TABLE {$tempTable} LIKE shift_saisies
        ");
        
        // Ajouter la colonne date_shift_temp
        DB::statement("
            ALTER TABLE {$tempTable} 
            ADD COLUMN date_shift_new DATE DEFAULT '2025-12-02' AFTER shift,
            DROP COLUMN date_shift
        ");
        
        // Copier les données en convertissant
        DB::statement("
            INSERT INTO {$tempTable} (
                id, shift, date_shift_new, responsable, total_litres,
                total_ventes, versement, ecart, user_id, created_at, updated_at
            )
            SELECT 
                id, 
                shift,
                CASE 
                    WHEN date_shift IS NULL OR TRIM(date_shift) = '' THEN '2025-12-02'
                    WHEN date_shift REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' 
                    THEN STR_TO_DATE(date_shift, '%d/%m/%Y')
                    WHEN date_shift REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' 
                    THEN date_shift
                    ELSE '2025-12-02'
                END as date_shift_new,
                responsable,
                total_litres,
                total_ventes,
                versement,
                ecart,
                user_id,
                created_at,
                updated_at
            FROM shift_saisies
        ");
        
        // Supprimer l'ancienne table
        DB::statement('DROP TABLE shift_saisies');
        
        // Renommer la nouvelle table
        DB::statement("RENAME TABLE {$tempTable} TO shift_saisies");
        
        // Renommer la colonne
        DB::statement("
            ALTER TABLE shift_saisies 
            CHANGE COLUMN date_shift_new date_shift DATE NOT NULL
        ");
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Recréer les contraintes
        DB::statement("
            ALTER TABLE shift_saisies 
            ADD PRIMARY KEY (id),
            ADD CONSTRAINT shift_saisies_user_id_foreign 
            FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ");
    }

    public function down()
    {
        // Pour rollback, juste rendre nullable
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->date('date_shift')->nullable()->change();
        });
    }
};