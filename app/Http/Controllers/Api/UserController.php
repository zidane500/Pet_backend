<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateProfileRequest;
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

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        // ── Changement de mot de passe ─────────────────────────
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Données invalides.',
                    'errors'  => [
                        'current_password' => ['Mot de passe actuel incorrect.'],
                    ],
                ], 422);
            }
            $user->password = Hash::make($request->password);
        }

        // ── Champs profil de base ──────────────────────────────
        $user->fill($request->only([
            'name', 'phone', 'city', 'region',
            'bio', 'avatar', 'locale',
        ]));

        // ── Préférences notifications (JSON) ───────────────────
        if ($request->has('notification_preferences')) {
            $user->notification_preferences = $request->notification_preferences;
        }

        // ── Préférences confidentialité (JSON) ─────────────────
        if ($request->has('privacy')) {
            $user->privacy = $request->privacy;
        }

        $user->save();

        return response()->json([
            'message' => 'Profil mis à jour.',
            'user'    => $user->fresh(),
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $recentListings = $user->listings()
            ->withCount(['favorites', 'messages'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $viewsByListing = $recentListings
            ->map(fn ($listing) => [
                'day'   => mb_strlen($listing->title) > 18
                    ? mb_substr($listing->title, 0, 18) . '…'
                    : $listing->title,
                'views' => (int) $listing->views_count,
            ])
            ->values();

        $messagesByDay = collect(range(6, 0))
            ->map(function ($daysAgo) use ($user) {
                $date = now()->subDays($daysAgo);
                return [
                    'day'   => $date->locale('fr')->isoFormat('dd'),
                    'count' => Message::where('receiver_id', $user->id)
                        ->whereDate('created_at', $date->toDateString())
                        ->count(),
                ];
            })
            ->values();

        return response()->json([
            'user'                 => $user,
            'listings_count'       => $user->listings()->count(),
            'active_listings'      => $user->listings()
                                          ->where('is_active', true)
                                          ->where('status', 'active')
                                          ->count(),
            'total_views'          => $user->listings()->sum('views_count'),
            'unread_messages'      => Message::where('receiver_id', $user->id)
                                          ->where('is_read', false)
                                          ->count(),
            'favorites_count'      => $user->favorites()->count(),
            'unread_notifications' => $user->unreadNotifications()->count(),
            'recent_listings'      => $recentListings,
            'views_by_listing'     => $viewsByListing,
            'messages_by_day'      => $messagesByDay,
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 50);

        return response()->json(
            $request->user()
                ->notifications()
                ->latest()
                ->paginate($perPage)
        );
    }

    public function markNotificationRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification lue.']);
    }

    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'Toutes les notifications sont lues.']);
    }

    public function deleteNotifications(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return response()->json(['message' => 'Notifications supprimées.']);
    }
}