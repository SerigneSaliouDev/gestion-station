<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectByRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // On ignore ce middleware pour les routes d'authentification
        if ($request->routeIs('login') || $request->routeIs('login.post') || $request->routeIs('logout')) {
            return $next($request);
        }

        if (auth()->check()) {
            $user = auth()->user();
            
            // Les logs de débogage sont utiles, on les garde temporairement.
            \Log::info('=== DEBUG RedirectByRole ===');
            \Log::info('User ID: ' . $user->id);
            \Log::info('User Email: ' . $user->email);
            \Log::info('User Roles: ' . implode(', ', $user->roles->pluck('name')->toArray()));

            // ******************************************************************
            // * CORRECTION MAJEURE : SUPPRESSION DE TOUTES LES REDIRECTIONS ICI *
            // * La route racine '/' est désormais responsable de l'accueil.     *
            // ******************************************************************
            
            \Log::info('Middleware RedirectByRole: Continuing to next middleware/route...');
            
            // Si vous voulez être très strict et rediriger les utilisateurs sans rôle connu
            // vers le login (ou vers /), vous pouvez ajouter une logique de "fallback" ici, 
            // mais ce n'est pas nécessaire pour la page d'accueil.
        } else {
            \Log::info('User not authenticated');
        }

        return $next($request);
    }
}