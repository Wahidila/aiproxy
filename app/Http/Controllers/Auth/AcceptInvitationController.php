<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class AcceptInvitationController extends Controller
{
    /**
     * Show the set-password form for an invitation.
     */
    public function show(string $token): View|RedirectResponse
    {
        $invitation = UserInvitation::where('token', $token)->first();

        if (! $invitation) {
            return redirect()->route('login')
                ->with('status', 'Link undangan tidak valid.');
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('status', 'Undangan ini sudah digunakan. Silakan login.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('status', 'Link undangan sudah kedaluwarsa. Hubungi admin untuk undangan baru.');
        }

        return view('auth.accept-invitation', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    /**
     * Create the user account from the invitation.
     */
    public function store(Request $request, string $token): RedirectResponse
    {
        $invitation = UserInvitation::where('token', $token)->first();

        if (! $invitation || $invitation->isAccepted() || $invitation->isExpired()) {
            return redirect()->route('login')
                ->with('status', 'Link undangan tidak valid atau sudah kedaluwarsa.');
        }

        // Check if email already registered
        if (User::where('email', $invitation->email)->exists()) {
            $invitation->markAccepted();
            return redirect()->route('login')
                ->with('status', 'Email sudah terdaftar. Silakan login.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $invitation->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
        ]);

        // Mark email as verified since admin invited them
        $user->email_verified_at = now();
        $user->save();

        $invitation->markAccepted();

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Selamat datang! Akun Anda berhasil dibuat.');
    }
}
