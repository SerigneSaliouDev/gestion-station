<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CheckStation
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }
        
        // 1. Les Administrateurs voient tout et sont exclus de la vérification.
        if ($user->hasRole('administrateur')) {
            return $next($request);
        }
        
        // *******************************************************************
        // * CORRECTION CRITIQUE : Vérification de la station ACTIVE/AFFECTÉE  *
        // *******************************************************************
        
        // Récupérer la station active (utilise active_station_id pour Chief, station_id pour Manager)
        $activeStationId = $user->getActiveStation();

        // Si l'utilisateur n'a PAS de station active (ni active_station_id ni station_id)
        if (empty($activeStationId)) {
            
            // Si la route actuelle n'est pas déjà le sélecteur, on redirige
            $routeName = $request->route()?->getName();

            if ($routeName !== 'station.select' && $routeName !== 'station.select.post') {
                
                // Si l'utilisateur est un Chief ou un Manager sans station, on le force à choisir
                return redirect()->route('station.select')->with('info', 'Veuillez sélectionner une station pour continuer.');
            }
            
            // Si l'utilisateur est sur la page de sélection, on le laisse continuer.
            return $next($request);
        }

        // *******************************************************************
        // * VÉRIFICATION D'ACCÈS DU MANAGER (Contrôle de permission)        *
        // *******************************************************************
        
        if ($user->hasRole('manager')) {
            
            // 2. Vérification de l'accès aux données (par exemple, pour l'édition d'un shift)
            if ($request->route()->hasParameter('shift')) {
                $shift = \App\Models\ShiftSaisie::find($request->route()->parameter('shift'));
                
                // Si le shift existe et que l'ID de la station ne correspond pas à la station du Manager
                if ($shift && $shift->station_id !== $user->station_id) {
                    abort(403, 'Accès non autorisé à cette station ou à ce shift.');
                }
            }
        }
        
        return $next($request);
    }
}