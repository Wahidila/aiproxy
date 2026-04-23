<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with('user')->withCount('replies')->latest();

        if ($request->filled('status') && in_array($request->status, SupportTicket::STATUSES)) {
            $query->ofStatus($request->status);
        }

        if ($request->filled('category') && in_array($request->category, SupportTicket::CATEGORIES)) {
            $query->ofCategory($request->category);
        }

        $tickets = $query->paginate(20)->withQueryString();

        $openCount = SupportTicket::where('status', SupportTicket::STATUS_OPEN)->count();
        $inProgressCount = SupportTicket::where('status', SupportTicket::STATUS_IN_PROGRESS)->count();

        return view('admin.support.index', compact('tickets', 'openCount', 'inProgressCount'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['replies.user', 'user']);

        return view('admin.support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'is_admin' => true,
        ]);

        // Auto-set to in_progress when admin replies to an open ticket
        if ($ticket->isOpen()) {
            $ticket->markInProgress();
        }

        return redirect()->route('admin.support.show', $ticket)
            ->with('success', 'Balasan berhasil dikirim.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', SupportTicket::STATUSES),
        ]);

        $ticket->update(['status' => $request->status]);

        $statusLabel = SupportTicket::STATUS_LABELS[$request->status] ?? $request->status;

        return redirect()->route('admin.support.show', $ticket)
            ->with('success', "Status ticket diubah menjadi \"{$statusLabel}\".");
    }
}
