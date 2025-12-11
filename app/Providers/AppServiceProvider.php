<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Surcharger la réponse de login Fortify
        $this->app->singleton(LoginResponseContract::class, function () {
            return new class implements LoginResponseContract {
                public function toResponse($request)
                {
                    $user = $request->user();
                    
                    Log::info('Fortify LoginResponse - Redirection:', [
                        'email' => $user->email,
                        'roles' => $user->getRoleNames()->toArray()
                    ]);

                    if ($user->hasRole('manager')) {
                        return redirect()->route('manager.index_form');
                    } elseif ($user->hasRole('administrateur')) {
                        return redirect('/dashboard');
                    } elseif ($user->hasRole('charge-operations')) {
                        return redirect('/operations-dashboard');
                    }

                    return redirect('/dashboard');
                }
            };
        });
    }

    public function boot()
    {
        //
    }
}