<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect to Google OAuth.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth callback failed: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('status', 'Gagal login dengan Google. Silakan coba lagi.');
        }

        // Check if user already exists by google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            // Existing Google user — just login
            if ($user->is_banned) {
                return redirect()->route('login')
                    ->with('status', 'Akun Anda telah diblokir.');
            }

            Auth::login($user, remember: true);
            return redirect()->intended('/dashboard');
        }

        // Check if user exists by email (registered via email before)
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Link Google account to existing user
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            if ($user->is_banned) {
                return redirect()->route('login')
                    ->with('status', 'Akun Anda telah diblokir.');
            }

            Auth::login($user, remember: true);
            return redirect()->intended('/dashboard');
        }

        // New user — create account
        $user = User::create([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'email_verified_at' => now(), // Google email is verified
        ]);

        // Auto-assign FREE subscription plan
        $user->subscribeTo('free');

        event(new Registered($user));

        Auth::login($user, remember: true);

        return redirect('/dashboard');
    }
}
