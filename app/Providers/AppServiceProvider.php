<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // SECURITY: never let production boot with the default seed password.
        // Dev keeps `admin123` for convenience; production must rotate it before
        // first request, or the app refuses to serve.
        if ($this->app->environment('production')) {
            $weak = ['admin123', 'password', 'changeme', '', null];
            if (in_array(env('ADMIN_PASSWORD'), $weak, true)) {
                throw new \RuntimeException(
                    'Refusing to boot: ADMIN_PASSWORD is unset or matches a known weak default. '.
                    'Generate a strong value (e.g. `php -r "echo bin2hex(random_bytes(16));"`) and set it in .env.'
                );
            }
            if (env('APP_DEBUG') === true || env('APP_DEBUG') === 'true') {
                throw new \RuntimeException(
                    'Refusing to boot: APP_DEBUG must be false in production.'
                );
            }
        }

        // Login brute-force protection — 10 attempts per minute combining IP +
        // username so one attacker can't lock all real admins out at once.
        RateLimiter::for('admin-login', function (Request $request) {
            $key = $request->ip().'|'.strtolower((string) $request->input('username'));
            return [
                Limit::perMinute(10)->by($key),
                Limit::perMinute(30)->by($request->ip()),
            ];
        });
    }
}
