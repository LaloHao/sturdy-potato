<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Decision;
use App\Models\User;
use App\Models\CommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = CommentNotification::where('user_id', $user->id)
            ->with(['commenter:id,name,avatar', 'decision:id,title'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json($notifications);
    }

    /**
     * Get count of unread notifications for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $count = CommentNotification::where('user_id', $user->id)
            ->where('read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a specific notification as read.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = CommentNotification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->read = true;
        $notification->save();

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        CommentNotification::where('user_id', $user->id)
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }
}
