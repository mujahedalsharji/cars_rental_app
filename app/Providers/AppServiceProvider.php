<?php

namespace App\Providers;

use App\Models\Banner;
use App\Observers\BannerObserver;
use App\Services\SettingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // ── Observers ─────────────────────────────────────────────────────────
        Banner::observe(BannerObserver::class);

        // ── API Rate Limiter ──────────────────────────────────────────────────
        // 60 requests per minute per IP address.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        // ── View Composer — shared layout data ────────────────────────────────
        // Injects company, contact, and social settings into the public layout
        // so every page has navigation/footer data without each controller needing to pass it.
        View::composer('layouts.app', function ($view): void {
            $settings = app(SettingService::class);
            $view->with([
                'layoutSettings' => [
                    'company' => $settings->getGroup('company'),
                    'contact' => $settings->getGroup('contact'),
                    'social' => $settings->getGroup('social'),
                ],
            ]);
        });
    }
}
