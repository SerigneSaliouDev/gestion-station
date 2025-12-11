<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

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