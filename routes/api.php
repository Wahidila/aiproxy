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

// AI Proxy routes - authenticated via API key
Route::prefix('v1')->middleware(['api.key', 'api.quota'])->group(function () {
    Route::post('/chat/completions', [ProxyController::class, 'chatCompletions']);
    Route::post('/messages', [ProxyController::class, 'messages']);
    Route::post('/responses', [ProxyController::class, 'responses']);
    Route::get('/models', [ProxyController::class, 'models']);
});

// Health check (no auth required)
Route::get('/v1/health', [ProxyController::class, 'health']);
