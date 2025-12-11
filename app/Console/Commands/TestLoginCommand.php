<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TestLoginCommand extends Command
{
    protected $signature = 'test:login';
    protected $description = 'Tester la connexion des utilisateurs';

    public function handle()
    {
        $this->info('=== TEST DE CONNEXION MANAGER ===');

        // Test 1: Vérifier si l'utilisateur existe
        $user = User::where('email', 'manager@entreprise.com')->first();

        if ($user) {
            $this->info('✅ Utilisateur trouvé:');
            $this->line("   Email: " . $user->email);
            $this->line("   Nom: " . $user->name);
            $this->line("   Rôles: " . $user->getRoleNames()->implode(', '));
            
            // Test 2: Vérifier le mot de passe
            $passwordOk = Hash::check('Saliou2003', $user->password);
            $this->info('🔐 Test mot de passe: ' . ($passwordOk ? '✅ CORRECT' : '❌ INCORRECT'));
            
            // Test 3: Tester la connexion Auth
            if (Auth::attempt(['email' => 'manager@entreprise.com', 'password' => 'Saliou2003'])) {
                $this->info('🎉 CONNEXION AUTH RÉUSSIE!');
                $this->line("   Utilisateur connecté: " . Auth::user()->email);
                $this->line("   Rôles: " . Auth::user()->getRoleNames()->implode(', '));
                
                // Vérifier la redirection
                if (Auth::user()->hasRole('manager')) {
                    $this->info('🔄 Redirection attendue vers: manager.index_form');
                }
                
                // Déconnexion
                Auth::logout();
                $this->info('🔒 Déconnecté');
            } else {
                $this->error('❌ ÉCHEC CONNEXION AUTH');
            }
        } else {
            $this->error('❌ UTILISATEUR NON TROUVÉ');
        }

        $this->info("\n=== TEST DES AUTRES UTILISATEURS ===");

        $admin = User::where('email', 'admin@entreprise.com')->first();
        $operations = User::where('email', 'operations@entreprise.com')->first();

        $this->line("Admin: " . ($admin ? "✅ Trouvé (" . $admin->getRoleNames()->implode(', ') . ")" : "❌ Non trouvé"));
        $this->line("Operations: " . ($operations ? "✅ Trouvé (" . $operations->getRoleNames()->implode(', ') . ")" : "❌ Non trouvé"));

        return 0;
    }
}