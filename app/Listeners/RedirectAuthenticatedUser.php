<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RedirectAuthenticatedUser
{
    public function handle(Login $event)
    {
        $user = $event->user;
        
        Log::info('Login Event - Redirection utilisateur:', [
            'email' => $user->email,
            'roles' => $user->getRoleNames()->toArray()
        ]);

        // La redirection se fera via la réponse de Fortify
        // Cette méthode est principalement pour le logging
    }
}