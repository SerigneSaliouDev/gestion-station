<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shift_saisies', function (Blueprint $table) {
            // Ajouter station_id si elle n'existe pas
            if (!Schema::hasColumn('shift_saisies', 'station_id')) {
                $table->foreignId('station_id')->nullable()->after('user_id')->constrained('stations');
            }
            
            // Ajouter les autres champs si nécessaire
            if (!Schema::hasColumn('shift_saisies', 'statut')) {
                $table->enum('statut', ['en_attente', 'valide', 'rejete'])->default('en_attente');
            }
            
            if (!Schema::hasColumn('shift_saisies', 'validated_by')) {
                $table->foreignId('validated_by')->nullable()->constrained('users');
            }
            
            if (!Schema::hasColumn('shift_saisies', 'validation_date')) {
                $table->timestamp('validation_date')->nullable();
            }
            
            if (!Schema::hasColumn('shift_saisies', 'notes_validation')) {
                $table->text('notes_validation')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('shift_saisies', function (Blueprint $table) {
            $table->dropForeign(['station_id']);
            $table->dropForeign(['validated_by']);
            $table->dropColumn(['station_id', 'statut', 'validated_by', 'validation_date', 'notes_validation']);
        });
    }
};