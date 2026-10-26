<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

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
        View::composer('layouts.admin', function ($view) {
            $notifications = collect();
            $unreadNotifications = collect();

            if (Auth::check() && Auth::user()->role === 'admin') {
                $user = Auth::user();

                $notifications = $user->notifications()
                    ->latest()
                    ->take(10)
                    ->get();

                $unreadNotifications = $user->unreadNotifications()
                    ->latest()
                    ->take(10)
                    ->get();
            }

            $view->with('notifications', $notifications);
            $view->with('unreadNotifications', $unreadNotifications);
        });
    }

}
