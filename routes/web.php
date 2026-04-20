<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DonationController as AdminDonationController;
use App\Http\Controllers\Admin\ModelPricingController as AdminModelPricingController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authenticated user routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // API Keys
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::patch('/api-keys/{apiKey}/toggle', [ApiKeyController::class, 'toggleActive'])->name('api-keys.toggle');

    // Usage
    Route::get('/usage', [UsageController::class, 'index'])->name('usage.index');
    Route::get('/usage/export', [UsageController::class, 'export'])->name('usage.export');

    // Donations
    Route::get('/donations', [DonationController::class, 'index'])->name('donations.index');
    Route::post('/donations', [DonationController::class, 'store'])->name('donations.store');
    Route::get('/donations/history', [DonationController::class, 'history'])->name('donations.history');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Proxy control
    Route::post('/proxy/toggle-laravel', [AdminDashboardController::class, 'toggleLaravelFallback'])->name('proxy.toggle-laravel');
    Route::get('/proxy/golang-status', [AdminDashboardController::class, 'golangProxyStatus'])->name('proxy.golang-status');

    // User management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/adjust-balance', [AdminUserController::class, 'adjustBalance'])->name('users.adjust-balance');
    Route::post('/users/{user}/ban', [AdminUserController::class, 'ban'])->name('users.ban');
    Route::post('/users/{user}/unban', [AdminUserController::class, 'unban'])->name('users.unban');
    Route::delete('/users/{user}/api-keys/{apiKey}', [AdminUserController::class, 'revokeApiKey'])->name('users.revoke-key');

    // Donation management
    Route::get('/donations', [AdminDonationController::class, 'index'])->name('donations.index');
    Route::post('/donations/{donation}/approve', [AdminDonationController::class, 'approve'])->name('donations.approve');
    Route::post('/donations/{donation}/reject', [AdminDonationController::class, 'reject'])->name('donations.reject');
    Route::get('/donations/{donation}/proof', [AdminDonationController::class, 'showProof'])->name('donations.proof');

    // Model Pricing
    Route::get('/model-pricing', [AdminModelPricingController::class, 'index'])->name('model-pricing.index');
    Route::post('/model-pricing', [AdminModelPricingController::class, 'store'])->name('model-pricing.store');
    Route::patch('/model-pricing/{modelPricing}', [AdminModelPricingController::class, 'update'])->name('model-pricing.update');
    Route::delete('/model-pricing/{modelPricing}', [AdminModelPricingController::class, 'destroy'])->name('model-pricing.destroy');

    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
