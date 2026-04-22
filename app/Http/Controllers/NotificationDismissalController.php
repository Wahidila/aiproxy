<?php

namespace App\Http\Controllers;

use App\Models\BroadcastNotification;
use App\Models\NotificationDismissal;
use Illuminate\Http\Request;

class NotificationDismissalController extends Controller
{
    public function dismiss(Request $request, BroadcastNotification $broadcastNotification)
    {
        NotificationDismissal::firstOrCreate([
            'user_id' => $request->user()->id,
            'broadcast_notification_id' => $broadcastNotification->id,
        ]);

        // Return JSON for AJAX requests, redirect for regular requests
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back();
    }
}
