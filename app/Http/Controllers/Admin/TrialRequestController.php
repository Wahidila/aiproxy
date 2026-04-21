<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrialRequest;
use App\Models\User;
use App\Models\UserInvitation;
use App\Services\BrevoMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrialRequestController extends Controller
{
    public function __construct(
        private BrevoMailService $mailer
    ) {}

    /**
     * List all trial requests with optional status filter.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = TrialRequest::query()->latest();

        if ($status && in_array($status, ['pending', 'invited', 'rejected'])) {
            $query->ofStatus($status);
        }

        $trialRequests = $query->paginate(20);
        $pendingCount = TrialRequest::pending()->count();

        return view('admin.trial-requests.index', compact('trialRequests', 'pendingCount', 'status'));
    }

    /**
     * One-click invite: create UserInvitation and send email.
     */
    public function invite(Request $request, TrialRequest $trialRequest): RedirectResponse
    {
        if (!$trialRequest->isPending()) {
            return redirect()->route('admin.trial-requests.index')
                ->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        // Check if user already exists
        if (User::where('email', $trialRequest->email)->exists()) {
            $trialRequest->markInvited();
            return redirect()->route('admin.trial-requests.index')
                ->with('error', "Email {$trialRequest->email} sudah terdaftar sebagai user.");
        }

        // Check if there's already a pending invitation
        $existingInvitation = UserInvitation::forEmail($trialRequest->email)->pending()->first();
        if ($existingInvitation) {
            $trialRequest->markInvited();
            return redirect()->route('admin.trial-requests.index')
                ->with('warning', "Sudah ada undangan pending untuk {$trialRequest->email}.");
        }

        // Create invitation
        $invitation = UserInvitation::create([
            'name' => $trialRequest->name,
            'email' => $trialRequest->email,
            'invited_by' => $request->user()->id,
        ]);

        // Load relationship for email template
        $invitation->load('invitedBy');

        // Send invitation email
        $result = $this->sendInvitationEmail($invitation);

        // Mark trial request as invited
        $trialRequest->markInvited();

        if ($result['success']) {
            return redirect()->route('admin.trial-requests.index')
                ->with('success', "Undangan berhasil dikirim ke {$trialRequest->email}.");
        }

        return redirect()->route('admin.trial-requests.index')
            ->with('warning', "Undangan dibuat tapi email gagal dikirim: {$result['message']}");
    }

    /**
     * Reject a trial request.
     */
    public function reject(Request $request, TrialRequest $trialRequest): RedirectResponse
    {
        if (!$trialRequest->isPending()) {
            return redirect()->route('admin.trial-requests.index')
                ->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $trialRequest->markRejected($request->input('notes'));

        return redirect()->route('admin.trial-requests.index')
            ->with('success', "Permintaan trial dari {$trialRequest->name} ditolak.");
    }

    /**
     * Send invitation email via Brevo API (reuses existing invitation email template).
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
