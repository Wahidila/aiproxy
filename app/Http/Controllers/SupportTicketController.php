<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->supportTickets()->withCount('replies')->latest();

        if ($request->filled('status') && in_array($request->status, SupportTicket::STATUSES)) {
            $query->ofStatus($request->status);
        }

        $tickets = $query->paginate(15)->withQueryString();

        // Stat counts for summary cards
        $userTickets = $request->user()->supportTickets();
        $openCount = (clone $userTickets)->where('status', SupportTicket::STATUS_OPEN)->count();
        $inProgressCount = (clone $userTickets)->where('status', SupportTicket::STATUS_IN_PROGRESS)->count();
        $closedCount = (clone $userTickets)->where('status', SupportTicket::STATUS_CLOSED)->count();
        $totalCount = $openCount + $inProgressCount + $closedCount;

        return view('support.index', compact('tickets', 'openCount', 'inProgressCount', 'closedCount', 'totalCount'));
    }

    public function create()
    {
        return view('support.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', SupportTicket::CATEGORIES),
            'message' => 'required|string|max:5000',
        ]);

        $ticket = $request->user()->supportTickets()->create([
            'subject' => $request->subject,
            'category' => $request->category,
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        // Create the initial message as the first reply
        $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'is_admin' => false,
        ]);

        return redirect()->route('support.show', $ticket)
            ->with('success', 'Ticket berhasil dibuat. Tim kami akan segera merespons.');
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        // Ownership check
        if ($ticket->user_id !== $request->user()->id) {
            abort(403);
        }

        $ticket->load(['replies.user', 'user']);

        return view('support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        // Ownership check
        if ($ticket->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($ticket->isClosed()) {
            return redirect()->route('support.show', $ticket)
                ->with('error', 'Ticket sudah ditutup. Tidak bisa menambah balasan.');
        }

        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'is_admin' => false,
        ]);

        // If ticket was closed or in_progress, reopen it when user replies
        if (!$ticket->isOpen()) {
            $ticket->update(['status' => SupportTicket::STATUS_OPEN]);
        }

        return redirect()->route('support.show', $ticket)
            ->with('success', 'Balasan berhasil dikirim.');
    }
}
