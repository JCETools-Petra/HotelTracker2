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
        // This is where you register middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class, // Ensure this line is correct
            // Add other aliases here, e.g.:
            // 'anotherAlias' => \App\Http\Middleware\AnotherMiddleware::class,
        ]);

        // You can also add middleware to groups like 'web' or 'api'
        // For example:
        // $middleware->web(append: [
        //     \App\Http\Middleware\ExampleWebMiddleware::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ...
    })->create();