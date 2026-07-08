<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Listing;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function conversations(Request $request)
    {
        $userId = (int) $request->user()->id;

        $messages = Message::query()
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->with([
                'sender:id,name,avatar,is_verified',
                'receiver:id,name,avatar,is_verified',
                'listing:id,title,type',
            ])
            ->latest()
            ->limit(500)
            ->get();

        $conversations = $messages
            ->groupBy(fn (Message $message) => $message->sender_id === $userId
                ? $message->receiver_id
                : $message->sender_id)
            ->map(function ($thread) use ($userId) {
                /** @var Message $lastMessage */
                $lastMessage = $thread->sortByDesc('created_at')->first();

                // ── Protection contre les utilisateurs supprimés ──
                $partner = $lastMessage->sender_id === $userId
                    ? $lastMessage->receiver
                    : $lastMessage->sender;

                // Si le partenaire n'existe plus, on ignore cette conversation
                if (! $partner) {
                    return null;
                }

                return [
                    'partner'      => $partner,
                    'last_message' => $lastMessage,
                    'unread_count' => $thread
                        ->where('receiver_id', $userId)
                        ->where('is_read', false)
                        ->count(),
                ];
            })
            ->filter() // supprime les null
            ->sortByDesc(fn ($conversation) => $conversation['last_message']->created_at)
            ->values();

        return response()->json($conversations);
    }

    public function show(Request $request, int $userId)
    {
        $me = (int) $request->user()->id;

        if ($me === $userId) {
            return response()->json([
                'message' => 'Conversation invalide.',
                'code'    => 'INVALID_CONVERSATION',
            ], 422);
        }

        // ── Cherche l'utilisateur même s'il est soft-deleted ──
        $partner = User::withTrashed()->find($userId);
        if (! $partner) {
            return response()->json([
                'message' => 'Utilisateur introuvable.',
            ], 404);
        }

        $messages = Message::query()
            ->where(function ($query) use ($me, $userId) {
                $query->where('sender_id', $me)->where('receiver_id', $userId);
            })
            ->orWhere(function ($query) use ($me, $userId) {
                $query->where('sender_id', $userId)->where('receiver_id', $me);
            })
            ->with([
                'sender:id,name,avatar,is_verified',
                'receiver:id,name,avatar,is_verified',
                'listing:id,title,type',
            ])
            ->oldest()
            ->limit(250)
            ->get();

        // Marquer les messages reçus comme lus
        Message::query()
            ->where('sender_id', $userId)
            ->where('receiver_id', $me)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json($messages);
    }

    public function store(StoreMessageRequest $request)
    {
        $data = $request->validated();

        $receiver = User::query()
            ->whereKey($data['receiver_id'])
            ->where('is_active', true)
            ->firstOrFail();

        if (! empty($data['listing_id'])) {
            Listing::query()
                ->whereKey($data['listing_id'])
                ->where('is_active', true)
                ->firstOrFail();
        }

        $message = DB::transaction(function () use ($request, $data, $receiver) {
            return Message::query()->create([
                'sender_id'   => $request->user()->id,
                'receiver_id' => $receiver->id,
                'content'     => $data['content'],
                'listing_id'  => $data['listing_id'] ?? null,
            ]);
        });

        return response()->json(
            $message->load([
                'sender:id,name,avatar,is_verified',
                'receiver:id,name,avatar,is_verified',
                'listing:id,title,type',
            ]),
            201
        );
    }

    public function markRead(Request $request, int $id)
    {
        $message = Message::query()
            ->where('receiver_id', $request->user()->id)
            ->findOrFail($id);

        if (! $message->is_read) {
            $message->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json(['message' => 'Message lu.']);
    }
}