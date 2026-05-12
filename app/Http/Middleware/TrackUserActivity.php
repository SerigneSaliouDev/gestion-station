<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Mettre à jour seulement toutes les 5 minutes pour éviter trop d'écritures
            $lastActivity = $user->last_activity_at;
            $shouldUpdate = !$lastActivity || $lastActivity->diffInMinutes(now()) >= 5;
            
            if ($shouldUpdate) {
                $user->update([
                    'last_activity_at' => now(),
                    'last_request_url' => $request->fullUrl(),
                ]);
            }
        }
        
        return $next($request);
    }
}