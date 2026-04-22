<?php

use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrialRequestController;
use App\Http\Controllers\UsageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DonationController as AdminDonationController;
use App\Http\Controllers\Admin\ModelPricingController as AdminModelPricingController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\TrialRequestController as AdminTrialRequestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SubscriptionPlanController as AdminSubscriptionPlanController;
use App\Http\Controllers\Admin\SubscriptionManageController as AdminSubscriptionManageController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Public trial request (no auth required)
Route::post('/trial-request', [TrialRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('trial-request.store');

// Public subscription landing page
Route::get('/subscription', function () {
    $plans = \App\Models\SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
    return view('landing.subscription', compact('plans'));
})->name('subscription.landing');

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

    // Subscriptions (user-facing)
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::post('/subscriptions/api-keys', [SubscriptionController::class, 'createApiKey'])->name('subscriptions.api-keys.create');
    Route::post('/subscriptions/api-keys/{apiKey}/toggle', [SubscriptionController::class, 'toggleApiKey'])->name('subscriptions.api-keys.toggle');
    Route::delete('/subscriptions/api-keys/{apiKey}', [SubscriptionController::class, 'deleteApiKey'])->name('subscriptions.api-keys.delete');
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

    // User invitations
    Route::post('/users/invite', [AdminInvitationController::class, 'store'])->name('users.invite');
    Route::post('/users/invite/{invitation}/resend', [AdminInvitationController::class, 'resend'])->name('users.invite.resend');

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

    // Trial Requests
    Route::get('/trial-requests', [AdminTrialRequestController::class, 'index'])->name('trial-requests.index');
    Route::post('/trial-requests/{trialRequest}/invite', [AdminTrialRequestController::class, 'invite'])->name('trial-requests.invite');
    Route::post('/trial-requests/{trialRequest}/reject', [AdminTrialRequestController::class, 'reject'])->name('trial-requests.reject');

    // Subscription Plans Management
    Route::get('/subscription-plans', [AdminSubscriptionPlanController::class, 'index'])->name('subscription-plans.index');
    Route::get('/subscription-plans/create', [AdminSubscriptionPlanController::class, 'create'])->name('subscription-plans.create');
    Route::post('/subscription-plans', [AdminSubscriptionPlanController::class, 'store'])->name('subscription-plans.store');
    Route::get('/subscription-plans/{subscriptionPlan}/edit', [AdminSubscriptionPlanController::class, 'edit'])->name('subscription-plans.edit');
    Route::put('/subscription-plans/{subscriptionPlan}', [AdminSubscriptionPlanController::class, 'update'])->name('subscription-plans.update');
    Route::delete('/subscription-plans/{subscriptionPlan}', [AdminSubscriptionPlanController::class, 'destroy'])->name('subscription-plans.destroy');

    // Subscription Management
    Route::get('/subscriptions', [AdminSubscriptionManageController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/{subscription}', [AdminSubscriptionManageController::class, 'show'])->name('subscriptions.show');
    Route::post('/subscriptions/{subscription}/approve', [AdminSubscriptionManageController::class, 'approve'])->name('subscriptions.approve');
    Route::post('/subscriptions/{subscription}/reject', [AdminSubscriptionManageController::class, 'reject'])->name('subscriptions.reject');
    Route::post('/subscriptions/{subscription}/extend', [AdminSubscriptionManageController::class, 'extend'])->name('subscriptions.extend');
    Route::post('/subscriptions/{subscription}/cancel', [AdminSubscriptionManageController::class, 'cancel'])->name('subscriptions.cancel');
});

require __DIR__.'/auth.php';
