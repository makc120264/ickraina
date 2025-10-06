<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(
    dirname(__DIR__)
)->withRouting(
    null,
    __DIR__ . '/../routes/web.php',
    __DIR__ . '/../routes/api.php'
)->withMiddleware(function (Middleware $middleware) {
    // Register route middleware aliases used in the project
    $middleware->alias([
        'api.auth' => \App\Http\Middleware\ApiAuth::class,
        'validate.json' => \App\Http\Middleware\ValidateJson::class,
    ]);

    // Ensure API group exists; additional middlewares can be appended if needed
    // $middleware->appendToGroup('api', [ /* ... */ ]);
})->withExceptions(function (Exceptions $exceptions) {
    // You can customize exception handling here if needed
})->create();
