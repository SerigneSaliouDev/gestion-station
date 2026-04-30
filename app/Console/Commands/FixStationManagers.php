<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Station;
use App\Models\User;

class FixStationManagers extends Command
{
    protected $signature = 'fix:station-managers';
    protected $description = 'Corriger les problèmes d\'assignation des managers';

    public function handle()
    {
        $this->info('=== CORRECTION DES ASSIGNATIONS MANAGERS ===');
        
        // 1. Trouver les conflits (même manager sur plusieurs stations)
        $this->info("\n1. Recherche des conflits...");
        $stationsByManager = [];
        
        $stations = Station::whereNotNull('manager_id')->get();
        foreach ($stations as $station) {
            $stationsByManager[$station->manager_id][] = $station;
        }
        
        foreach ($stationsByManager as $managerId => $stations) {
            if (count($stations) > 1) {
                $this->error("CONFLIT: Manager ID {$managerId} est sur " . count($stations) . " stations:");
                foreach ($stations as $station) {
                    $this->line("   - Station: {$station->id}. {$station->nom}");
                }
            }
        }
        
        // 2. Vérifier que les managers ont le bon rôle
        $this->info("\n2. Vérification des rôles...");
        foreach ($stations as $station) {
            if ($station->manager_id) {
                $user = User::find($station->manager_id);
                if (!$user) {
                    $this->warn("Station {$station->nom}: Manager ID {$station->manager_id} n'existe pas");
                } elseif (!$user->hasRole('manager')) {
                    $this->warn("Station {$station->nom}: {$user->name} n'a pas le rôle 'manager' (rôles: " . $user->getRoleNames()->implode(', ') . ")");
                }
            }
        }
        
        // 3. Suggestions de correction
        $this->info("\n3. Suggestions:");
        $this->info("   a) Exécutez: php artisan migrate pour supprimer responsable_id");
        $this->info("   b) Réassignez les managers en conflit");
        $this->info("   c) Vérifiez les rôles des utilisateurs");
        
        return 0;
    }
}