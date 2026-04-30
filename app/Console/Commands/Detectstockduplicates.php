<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockMovement;
use App\Models\ShiftSaisie;
use Illuminate\Support\Facades\DB;

class DetectStockDuplicates extends Command
{
    protected $signature = 'stock:detect-duplicates 
                            {--fix : Corriger automatiquement les doublons}
                            {--shift= : Vérifier un shift spécifique}';
    
    protected $description = 'Détecte et corrige les doublons de mouvements de stock';

    public function handle()
    {
        $this->info('🔍 Recherche de doublons de stock movements...');
        
        $shiftId = $this->option('shift');
        $fix = $this->option('fix');
        
        // 1. Détecter les doublons par shift_saisie_id
        $query = DB::table('stock_movements')
            ->select('shift_saisie_id', 'fuel_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('shift_saisie_id')
            ->groupBy('shift_saisie_id', 'fuel_type')
            ->having('count', '>', 1);
        
        if ($shiftId) {
            $query->where('shift_saisie_id', $shiftId);
        }
        
        $duplicates = $query->get();
        
        if ($duplicates->isEmpty()) {
            $this->info('✅ Aucun doublon détecté!');
            return 0;
        }
        
        $this->warn("⚠️  {$duplicates->count()} doublons détectés:");
        
        $table = [];
        $totalExcess = 0;
        
        foreach ($duplicates as $dup) {
            $shift = ShiftSaisie::find($dup->shift_saisie_id);
            $movements = StockMovement::where('shift_saisie_id', $dup->shift_saisie_id)
                ->where('fuel_type', $dup->fuel_type)
                ->orderBy('created_at')
                ->get();
            
            $table[] = [
                'Shift' => $dup->shift_saisie_id,
                'Date' => $shift ? $shift->date_shift->format('Y-m-d') : 'N/A',
                'Carburant' => strtoupper($dup->fuel_type),
                'Doublons' => $dup->count,
                'Quantité totale' => number_format($movements->sum('quantity'), 2) . ' L',
                'Premier ID' => $movements->first()->id,
                'Dernier ID' => $movements->last()->id,
            ];
            
            $totalExcess += ($dup->count - 1);
        }
        
        $this->table(
            ['Shift', 'Date', 'Carburant', 'Doublons', 'Quantité totale', 'Premier ID', 'Dernier ID'],
            $table
        );
        
        $this->warn("📊 Total: {$totalExcess} mouvements en trop");
        
        if (!$fix) {
            $this->info("\n💡 Utilisez --fix pour corriger automatiquement");
            return 0;
        }
        
        // 2. Correction des doublons
        if (!$this->confirm('Voulez-vous vraiment supprimer ces doublons ?')) {
            $this->info('Annulé.');
            return 0;
        }
        
        DB::beginTransaction();
        
        try {
            $deleted = 0;
            
            foreach ($duplicates as $dup) {
                // Garder seulement le PREMIER mouvement créé
                $movements = StockMovement::where('shift_saisie_id', $dup->shift_saisie_id)
                    ->where('fuel_type', $dup->fuel_type)
                    ->orderBy('created_at')
                    ->get();
                
                $keepId = $movements->first()->id;
                
                // Supprimer les autres
                $toDelete = $movements->skip(1)->pluck('id');
                
                $this->info("  Shift {$dup->shift_saisie_id} - {$dup->fuel_type}: Garde #{$keepId}, supprime " . $toDelete->count());
                
                StockMovement::whereIn('id', $toDelete)->delete();
                $deleted += $toDelete->count();
            }
            
            DB::commit();
            
            $this->info("✅ {$deleted} doublons supprimés avec succès!");
            
            // 3. Vérification finale
            $remaining = DB::table('stock_movements')
                ->select('shift_saisie_id', 'fuel_type', DB::raw('COUNT(*) as count'))
                ->whereNotNull('shift_saisie_id')
                ->groupBy('shift_saisie_id', 'fuel_type')
                ->having('count', '>', 1)
                ->count();
            
            if ($remaining == 0) {
                $this->info('✅ Vérification: Tous les doublons ont été nettoyés!');
            } else {
                $this->warn("⚠️  Il reste encore {$remaining} doublons");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Erreur: ' . $e->getMessage());
            return 1;
        }
    }
}