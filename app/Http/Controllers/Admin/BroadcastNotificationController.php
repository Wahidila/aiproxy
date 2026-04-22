<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BroadcastNotification;
use Illuminate\Http\Request;

class BroadcastNotificationController extends Controller
{
    public function index()
    {
        $notifications = BroadcastNotification::with('creator')
            ->withCount('dismissals')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.broadcast-notifications.index', compact('notifications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:' . implode(',', BroadcastNotification::TYPES),
            'display_type' => 'required|in:' . implode(',', BroadcastNotification::DISPLAY_TYPES),
            'expires_at' => 'nullable|date|after:now',
        ]);

        BroadcastNotification::create([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'display_type' => $request->display_type,
            'created_by' => $request->user()->id,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('admin.broadcast-notifications.index')
            ->with('success', 'Notifikasi broadcast berhasil dibuat.');
    }

    public function update(Request $request, BroadcastNotification $broadcastNotification)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:' . implode(',', BroadcastNotification::TYPES),
            'display_type' => 'required|in:' . implode(',', BroadcastNotification::DISPLAY_TYPES),
            'expires_at' => 'nullable|date',
        ]);

        $broadcastNotification->update([
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'display_type' => $request->display_type,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('admin.broadcast-notifications.index')
            ->with('success', 'Notifikasi broadcast berhasil diperbarui.');
    }

    public function toggleActive(BroadcastNotification $broadcastNotification)
    {
        $broadcastNotification->update([
            'is_active' => !$broadcastNotification->is_active,
        ]);

        $status = $broadcastNotification->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.broadcast-notifications.index')
            ->with('success', "Notifikasi broadcast berhasil {$status}.");
    }

    public function destroy(BroadcastNotification $broadcastNotification)
    {
        $broadcastNotification->delete();

        return redirect()->route('admin.broadcast-notifications.index')
            ->with('success', 'Notifikasi broadcast berhasil dihapus.');
    }
}
