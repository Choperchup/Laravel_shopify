<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureHostParam;
use App\Http\Middleware\EnsureShopifyHmac;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Chỉ định nghĩa alias tùy chỉnh nếu cần, nhưng không ghi đè VerifyShopify
        $middleware->alias([
            'ensure.hmac' => \App\Http\Middleware\EnsureShopifyHmac::class,
            'ensure.host' => \App\Http\Middleware\EnsureHostParam::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
