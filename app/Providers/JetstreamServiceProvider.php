<?php

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        Jetstream::deleteUsersUsing(DeleteUser::class);

        // ⚠️ SUPPRIMEZ ou COMMETEZ le code problématique :
        // Jetstream::redirectAfterLoginUsing(function ($request) {
        //     $user = $request->user();
        //     
        //     \Log::info('Jetstream redirectAfterLogin - User:', [
        //         'email' => $user->email,
        //         'roles' => $user->getRoleNames()->toArray()
        //     ]);
        //
        //     if ($user->hasRole('manager')) {
        //         return route('manager.index_form');
        //     } elseif ($user->hasRole('administrateur')) {
        //         return '/dashboard';
        //     } elseif ($user->hasRole('charge-operations')) {
        //         return '/operations-dashboard';
        //     }
        //
        //     return '/dashboard';
        // });
    }

    /**
     * Configure the permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}