<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        $this->configureRateLimiting();
    }

    /**
     * Limiters de autenticacao (ver SECURITY_GUIDELINES.md, secao 8).
     * Excedendo o limite, o middleware `throttle:nome` responde com HTTP 429.
     */
    protected function configureRateLimiting(): void
    {
        // POST /login — 5/min por IP + e-mail (brute-force de conta).
        RateLimiter::for('login', function (Request $request) {
            $email = Str::lower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by($email . '|' . $request->ip());
        });

        // POST /register — 5/min por IP (cadastro em massa).
        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(5)->by((string) $request->ip()));

        // POST /forgot-password — 3/min por IP + e-mail (spam de recuperacao).
        RateLimiter::for('forgot-password', function (Request $request) {
            $email = Str::lower(trim((string) $request->input('email')));

            return Limit::perMinute(3)->by($email . '|' . $request->ip());
        });

        // POST /reset-password — 5/min por IP (brute-force de token).
        RateLimiter::for('reset-password', fn (Request $request) => Limit::perMinute(5)->by((string) $request->ip()));
    }
}
