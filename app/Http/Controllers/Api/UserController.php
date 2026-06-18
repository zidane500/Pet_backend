<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::with(['listings' => function($q) {
            $q->where('is_active', true)->orderByDesc('created_at')->limit(6);
        }])->findOrFail($id);

        return response()->json($user->makeHidden(['email', 'phone']));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'         => 'sometimes|string|max:100',
            'phone'        => 'nullable|string|max:20',
            'city'         => 'nullable|string|max:100',
            'region'       => 'nullable|string|max:100',
            'bio'          => 'nullable|string|max:500',
            'avatar'       => 'nullable|string',
            'locale'       => 'nullable|in:fr,en,ar',
            'password'     => 'nullable|string|min:6|confirmed',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $request->user()->update($data);

        return response()->json($request->user()->fresh());
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user'            => $user,
            'listings_count'  => $user->listings()->count(),
            'active_listings' => $user->listings()->where('is_active', true)->count(),
            'total_views'     => $user->listings()->sum('views_count'),
            'unread_messages' => \App\Models\Message::where('receiver_id', $user->id)
                                    ->where('is_read', false)->count(),
            'recent_listings' => $user->listings()->orderByDesc('created_at')->limit(5)->get(),
        ]);
    }

    public function notifications(Request $request)
    {
        return response()->json(
            $request->user()->notifications()->orderByDesc('created_at')->paginate(20)
        );
    }

    public function markNotificationRead(Request $request, $id)
    {
        $notif = $request->user()->notifications()->findOrFail($id);
        $notif->markAsRead();
        return response()->json(['message' => 'Lu.']);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Toutes lues.']);
    }
}