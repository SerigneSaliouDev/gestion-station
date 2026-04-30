<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StockOperationGuard
{
    /**
     * Empêcher les opérations de stock simultanées
     */
    public function handle(Request $request, Closure $next, $operation = 'stock')
    {
        $user = $request->user();
        
        if (!$user) {
            return $next($request);
        }
        
        $stationId = $user->station_id;
        
        // Clé unique par station + utilisateur + opération
        $lockKey = "stock_lock_{$stationId}_{$user->id}_{$operation}";
        
        // Vérifier si une opération est déjà en cours
        if (Cache::has($lockKey)) {
            \Log::warning('Tentative d\'opération simultanée bloquée', [
                'user_id' => $user->id,
                'station_id' => $stationId,
                'operation' => $operation,
                'locked_until' => Cache::get($lockKey . '_until')
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Une opération de stock est déjà en cours. Veuillez patienter.',
                    'retry_after' => Cache::get($lockKey . '_until')
                ], 429);
            }
            
            return redirect()->back()
                ->with('error', 'Une opération de stock est déjà en cours. Veuillez patienter quelques secondes.');
        }
        
        // Verrouiller pour 30 secondes
        $unlockTime = now()->addSeconds(30);
        Cache::put($lockKey, true, 30);
        Cache::put($lockKey . '_until', $unlockTime->toDateTimeString(), 30);
        
        \Log::info('Verrou de stock acquis', [
            'user_id' => $user->id,
            'station_id' => $stationId,
            'operation' => $operation,
            'until' => $unlockTime
        ]);
        
        try {
            $response = $next($request);
            
            // Libérer immédiatement si succès
            if (method_exists($response, 'getStatusCode')) {
                $statusCode = $response->getStatusCode();
                if ($statusCode >= 200 && $statusCode < 300) {
                    Cache::forget($lockKey);
                    Cache::forget($lockKey . '_until');
                    
                    \Log::info('Verrou de stock libéré (succès)', [
                        'user_id' => $user->id,
                        'operation' => $operation
                    ]);
                }
            }
            
            return $response;
            
        } catch (\Exception $e) {
            // En cas d'erreur, libérer le verrou
            Cache::forget($lockKey);
            Cache::forget($lockKey . '_until');
            
            \Log::error('Verrou de stock libéré (erreur)', [
                'user_id' => $user->id,
                'operation' => $operation,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}