<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminEmail;
use App\Models\ApiKey;
use App\Models\ModelPricing;
use App\Models\User;
use App\Models\UserInvitation;
use App\Models\WalletTransaction;
use App\Services\BrevoMailService;
use App\Services\TokenTrackingService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private TokenTrackingService $trackingService,
        private BrevoMailService $mailer,
    ) {
    }

    public function index(Request $request)
    {
        $query = User::withCount(['apiKeys', 'tokenUsages', 'donations'])
            ->with('tokenQuota');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        $pendingInvitations = UserInvitation::pending()
            ->with('invitedBy')
            ->latest()
            ->get();

        return view('admin.users.index', compact('users', 'pendingInvitations'));
    }

    public function show(User $user)
    {
        $user->load(['apiKeys', 'tokenQuota']);

        $quota = $user->getOrCreateQuota();
        $stats = $this->trackingService->getUserStats($user, 30);

        // Total lifetime spending
        $totalSpending = $user->walletTransactions()
            ->where('type', WalletTransaction::TYPE_USAGE)
            ->sum('amount');
        $totalSpending = abs($totalSpending);

        // Wallet transactions (paginated)
        $transactions = $user->walletTransactions()
            ->latest('created_at')
            ->paginate(15, ['*'], 'tx_page');

        $recentUsages = $user->tokenUsages()
            ->with('apiKey')
            ->latest('created_at')
            ->take(20)
            ->get();

        // Email history sent to this user
        $adminEmails = AdminEmail::where('user_id', $user->id)
            ->with('admin')
            ->latest('created_at')
            ->paginate(10, ['*'], 'email_page');

        return view('admin.users.show', compact('user', 'quota', 'stats', 'totalSpending', 'transactions', 'recentUsages', 'adminEmails'));
    }

    public function adjustBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|not_in:0',
            'balance_type' => 'required|in:free,paid',
            'reason' => 'required|string|max:500',
        ]);

        $quota = $user->getOrCreateQuota();
        $amount = (float) $request->amount;
        $balanceType = $request->balance_type;
        $typeLabel = $balanceType === 'free' ? 'Free Tier' : 'Top Up';

        if ($amount > 0) {
            if ($balanceType === 'free') {
                $quota->addFreeBalance(
                    $amount,
                    WalletTransaction::TYPE_ADJUSTMENT,
                    "Admin adjustment ({$typeLabel}): {$request->reason}"
                );
            } else {
                $quota->addBalance(
                    $amount,
                    WalletTransaction::TYPE_ADJUSTMENT,
                    "Admin adjustment ({$typeLabel}): {$request->reason}"
                );
            }
            $action = 'ditambahkan';
        } else {
            $absAmount = abs($amount);
            $currentBalance = $balanceType === 'free' ? $quota->free_balance : $quota->paid_balance;

            if ($currentBalance < $absAmount) {
                return redirect()->route('admin.users.show', $user)
                    ->with('error', "Saldo {$typeLabel} user tidak mencukupi untuk pengurangan ini. Saldo saat ini: Rp " . number_format($currentBalance, 0, ',', '.'));
            }

            $quota->deductBalance(
                $absAmount,
                "Admin adjustment ({$typeLabel}): {$request->reason}",
                null,
                $balanceType
            );
            $action = 'dikurangi';
        }

        $formatted = 'Rp ' . number_format(abs($amount), 0, ',', '.');
        return redirect()->route('admin.users.show', $user)
            ->with('success', "Saldo {$typeLabel} {$formatted} berhasil {$action}. Reason: {$request->reason}");
    }

    public function ban(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.users.show', $user)
                ->with('error', 'Tidak bisa ban admin user.');
        }

        $request->validate([
            'ban_reason' => 'required|string|max:500',
        ]);

        $user->ban($request->ban_reason);

        return redirect()->route('admin.users.show', $user)
            ->with('success', "User {$user->name} berhasil di-ban.");
    }

    public function unban(User $user)
    {
        $user->unban();

        return redirect()->route('admin.users.show', $user)
            ->with('success', "User {$user->name} berhasil di-unban.");
    }

    public function revokeApiKey(User $user, ApiKey $apiKey)
    {
        if ($apiKey->user_id !== $user->id) {
            abort(403);
        }

        $apiKey->delete();

        return redirect()->route('admin.users.show', $user)
            ->with('success', "API key '{$apiKey->name}' berhasil di-revoke.");
    }

    /**
     * Send a custom email to a user.
     */
    public function sendEmail(Request $request, User $user)
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        // Render the email template
        $htmlContent = view('emails.admin-contact', [
            'userName' => $user->name,
            'messageBody' => $request->body,
            'subject' => $request->subject,
            'appName' => config('app.name'),
        ])->render();

        // Send via Brevo
        $result = $this->mailer->send(
            toEmail: $user->email,
            toName: $user->name,
            subject: $request->subject,
            htmlContent: $htmlContent,
        );

        // Log the email
        AdminEmail::create([
            'admin_id' => $request->user()->id,
            'user_id' => $user->id,
            'subject' => $request->subject,
            'body' => $request->body,
            'status' => $result['success'] ? 'sent' : 'failed',
            'error_message' => $result['success'] ? null : $result['message'],
            'sent_at' => $result['success'] ? now() : null,
        ]);

        if ($result['success']) {
            return redirect()->route('admin.users.show', $user)
                ->with('success', "Email berhasil dikirim ke {$user->email}.");
        }

        return redirect()->route('admin.users.show', $user)
            ->with('error', "Gagal mengirim email: {$result['message']}");
    }

    public function export()
    {
        $users = User::with('tokenQuota')
            ->withCount(['tokenUsages', 'apiKeys'])
            ->get();

        $csv = "ID,Name,Email,Role,Saldo (IDR),Free Credit,Total Requests,API Keys,Banned,Joined\n";
        foreach ($users as $user) {
            $csv .= implode(',', [
                $user->id,
                '"' . str_replace('"', '""', $user->name) . '"',
                $user->email,
                $user->role,
                $user->tokenQuota->balance ?? 0,
                $user->tokenQuota->free_credit_claimed ? 'Yes' : 'No',
                $user->token_usages_count,
                $user->api_keys_count,
                $user->is_banned ? 'Yes' : 'No',
                $user->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
