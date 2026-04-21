<?php

namespace App\Http\Controllers;

use App\Models\TrialRequest;
use App\Models\User;
use App\Services\BrevoMailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrialRequestController extends Controller
{
    public function __construct(
        private BrevoMailService $mailer
    ) {}

    /**
     * Store a new trial request (public, no auth required).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        // Check if user already exists
        if (User::where('email', $validated['email'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email ini sudah terdaftar. Silakan login langsung.',
            ], 422);
        }

        // Check if there's already a pending trial request with this email
        $existing = TrialRequest::where('email', $validated['email'])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan trial dengan email ini sudah ada. Kami akan segera menghubungi Anda.',
            ], 422);
        }

        // Create trial request
        $trialRequest = TrialRequest::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Send notification email to admin
        $this->notifyAdmin($trialRequest);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih! Permintaan trial Anda sudah diterima. Kami akan mengirimkan undangan ke email Anda segera.',
        ]);
    }

    /**
     * Send notification email to admin about new trial request.
     */
    private function notifyAdmin(TrialRequest $trialRequest): void
    {
        try {
            $this->mailer->sendView(
                toEmail: 'contentsaya1@gmail.com',
                toName: 'Admin AIMurah',
                subject: '[AIMurah] Permintaan Trial Baru: ' . $trialRequest->name,
                view: 'emails.trial-request',
                data: [
                    'trialRequest' => $trialRequest,
                    'appName' => config('app.name'),
                    'adminUrl' => route('admin.trial-requests.index'),
                ],
            );
        } catch (\Exception $e) {
            Log::error('Failed to send trial request notification email', [
                'trial_request_id' => $trialRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
