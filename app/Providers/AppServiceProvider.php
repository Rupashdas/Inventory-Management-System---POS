<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\View;
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
        // The signed-in user, for the shell that every dashboard page renders
        // inside. The header used to read "User Name" -- a literal string in
        // the markup -- for whoever was logged in.
        //
        // Bound to the layout rather than shared globally so the lookup happens
        // when a page is drawn, not on every JSON request that passes through
        // the same middleware.
        View::composer('layout.sidenav-layout', function ($view) {
            $userId = request()->header('userID');

            $view->with('authUser', $userId ? User::find($userId) : null);
        });
    }
}
