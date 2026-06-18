<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function conversations(Request $request)
    {
        $userId = $request->user()->id;

        $conversations = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender:id,name,avatar', 'receiver:id,name,avatar'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function($msg) use ($userId) {
                return $msg->sender_id === $userId
                    ? $msg->receiver_id
                    : $msg->sender_id;
            })
            ->map(function($msgs) {
                return $msgs->first();
            })
            ->values();

        return response()->json($conversations);
    }

    public function show(Request $request, $userId)
    {
        $me = $request->user()->id;

        $messages = Message::where(function($q) use ($me, $userId) {
            $q->where('sender_id', $me)->where('receiver_id', $userId);
        })->orWhere(function($q) use ($me, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $me);
        })
        ->with(['sender:id,name,avatar'])
        ->orderBy('created_at')
        ->get();

        Message::where('sender_id', $userId)
            ->where('receiver_id', $me)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content'     => 'required|string|max:2000',
            'listing_id'  => 'nullable|exists:listings,id',
        ]);

        $message = Message::create([
            'sender_id'   => $request->user()->id,
            'receiver_id' => $data['receiver_id'],
            'content'     => $data['content'],
            'listing_id'  => $data['listing_id'] ?? null,
        ]);

        return response()->json(
            $message->load('sender:id,name,avatar'),
            201
        );
    }

    public function markRead(Request $request, $id)
    {
        $message = Message::where('receiver_id', $request->user()->id)->findOrFail($id);
        $message->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['message' => 'Lu.']);
    }
}