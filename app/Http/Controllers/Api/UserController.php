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

        $recentListings = $user->listings()
            ->withCount(['favorites', 'messages'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $viewsByListing = $recentListings
            ->take(5)
            ->map(fn ($listing) => [
                'day' => mb_strlen($listing->title) > 18
                    ? mb_substr($listing->title, 0, 18) . '…'
                    : $listing->title,
                'views' => (int) $listing->views_count,
            ])
            ->values();

        $messagesByDay = collect(range(6, 0))
            ->map(function ($daysAgo) use ($user) {
                $date = now()->subDays($daysAgo);

                return [
                    'day' => $date->locale('fr')->isoFormat('dd'),
                    'count' => \App\Models\Message::where('receiver_id', $user->id)
                        ->whereDate('created_at', $date->toDateString())
                        ->count(),
                ];
            })
            ->values();

        return response()->json([
            'user'                 => $user,
            'listings_count'       => $user->listings()->count(),
            'active_listings'      => $user->listings()->where('is_active', true)->where('status', 'active')->count(),
            'total_views'          => $user->listings()->sum('views_count'),
            'unread_messages'      => \App\Models\Message::where('receiver_id', $user->id)
                                        ->where('is_read', false)->count(),
            'favorites_count'      => $user->favorites()->count(),
            'unread_notifications' => $user->unreadNotifications()->count(),
            'recent_listings'      => $recentListings,
            'views_by_listing'     => $viewsByListing,
            'messages_by_day'      => $messagesByDay,
        ]);
    }

    public function notifications(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 50);

        return response()->json(
            $request->user()
                ->notifications()
                ->latest()
                ->paginate($perPage)
        );
    }

    public function markNotificationRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification lue.']);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'Toutes les notifications sont lues.']);
    }

    public function deleteNotifications(Request $request)
    {
        $request->user()->notifications()->delete();

        return response()->json(['message' => 'Notifications supprimées.']);
    }
}