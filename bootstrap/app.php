<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api', // API routes will have /api prefix
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.user.active' => \App\Http\Middleware\CheckUserActive::class,
            'admin' => \App\Http\Middleware\CheckAdmin::class,
        ]);
        
        // Use custom CSRF middleware to exclude API routes
        $middleware->validateCsrfTokens(except: [
            'api/whatsapp/save-incoming-message',
            'send-message',
            'api/send-message',
            '/send-message',
            '/api/send-message',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
