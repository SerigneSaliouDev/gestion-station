 <?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tank_levels', function (Blueprint $table) {
            // Ajouter la colonne 'station_id' comme clé étrangère
            $table->foreignId('station_id')->nullable()->constrained()->after('id');
            // Assurez-vous que la contrainte pointe vers la table 'stations'
            
            // Si vous préférez ajouter la FK après une colonne existante :
            // $table->foreignId('station_id')->nullable()->constrained()->after('verified_by'); 
        });
    }

    public function down(): void
    {
        Schema::table('tank_levels', function (Blueprint $table) {
            // Suppression de la contrainte et de la colonne
            $table->dropForeign(['station_id']);
            $table->dropColumn('station_id');
        });
    }
};