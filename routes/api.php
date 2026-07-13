<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas API básicas (se irán agregando según el SDD §7.6 y §8)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Nota: Las rutas completas de API se agregarán según el desarrollo
// - Sync PWA: /api/v1/sync/download, /api/v1/sync/upload
// - Webhooks: /api/webhooks/mercadopago, /api/webhooks/stripe, etc.