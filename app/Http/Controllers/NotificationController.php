<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Auth::user()
            ->notifications()
            ->where('tenant_id', app('currentTenant')->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'notifications' => $notifications->items(),
                'unread_count' => Auth::user()
                    ->notifications()
                    ->where('tenant_id', app('currentTenant')->id)
                    ->whereNull('read_at')
                    ->count(),
            ]);
        }

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        // Ensure user can only mark their own notifications as read
        if ($notification->user_id !== Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
            ]);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request)
    {
        Auth::user()
            ->notifications()
            ->where('tenant_id', app('currentTenant')->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
            ]);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'All notifications marked as read.');
    }

    public function destroy(Request $request, Notification $notification)
    {
        // Ensure user can only delete their own notifications
        if ($notification->user_id !== Auth::id()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $notification->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted',
            ]);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }

    public function getUnreadCount()
    {
        $count = Auth::user()
            ->notifications()
            ->where('tenant_id', app('currentTenant')->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }
} 