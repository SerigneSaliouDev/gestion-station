<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeederCustom extends Seeder
{
    public function run()
    {
        // 1. Création des Rôles
        $roles = ['administrateur', 'manager', 'charge-operations'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 2. Création de l'utilisateur Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@entreprise.com'],
            [
                'name' => 'Manager Station',
                'password' => Hash::make('Saliou2003'),
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole('manager');

        // 3. Création de l'utilisateur Administrateur
        $admin = User::firstOrCreate(
            ['email' => 'admin@entreprise.com'],
            [
                'name' => 'Admin System',
                'password' => Hash::make('Saliou2003'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('administrateur');

        // 4. Création de l'utilisateur Chargé des Opérations
        $operations = User::firstOrCreate(
            ['email' => 'operations@entreprise.com'],
            [
                'name' => 'Chargé des Opérations',
                'password' => Hash::make('Saliou2003'),
                'email_verified_at' => now(),
            ]
        );
        $operations->assignRole('charge-operations');

        \Log::info('RolePermissionSeederCustom exécuté avec succès');
    }
}