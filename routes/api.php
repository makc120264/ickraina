<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes for Orders
|--------------------------------------------------------------------------
| This file is designed to be dropped into a Laravel application's routes.
| It defines three endpoints with custom auth and validation middleware.
| Middleware aliases referenced here must be registered in app/Http/Kernel.php:
|
|   protected $routeMiddleware = [
|       'api.auth' => \App\Http\Middleware\ApiAuth::class,
|       'validate.json' => \App\Http\Middleware\ValidateJson::class,
|   ];
|
*/

Route::middleware(['api', 'api.auth'])->group(function () {
    // Create order
    Route::post('/orders', [OrderController::class, 'store'])->middleware('validate.json');

    // Update status
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware('validate.json');

    // List with filtering
    Route::get('/orders', [OrderController::class, 'index']);
});
