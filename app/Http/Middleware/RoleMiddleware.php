<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Vérifie si l'utilisateur est connecté
        if (! Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Vérifie si l'utilisateur possède le rôle requis
        if (!$user->hasRole($role)) {
            // Si le rôle ne correspond pas, rediriger vers le dashboard
            return redirect('/dashboard')->with('error', 'Accès refusé. Rôle insuffisant.');
        }

        return $next($request);
    }
}