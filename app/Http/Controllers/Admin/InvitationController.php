<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInvitation;
use App\Services\BrevoMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        private BrevoMailService $mailer
    ) {}

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

        // Send email via Brevo API
        $result = $this->sendInvitationEmail($invitation);

        if ($result['success']) {
            return redirect()->route('admin.users.index')
                ->with('success', "Undangan berhasil dikirim ke {$email}.");
        }

        return redirect()->route('admin.users.index')
            ->with('warning', "Undangan dibuat tapi email gagal dikirim: {$result['message']}");
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

        // Send email via Brevo API
        $result = $this->sendInvitationEmail($invitation);

        if ($result['success']) {
            return redirect()->route('admin.users.index')
                ->with('success', "Undangan berhasil dikirim ulang ke {$invitation->email}.");
        }

        return redirect()->route('admin.users.index')
            ->with('warning', "Token diperbarui tapi email gagal dikirim: {$result['message']}");
    }

    /**
     * Send invitation email via Brevo API.
     */
    private function sendInvitationEmail(UserInvitation $invitation): array
    {
        $subject = 'Anda diundang untuk bergabung di ' . config('app.name');

        return $this->mailer->sendView(
            toEmail: $invitation->email,
            toName: $invitation->name,
            subject: $subject,
            view: 'emails.invitation',
            data: [
                'invitation' => $invitation,
                'acceptUrl' => $invitation->getAcceptUrl(),
                'appName' => config('app.name'),
                'expiresAt' => $invitation->expires_at->format('d M Y, H:i'),
            ],
        );
    }
}
