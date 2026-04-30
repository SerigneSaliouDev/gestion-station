<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /**
     * Où rediriger les utilisateurs après connexion.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Crée une nouvelle instance du contrôleur.
     */
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

        // Tenter la connexion
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ], $request->filled('remember'))) {
            
            $request->session()->regenerate();
            
            // Redirection selon le rôle
            $user = Auth::user();
            
            // Définir la station active selon le rôle
            $this->setActiveStationForUser($user);
            
            if ($user->isManager()) {
                // Vérifier si le manager a une station assignée
                if ($user->station_id) {
                    // Stocker la station dans la session
                    Session::put('current_station_id', $user->station_id);
                    
                    // RÉDIRIGER DIRECTEMENT VERS LA PAGE DE SAISIE DES INDEX
                    return redirect()->route('manager.index_form');
                } else {
                    // Si aucun manager n'a de station, afficher une erreur
                    return redirect()->route('manager.no-station');
                }
            } elseif ($user->isChief()) {
                // Le chef d'opérations voit toutes les stations
                return redirect()->route('station.select');
            } elseif ($user->isAdmin()) {
                // L'administrateur va au tableau de bord admin
                return redirect()->route('admin.users.index');
            }
            
            return redirect()->intended($this->redirectTo);
        }

        // Si la connexion échoue
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Définit la station active pour l'utilisateur selon son rôle
     */
    protected function setActiveStationForUser($user)
    {
        if ($user->isManager()) {
            // Pour un manager, la station active est toujours sa station assignée
            if ($user->station_id) {
                $user->setActiveStation($user->station_id);
                Session::put('active_station_id', $user->station_id);
                Session::put('current_station', $user->station);
            }
        } elseif ($user->isChief() || $user->isAdmin()) {
            // Pour les chefs/admins, on ne définit pas de station par défaut
            // Ils devront en sélectionner une
            Session::forget('active_station_id');
            Session::forget('current_station');
        }
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}