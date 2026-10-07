<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Halaman check-in publik. Batas per IP sengaja longgar karena satu
        // WiFi lokasi acara dipakai banyak peserta dengan IP publik yang sama.
        RateLimiter::for('public-check-in', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
