<?php

namespace App\Providers;

use App\Models\User;
use Laravel\Pulse\Facades\Pulse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        Gate::define('viewPulse', function (?User $user) {
            return true;
        });

        Pulse::user(fn($user) => [
            'name' => $user->first_name . ' ' . $user->last_name,
            'extra' => $user->username,
        ]);
    }
}
