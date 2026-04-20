<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    /**
     * Send a new invitation.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        $email = $request->email;

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', "Email {$email} sudah terdaftar sebagai user.");
        }

        // Check if there's already a pending invitation
        $existing = UserInvitation::forEmail($email)->pending()->first();
        if ($existing) {
            return redirect()->route('admin.users.index')
                ->with('error', "Sudah ada undangan pending untuk {$email}. Gunakan tombol resend.");
        }

        // Create invitation
        $invitation = UserInvitation::create([
            'name' => $request->name,
            'email' => $email,
            'invited_by' => $request->user()->id,
        ]);

        // Load relationship for email template
        $invitation->load('invitedBy');

        // Send email (with try-catch to avoid 500 if mail fails)
        try {
            Mail::to($email)->send(new InvitationMail($invitation));
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('warning', "Undangan dibuat tapi email gagal dikirim: {$e->getMessage()}");
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Undangan berhasil dikirim ke {$email}.");
    }

    /**
     * Resend an existing invitation.
     */
    public function resend(UserInvitation $invitation): RedirectResponse
    {
        if ($invitation->isAccepted()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Undangan ini sudah diterima.');
        }

        // Refresh token and extend expiry
        $invitation->refreshToken();

        // Load relationship for email template
        $invitation->load('invitedBy');

        // Resend email (with try-catch to avoid 500 if mail fails)
        try {
            Mail::to($invitation->email)->send(new InvitationMail($invitation));
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('warning', "Token diperbarui tapi email gagal dikirim: {$e->getMessage()}");
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Undangan berhasil dikirim ulang ke {$invitation->email}.");
    }
}
