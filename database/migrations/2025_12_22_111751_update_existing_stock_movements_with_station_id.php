<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
// Dans database/migrations/xxxx_update_existing_stock_movements_with_station_id.php

public function up()
{
    // Pour les mouvements de stock existants
    $movements = DB::table('stock_movements')->get();
    
    foreach ($movements as $movement) {
        // Récupérer l'utilisateur qui a enregistré le mouvement
        $user = DB::table('users')->find($movement->recorded_by);
        
        if ($user && $user->station_id) {
            DB::table('stock_movements')
                ->where('id', $movement->id)
                ->update(['station_id' => $user->station_id]);
        } else {
            // Si pas de station_id, mettre la station par défaut (1)
            DB::table('stock_movements')
                ->where('id', $movement->id)
                ->update(['station_id' => 1]);
        }
    }
    
    // Pour les jaugeages existants
    $tankLevels = DB::table('tank_levels')->get();
    
    foreach ($tankLevels as $tankLevel) {
        // Récupérer l'utilisateur qui a mesuré
        $user = DB::table('users')->find($tankLevel->measured_by);
        
        if ($user && $user->station_id) {
            DB::table('tank_levels')
                ->where('id', $tankLevel->id)
                ->update(['station_id' => $user->station_id]);
        } else {
            // Si pas de station_id, mettre la station par défaut (1)
            DB::table('tank_levels')
                ->where('id', $tankLevel->id)
                ->update(['station_id' => 1]);
        }
    }
}

public function down()
{
    // Pas de rollback nécessaire
}
};
