<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        View::composer(['properties.home', 'properties.index', 'properties.show', 'dashboard.favorites'], function ($view): void {
            $view->with('savedPropertyIds', request()->user()?->favorites()->pluck('properties.id')->all() ?? []);
        });
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        // Breached-password lookups only in production, so tests and local previews stay offline.
        Password::defaults(fn () => $this->app->isProduction() ? Password::min(12)->uncompromised() : Password::min(12));
        // Per account and per IP, so one address cannot spray a password across many accounts.
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(30)->by('ip|'.$request->ip()),
        ]);
    }
}
