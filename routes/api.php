<?php

use App\Http\Controllers\ProxyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// AI Proxy routes - authenticated via API key, gated by Laravel fallback setting
Route::prefix('v1')->middleware(['api.fallback', 'api.key', 'api.quota', 'api.subscription'])->group(function () {
    Route::post('/chat/completions', [ProxyController::class, 'chatCompletions']);
    Route::post('/messages', [ProxyController::class, 'messages']);
    Route::post('/responses', [ProxyController::class, 'responses']);
    Route::get('/models', [ProxyController::class, 'models']);
});

// Health check (no auth required, but still gated by fallback setting)
Route::get('/v1/health', [ProxyController::class, 'health'])->middleware('api.fallback');
