<?php

use App\Http\Middleware\EnsureAdminAccess;
use App\Http\Middleware\EnsureKioskAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The app runs behind nginx-proxy, which terminates TLS and forwards
        // plain HTTP over the internal Docker network. Without this, Laravel
        // sees the request as insecure and generates http:// URLs, which the
        // proxy then redirects back to https:// — an infinite redirect loop,
        // plus mixed-content failures in Filament.
        // Trusting all proxies is safe because the container publishes no host
        // port in production: the proxy is its only possible client.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'kiosk' => EnsureKioskAccess::class,
            'admin.access' => EnsureAdminAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
