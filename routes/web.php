<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => config('app.name', 'Laravel'),
        'status' => 'ok'
    ]);
});

Route::get('/orders', function () {
    return view('orders');
});
