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
use App\Http\Controllers\Admin\BroadcastNotificationController as AdminBroadcastNotificationController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\NotificationDismissalController;
use App\Http\Controllers\SupportTicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// TEMPORARY MAINTENANCE MODE - revert to view('welcome') when done
Route::get('/', function () {
    return view('maintenance');
})->name('home');

// Public trial request (no auth required)
Route::post('/trial-request', [TrialRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('trial-request.store');

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

    // Support Tickets
    Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
    Route::get('/support/create', [SupportTicketController::class, 'create'])->name('support.create');
    Route::post('/support', [SupportTicketController::class, 'store'])->name('support.store');
    Route::get('/support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('support.reply');

    // Notification dismissal
    Route::post('/notifications/{broadcastNotification}/dismiss', [NotificationDismissalController::class, 'dismiss'])->name('notifications.dismiss');
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

    // Support Tickets (Admin)
    Route::get('/support', [AdminSupportTicketController::class, 'index'])->name('support.index');
    Route::get('/support/{ticket}', [AdminSupportTicketController::class, 'show'])->name('support.show');
    Route::post('/support/{ticket}/reply', [AdminSupportTicketController::class, 'reply'])->name('support.reply');
    Route::patch('/support/{ticket}/status', [AdminSupportTicketController::class, 'updateStatus'])->name('support.update-status');

    // Broadcast Notifications
    Route::get('/broadcast-notifications', [AdminBroadcastNotificationController::class, 'index'])->name('broadcast-notifications.index');
    Route::post('/broadcast-notifications', [AdminBroadcastNotificationController::class, 'store'])->name('broadcast-notifications.store');
    Route::patch('/broadcast-notifications/{broadcastNotification}', [AdminBroadcastNotificationController::class, 'update'])->name('broadcast-notifications.update');
    Route::post('/broadcast-notifications/{broadcastNotification}/toggle', [AdminBroadcastNotificationController::class, 'toggleActive'])->name('broadcast-notifications.toggle');
    Route::delete('/broadcast-notifications/{broadcastNotification}', [AdminBroadcastNotificationController::class, 'destroy'])->name('broadcast-notifications.destroy');
});

require __DIR__.'/auth.php';
