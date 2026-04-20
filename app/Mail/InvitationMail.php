<?php

namespace App\Mail;

use App\Models\UserInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public UserInvitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Anda diundang untuk bergabung di ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
            with: [
                'invitation' => $this->invitation,
                'acceptUrl' => $this->invitation->getAcceptUrl(),
                'appName' => config('app.name'),
                'expiresAt' => $this->invitation->expires_at->format('d M Y, H:i'),
            ],
        );
    }
}
