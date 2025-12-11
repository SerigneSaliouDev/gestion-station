<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Début de la réparation de la base de données...');
        
        // 1. Stations
        $this->createStationsTable();
        
        // 2. Sales
        $this->createSalesTable();
        
        // 3. Colonnes manquantes
        $this->addMissingColumns();
        
        $this->command->info('Réparation terminée!');
    }
    
    private function createStationsTable(): void
    {
        if (!Schema::hasTable('stations')) {
            $this->command->info('Création de la table stations...');
            
            DB::statement("
                CREATE TABLE stations (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    code VARCHAR(50) UNIQUE NOT NULL,
                    nom VARCHAR(255) NOT NULL,
                    ville VARCHAR(100) NOT NULL,
                    quartier VARCHAR(100) NULL,
                    adresse VARCHAR(255) NULL,
                    telephone VARCHAR(20) NULL,
                    email VARCHAR(100) NULL,
                    responsable VARCHAR(100) NULL,
                    statut ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
                    nombre_pompes INT DEFAULT 0,
                    capacite_total DECIMAL(10,2) DEFAULT 0.00,
                    notes TEXT NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    deleted_at TIMESTAMP NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Ajouter une station par défaut
            DB::table('stations')->insert([
                'code' => 'STA001',
                'nom' => 'Station Principale',
                'ville' => 'Abidjan',
                'statut' => 'active',
                'nombre_pompes' => 4,
                'capacite_total' => 100000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
    
    private function createSalesTable(): void
    {
        if (!Schema::hasTable('sales')) {
            $this->command->info('Création de la table sales...');
            
            DB::statement("
                CREATE TABLE sales (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    sale_date DATETIME NOT NULL,
                    fuel_type ENUM('super', 'gazole') NOT NULL,
                    quantity DECIMAL(10,2) NOT NULL,
                    unit_price DECIMAL(10,2) NOT NULL,
                    total_amount DECIMAL(15,2) NOT NULL,
                    pump_number VARCHAR(50) NULL,
                    payment_method ENUM('cash', 'card', 'mobile_money', 'credit') NOT NULL,
                    customer_type ENUM('retail', 'wholesale', 'corporate') NULL,
                    customer_name VARCHAR(255) NULL,
                    vehicle_number VARCHAR(50) NULL,
                    shift_id BIGINT UNSIGNED NULL,
                    station_id BIGINT UNSIGNED NULL,
                    recorded_by BIGINT UNSIGNED NOT NULL,
                    cancelled_at TIMESTAMP NULL,
                    cancelled_by BIGINT UNSIGNED NULL,
                    cancellation_reason TEXT NULL,
                    stock_before DECIMAL(10,2) DEFAULT 0.00,
                    stock_after DECIMAL(10,2) DEFAULT 0.00,
                    notes TEXT NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Ajouter les indexes
            DB::statement('ALTER TABLE sales ADD INDEX idx_sale_date (sale_date)');
            DB::statement('ALTER TABLE sales ADD INDEX idx_fuel_type (fuel_type)');
            DB::statement('ALTER TABLE sales ADD INDEX idx_payment_method (payment_method)');
        }
    }
    
    private function addMissingColumns(): void
    {
        if (Schema::hasTable('shift_saisies')) {
            $columns = DB::select('DESCRIBE shift_saisies');
            $existingColumns = array_column($columns, 'Field');
            
            if (!in_array('stock_synced_at', $existingColumns)) {
                $this->command->info('Ajout de stock_synced_at à shift_saisies...');
                DB::statement('ALTER TABLE shift_saisies ADD stock_synced_at TIMESTAMP NULL AFTER ecart_final');
            }
            
            if (!in_array('stock_sync_notes', $existingColumns)) {
                $this->command->info('Ajout de stock_sync_notes à shift_saisies...');
                DB::statement('ALTER TABLE shift_saisies ADD stock_sync_notes TEXT NULL AFTER stock_synced_at');
            }
        }
        
        if (Schema::hasTable('users')) {
            $columns = DB::select('DESCRIBE users');
            $existingColumns = array_column($columns, 'Field');
            
            if (!in_array('role', $existingColumns)) {
                $this->command->info('Ajout de role à users...');
                DB::statement("ALTER TABLE users ADD role ENUM('administrateur', 'manager', 'chief', 'charge-operations', 'pompiste') DEFAULT 'pompiste' AFTER email");
            }
            
            if (!in_array('station_id', $existingColumns)) {
                $this->command->info('Ajout de station_id à users...');
                DB::statement('ALTER TABLE users ADD station_id BIGINT UNSIGNED NULL AFTER role');
            }
        }
    }
}