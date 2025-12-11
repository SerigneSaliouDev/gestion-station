<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FuelPrice;
use App\Models\User;

class FuelPricesSeeder extends Seeder
{
    public function run()
    {
        // Prix initiaux
        $initialPrices = [
            ['fuel_type' => 'super', 'price_per_liter' => 850, 'change_reason' => 'Prix initial'],
            ['fuel_type' => 'gazole', 'price_per_liter' => 750, 'change_reason' => 'Prix initial'],
            ['fuel_type' => 'premium', 'price_per_liter' => 900, 'change_reason' => 'Prix initial'],
        ];
        
        // Trouver un utilisateur admin
        $admin = User::where('email', 'admin@entreprise.com')->first();
        
        foreach ($initialPrices as $price) {
            // Vérifier si le prix existe déjà
            $existing = FuelPrice::where('fuel_type', $price['fuel_type'])
                ->latest()
                ->first();
                
            if (!$existing) {
                FuelPrice::create([
                    'fuel_type' => $price['fuel_type'],
                    'price_per_liter' => $price['price_per_liter'],
                    'change_reason' => $price['change_reason'],
                    'changed_by' => $admin ? $admin->id : 1,
                ]);
            }
        }
    }
}