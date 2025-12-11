<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Vérifier d'abord le type actuel de la colonne
        $columnInfo = DB::select("
            SHOW COLUMNS FROM shift_saisies WHERE Field = 'date_shift'
        ");
        
        if (empty($columnInfo)) {
            // La colonne n'existe pas, la créer
            Schema::table('shift_saisies', function (Blueprint $table) {
                $table->date('date_shift')->default('2025-12-01')->after('shift');
            });
        } else {
            $columnType = $columnInfo[0]->Type;
            
            if (strpos($columnType, 'varchar') !== false || strpos($columnType, 'text') !== false) {
                // C'est un texte, on doit le convertir
                
                // 1. Créer une colonne temporaire
                Schema::table('shift_saisies', function (Blueprint $table) {
                    $table->date('date_shift_temp')->nullable()->after('date_shift');
                });
                
                // 2. Convertir les données
                DB::update("
                    UPDATE shift_saisies 
                    SET date_shift_temp = 
                        CASE 
                            WHEN TRIM(date_shift) = '' THEN '2025-12-01'
                            WHEN date_shift REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' 
                            THEN STR_TO_DATE(date_shift, '%d/%m/%Y')
                            WHEN date_shift REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' 
                            THEN date_shift
                            ELSE '2025-12-01'
                        END
                ");
                
                // 3. Supprimer l'ancienne colonne
                Schema::table('shift_saisies', function (Blueprint $table) {
                    $table->dropColumn('date_shift');
                });
                
                // 4. Renommer
                Schema::table('shift_saisies', function (Blueprint $table) {
                    $table->renameColumn('date_shift_temp', 'date_shift');
                });
            }
            
            // Finalement, s'assurer que c'est NOT NULL
            Schema::table('shift_saisies', function (Blueprint $table) {
                $table->date('date_shift')->nullable(false)->change();
            });
        }
    }

    public function down()
    {
        // Pour rollback
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->date('date_shift')->nullable()->change();
        });
    }
};