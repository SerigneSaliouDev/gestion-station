<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Où rediriger les utilisateurs après connexion.
     *
     * @var string
     */
    protected $redirectTo = '/'; // MODIFIEZ ICI

    /**
     * Affiche le formulaire de connexion.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Gère une tentative de connexion.
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Valide la tentative de connexion.
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    }

    /**
     * Tente de connecter l'utilisateur.
     */
    protected function attemptLogin(Request $request)
    {
        return Auth::attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * Récupère les informations d'identification de la requête.
     */
    protected function credentials(Request $request)
    {
        return $request->only('email', 'password');
    }

    /**
     * Envoie la réponse après une connexion réussie.
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $user = Auth::user();
        
        // REDIRECTION PERSONNALISÉE
        if ($user->hasRole('chief')) {
            return redirect()->route('chief.validations');
        } elseif ($user->hasRole('manager')) {
            return redirect()->route('manager.index_form');
        }
        
        // Par défaut
        return redirect()->intended($this->redirectPath());
    }

    /**
     * Obtient le chemin de redirection après connexion.
     */
    protected function redirectPath()
    {
        return '/';
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