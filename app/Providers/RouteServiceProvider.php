<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware(['web', 'admin.domain'])
                ->group(base_path('routes/web.php'));

            Route::middleware(['web', 'admin.domain'])
                ->prefix('superadmin')
                ->name('superadmin.')
                ->group(base_path('routes/superadmin.php'));

            // Unified interface routes (no /admin or /user prefix) — every
            // page reachable regardless of role, gated by permission instead
            // of URL namespace.
            Route::middleware(['web', 'admin.domain', 'tenant.database'])
                ->group(base_path('routes/app.php'));

            Route::middleware(['web', 'admin.domain'])
                ->prefix('admin')
                ->group(base_path('routes/admin_routes.php'));
            // No /user prefix: these are the canonical /login, /logout —
            // route *names* (user.login, user.logout, ...) are unchanged
            // since they're referenced throughout the app; only the URL
            // loses the redundant /user segment.
            Route::middleware(['web', 'admin.domain'])
                ->group(base_path('routes/user.php'));
        });
    }
}
