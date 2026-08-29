<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MessageController extends Controller
{
    /**
     * Get the chat list (for Admin it lists all users, usually gurus).
     */
    public function getChatList(Request $request)
    {
        $user = $request->user();
        
        // Let's get all Guru
        $gurus = User::where('role', 'guru')->get();

        $chatList = $gurus->map(function ($guru) use ($user) {
            // Get the last message between this user and guru
            $lastMessage = Message::where(function ($query) use ($user, $guru) {
                $query->where('sender_id', $user->id)->where('receiver_id', $guru->id);
            })->orWhere(function ($query) use ($user, $guru) {
                $query->where('sender_id', $guru->id)->where('receiver_id', $user->id);
            })->latest()->first();

            // Unread count (messages sent from guru to user that are unread)
            $unreadCount = Message::where('sender_id', $guru->id)
                ->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            return [
                'id' => $guru->id,
                'name' => $guru->name,
                'lastMessage' => $lastMessage ? $lastMessage->message : 'Belum ada percakapan.',
                'lastTime' => $lastMessage ? $this->formatTime($lastMessage->created_at) : '',
                'lastTimestamp' => $lastMessage ? $lastMessage->created_at : null,
                'unread' => $unreadCount,
                'online' => false,
                'avatar' => null
            ];
        });

        // Sort by last message time descending
        $chatList = $chatList->sortByDesc('lastTimestamp')->values();

        return response()->json($chatList);
    }

    /**
     * Get admin profile for Guru
     */
    public function getAdminProfile()
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        return response()->json([
            'id' => $admin->id,
            'name' => $admin->name,
            'online' => true, // Simulate online
            'avatar' => null
        ]);
    }

    /**
     * Get conversation with a specific user.
     */
    public function getConversation(Request $request, $userId)
    {
        $user = $request->user();

        // Mark messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Get messages
        $messages = Message::where(function ($query) use ($user, $userId) {
                $query->where('sender_id', $user->id)->where('receiver_id', $userId);
            })->orWhere(function ($query) use ($user, $userId) {
                $query->where('sender_id', $userId)->where('receiver_id', $user->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $formattedMessages = $messages->map(function ($msg) use ($user) {
            return [
                'id' => $msg->id,
                'text' => $msg->message,
                'time' => $this->formatTime($msg->created_at),
                'isAdmin' => $msg->sender->role === 'admin',
                'isGuru' => $msg->sender->role === 'guru',
                'isSender' => $msg->sender_id === $user->id
            ];
        });

        return response()->json($formattedMessages);
    }

    /**
     * Send a message.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_read' => false
        ]);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => [
                'id' => $message->id,
                'text' => $message->message,
                'time' => $this->formatTime($message->created_at),
                'isAdmin' => $message->sender->role === 'admin',
                'isGuru' => $message->sender->role === 'guru',
                'isSender' => true
            ]
        ], 201);
    }

    private function formatTime($datetime)
    {
        return Carbon::parse($datetime)->format('h:i A');
    }
}
