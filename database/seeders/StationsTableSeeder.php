<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Station;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StationsTableSeeder extends Seeder
{
    public function run()
    {
        // Créer un chef d'opérations (utilise Spatie Role)
        $chiefRole = Role::firstOrCreate(['name' => 'charge-operations']);
        
        $chief = User::firstOrCreate(
            ['email' => 'chief@odyssee.sn'],
            [
                'name' => 'Chef Opérations',
                'password' => Hash::make('Saliou2003'),
            ]
        );
        $chief->assignRole('charge-operations');
        
        // Créer des stations - STATION PILOTE A EN PREMIER
        $stations = [
            [
                'nom' => 'Station Pilote A - Mermoz',
                'code' => 'A', // ← CODE A POUR LA STATION PILOTE
                'adresse' => 'Rue de Mermoz',
                'ville' => 'Dakar',
                'telephone' => '33 889 00 00',
                'statut' => 'actif',
                'capacite_super' => 10000,
                'capacite_gazole' => 15000,
            ],
            [
                'nom' => 'Odyssée Energie Ouakam',
                'code' => 'B',
                'adresse' => 'Rue de Ouakam',
                'ville' => 'Dakar',
                'telephone' => '33 889 00 01',
                'statut' => 'actif',
                'capacite_super' => 8000,
                'capacite_gazole' => 12000,
            ],
            [
                'nom' => 'Odyssée Energie Pikine',
                'code' => 'C',
                'adresse' => 'Rue de Pikine',
                'ville' => 'Dakar',
                'telephone' => '33 889 00 02',
                'statut' => 'actif',
                'capacite_super' => 9000,
                'capacite_gazole' => 13000,
            ],
        ];
        
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        
        foreach ($stations as $index => $stationData) {
            $station = Station::firstOrCreate(
                ['code' => $stationData['code']],
                $stationData
            );
            
            // Créer un gérant pour chaque station
            $managerEmail = 'gerant' . $stationData['code'] . '@odyssee.sn';
            $manager = User::firstOrCreate(
                ['email' => $managerEmail],
                [
                    'name' => 'Gérant ' . $station->nom,
                    'password' => Hash::make('Saliou2003'),
                    'station_id' => $station->id,
                ]
            );
            
            // Assigner le rôle manager
            if (!$manager->hasRole('manager')) {
                $manager->assignRole('manager');
            }
            
            // Assigner comme responsable de la station
            $station->update(['manager_id' => $manager->id]);
            
            echo "✓ Station créée: {$station->nom} (Code: {$station->code}) avec gérant: {$manager->name} ({$managerEmail})\n";
        }
        
        echo "\n=== COMPTES CRÉÉS ===\n";
        echo "Manager Station A: gerantA@odyssee.sn / Saliou2003\n";
        echo "Manager Station B: gerantB@odyssee.sn / Saliou2003\n";
        echo "Manager Station C: gerantC@odyssee.sn / Saliou2003\n";
        echo "Chef Opérations: chief@odyssee.sn / Saliou2003\n";
    }
}