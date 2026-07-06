<?php

use Illuminate\Support\Facades\Route;

// Servir el frontend SPA desde public/
Route::get('/{any}', function () {
    $index = public_path('index.html');
    if (file_exists($index)) {
        return file_get_contents($index);
    }
    return response()->json([
        'success' => true,
        'message' => 'SICE API v1.0.0',
    ]);
})->where('any', '^(?!api).*$');
