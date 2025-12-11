<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // VÉRIFIER d'abord si la colonne existe et son type
        $exists = Schema::hasColumn('shift_saisies', 'date_shift');
        
        if (!$exists) {
            // Créer la colonne si elle n'existe pas
            Schema::table('shift_saisies', function (Blueprint $table) {
                $table->date('date_shift')->default('2025-12-02')->after('shift');
            });
        } else {
            // Juste s'assurer que c'est un DATE et non nullable
            try {
                Schema::table('shift_saisies', function (Blueprint $table) {
                    $table->date('date_shift')->nullable(false)->default('2025-12-02')->change();
                });
            } catch (\Exception $e) {
                // Si erreur, on fait une conversion manuelle
                $this->manualFix();
            }
        }
    }
    
    private function manualFix()
    {
        // Remplir d'abord toutes les valeurs NULL
        DB::table('shift_saisies')
            ->whereNull('date_shift')
            ->orWhere('date_shift', '=', '')
            ->update(['date_shift' => '2025-12-02']);
        
        // Puis changer le type
        DB::statement("
            ALTER TABLE shift_saisies 
            MODIFY COLUMN date_shift DATE NOT NULL DEFAULT '2025-12-02'
        ");
    }

    public function down()
    {
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->date('date_shift')->nullable()->change();
        });
    }
};