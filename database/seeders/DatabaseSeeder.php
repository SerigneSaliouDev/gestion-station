<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
       
        
        // Assurez-vous que votre seeder personnalisé est appelé
        $this->call(RolePermissionSeederCustom::class);
    }
}