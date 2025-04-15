<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        // Definir rate limiters
        
        // API rate limiter: 60 requests per minute
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        
        // Contact form limiter: 3 submissions per minute
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Demasiados intentos. Por favor, inténtelo de nuevo en un minuto.'
                    ], 429);
                });
        });
        
        // Calendar operations limiter: 30 requests per minute
        RateLimiter::for('calendar', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Demasiadas operaciones de calendario. Por favor, espere un momento.'
                    ], 429);
                });
        });
        
        // Appointments booking limiter: 10 requests per minute
        RateLimiter::for('appointments', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ha alcanzado el límite de solicitudes de citas. Por favor, espere un momento.'
                    ], 429);
                });
        });
        
        // Global rate limiter: 150 requests per minute
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(150)->by($request->ip());
        });
    }
}
