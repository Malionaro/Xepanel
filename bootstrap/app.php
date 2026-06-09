<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            '/webhooks/discord',
            '/api/*',
        ]);
        $middleware->alias([
            'api.auth' => \App\Http\Middleware\CheckApiKey::class,
            'permission' => \App\Http\Middleware\RequirePermission::class,
            'setup.ready' => \App\Http\Middleware\RedirectToSetupIfNeeded::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLanguage::class,
            \App\Http\Middleware\CheckMaintenance::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
