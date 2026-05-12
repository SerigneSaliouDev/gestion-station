<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log; // Ajoutez ceci

class LoginController extends Controller
{
    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Gère une tentative de connexion.
     */
    public function login(Request $request)
    {
        // Valider les données
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Tentative de connexion
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ], $request->filled('remember'))) {
            
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // ===== IMPORTANT: METTRE À JOUR LES INFOS DE CONNEXION =====
            $user->update([
                'last_login_at' => now(),           // Dernière connexion
                'last_activity_at' => now(),        // Dernière activité
                'last_login_ip' => $request->ip(),  // IP de connexion
                'last_user_agent' => $request->userAgent(), // Navigateur
                'email_verified_at' => $user->email_verified_at ?? now(), // Force la vérification
                'is_active' => true,                // Active le compte
                'statut' => 'active',               // Met à jour le statut
            ]);
            
            // Log pour débogage
            Log::info('Utilisateur connecté', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'ip' => $request->ip(),
                'time' => now()->toDateTimeString()
            ]);
            
            // Définir la station active selon le rôle
            $this->setActiveStationForUser($user);
            
            // Redirection selon le rôle
            if ($user->isManager()) {
                if ($user->station_id) {
                    Session::put('current_station_id', $user->station_id);
                    Session::put('current_station_name', $user->station->nom ?? 'Station inconnue');
                    
                    // Redirection vers la saisie des index
                    return redirect()->route('manager.index_form');
                } else {
                    return redirect()->route('manager.no-station')
                        ->with('error', 'Vous n\'êtes pas assigné à une station. Contactez l\'administrateur.');
                }
            } 
            elseif ($user->isChief()) {
                return redirect()->route('station.select');
            } 
            elseif ($user->isAdmin()) {
                return redirect()->route('admin.users.index');
            }
            
            return redirect()->intended($this->redirectTo);
        }

        // Log de l'échec de connexion
        Log::warning('Tentative de connexion échouée', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'time' => now()->toDateTimeString()
        ]);

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Définit la station active pour l'utilisateur selon son rôle
     */
    protected function setActiveStationForUser($user)
    {
        try {
            if ($user->isManager()) {
                if ($user->station_id) {
                    // Charger la relation si pas déjà fait
                    if (!$user->relationLoaded('station')) {
                        $user->load('station');
                    }
                    
                    $user->setActiveStation($user->station_id);
                    Session::put('active_station_id', $user->station_id);
                    
                    if ($user->station) {
                        Session::put('current_station', [
                            'id' => $user->station->id,
                            'name' => $user->station->nom,
                            'code' => $user->station->code,
                        ]);
                    }
                }
            } elseif ($user->isChief() || $user->isAdmin()) {
                // Nettoyer la session pour les chefs/admins
                Session::forget('active_station_id');
                Session::forget('current_station');
                Session::forget('current_station_id');
                Session::forget('current_station_name');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la définition de la station active', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            Log::info('Utilisateur déconnecté', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
            
            // Optionnel: Enregistrer la dernière déconnexion
            $user->update(['last_logout_at' => now()]);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}