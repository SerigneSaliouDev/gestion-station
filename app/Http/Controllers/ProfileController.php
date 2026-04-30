<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Affiche le formulaire de profil utilisateur.
     */
    public function show(Request $request)
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Affiche le formulaire de modification du profil.
     */
    public function editForm(Request $request)
    {
        return view('profile.update-profile-information-form', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Met à jour les informations du profil utilisateur.
     */
    public function update(Request $request, UpdatesUserProfileInformation $updater)
    {
        $updater->update($request->user(), $request->all());

        return $request->wantsJson()
                    ? new JsonResponse('', 200)
                    : back()->with('status', 'profile-information-updated');
    }

    /**
     * Affiche le formulaire de changement de mot de passe.
     */
    public function passwordForm(Request $request)
    {
        return view('profile.update-password-form');
    }

    /**
     * Met à jour le mot de passe.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current-password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('status', 'password-updated');
    }

    /**
     * Affiche la page 2FA.
     */
    public function twoFactor(Request $request)
    {
        return view('profile.two-factor-authentication-form');
    }

    /**
     * Affiche la page de déconnexion autres sessions.
     */
    public function logoutOther(Request $request)
    {
        return view('profile.logout-other-browser-sessions-form');
    }

    /**
     * Affiche le formulaire de suppression de compte.
     */
    public function deleteForm(Request $request)
    {
        return view('profile.delete-user-form');
    }

    /**
     * Supprime le compte utilisateur.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}