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
        $chiefRole = Role::where('name', 'chief')->first();
        if (!$chiefRole) {
            $chiefRole = Role::create(['name' => 'chief']);
        }
        
        $chief = User::firstOrCreate(
            ['email' => 'chief@odyssee.sn'],
            [
                'name' => 'Chef Opérations',
                'password' => Hash::make('password'),
            ]
        );
        $chief->assignRole('chief');
        
        // Créer des stations
        $stations = [
            [
                'nom' => 'Odyssée Energie Mermoz',
                'code' => 'OE001',
                'adresse' => 'Rue de Mermoz',
                'ville' => 'Dakar',
                'telephone' => '33 889 00 00',
            ],
            [
                'nom' => 'Odyssée Energie Ouakam',
                'code' => 'OE002',
                'adresse' => 'Rue de Ouakam',
                'ville' => 'Dakar',
                'telephone' => '33 889 00 01',
            ],
            [
                'nom' => 'Odyssée Energie Pikine',
                'code' => 'OE003',
                'adresse' => 'Rue de Pikine',
                'ville' => 'Dakar',
                'telephone' => '33 889 00 02',
            ],
        ];
        
        $managerRole = Role::where('name', 'manager')->first();
        
        foreach ($stations as $stationData) {
            $station = Station::firstOrCreate(
                ['code' => $stationData['code']],
                $stationData
            );
            
            // Créer un gérant pour chaque station
            $managerEmail = 'gerant' . $station->code . '@odyssee.sn';
            $manager = User::firstOrCreate(
                ['email' => $managerEmail],
                [
                    'name' => 'Gérant ' . $station->nom,
                    'password' => Hash::make('password'),
                    'station_id' => $station->id,
                ]
            );
            
            // Assigner le rôle manager
            $manager->assignRole('manager');
            
            // Assigner comme responsable de la station
            $station->update(['responsable_id' => $manager->id]);
            
            echo "✓ Station créée: {$station->nom} avec gérant: {$manager->name}\n";
        }
    }
}