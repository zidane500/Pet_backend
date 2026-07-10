<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Listing;
use App\Models\Message;
use App\Models\LostFound;
use App\Models\Product;
use App\Models\Order;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  STATISTIQUES GLOBALES
    // ─────────────────────────────────────────────────────────────

    public function stats(): JsonResponse
    {
        $totalUsers     = User::count();
        $activeUsers    = User::where('is_active', true)->count();
        $bannedUsers    = User::where('is_active', false)->count();
        $totalListings  = Listing::count();
        $activeListings = Listing::where('is_active', true)->count();
        $totalMessages  = Message::count();
        $totalLostFound = LostFound::count();

        // Utilisateurs par rôle
        $usersByRole = User::selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        // Nouveaux utilisateurs sur les 7 derniers jours
        $newUsersPerDay = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'day'   => $date->format('D'),
                'count' => User::whereDate('created_at', $date->toDateString())->count(),
            ];
        })->values();

        // Nouvelles annonces sur les 7 derniers jours
        $newListingsPerDay = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'day'   => $date->format('D'),
                'count' => Listing::whereDate('created_at', $date->toDateString())->count(),
            ];
        })->values();

        return response()->json([
            'users' => [
                'total'   => $totalUsers,
                'active'  => $activeUsers,
                'banned'  => $bannedUsers,
                'by_role' => $usersByRole,
            ],
            'listings' => [
                'total'  => $totalListings,
                'active' => $activeListings,
            ],
            'messages'   => $totalMessages,
            'lost_found' => $totalLostFound,
            'charts' => [
                'users_per_day'    => $newUsersPerDay,
                'listings_per_day' => $newListingsPerDay,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  CRUD UTILISATEURS
    // ─────────────────────────────────────────────────────────────

    public function users(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);

        $query = User::withCount(['listings', 'messages', 'favorites'])
            ->orderByDesc('created_at');

        // Recherche par nom ou email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Filtre par rôle
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        return response()->json($query->paginate($perPage));
    }

    public function showUser($id): JsonResponse
    {
        $user = User::withCount(['listings', 'messages', 'favorites'])
            ->with(['listings' => fn($q) => $q->latest()->limit(5)])
            ->findOrFail($id);

        return response()->json($user);
    }

    public function createUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => ['required', Rule::in(['owner', 'vet', 'shop', 'shelter', 'breeder', 'admin'])],
            'phone'    => 'nullable|string|max:20',
            'city'     => 'nullable|string|max:100',
            'plan'     => ['nullable', Rule::in(['free', 'basic', 'premium', 'pro'])],
            'is_verified' => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
        ]);

        $user = User::create([
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'role'        => $validated['role'],
            'phone'       => $validated['phone'] ?? null,
            'city'        => $validated['city'] ?? null,
            'plan'        => $validated['plan'] ?? 'free',
            'is_verified' => $validated['is_verified'] ?? false,
            'is_active'   => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'user'    => $user,
        ], 201);
    }

    public function updateUser(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // L'admin ne peut pas se modifier son propre rôle
        if ($user->id === $request->user()->id && $request->filled('role')) {
            return response()->json([
                'message' => 'Vous ne pouvez pas modifier votre propre rôle.',
            ], 422);
        }

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:100',
            'email'       => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password'    => 'sometimes|string|min:8',
            'role'        => ['sometimes', Rule::in(['owner', 'vet', 'shop', 'shelter', 'breeder', 'admin'])],
            'phone'       => 'nullable|string|max:20',
            'city'        => 'nullable|string|max:100',
            'region'      => 'nullable|string|max:100',
            'bio'         => 'nullable|string|max:500',
            'plan'        => ['nullable', Rule::in(['free', 'basic', 'premium', 'pro'])],
            'is_verified' => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        // Si le compte est désactivé → révoque tous ses tokens
        if (isset($validated['is_active']) && !$validated['is_active']) {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Utilisateur mis à jour.',
            'user'    => $user->fresh(),
        ]);
    }

    public function deleteUser(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // L'admin ne peut pas se supprimer lui-même
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte.',
            ], 422);
        }

        // Révoque tous les tokens avant suppression
        $user->tokens()->delete();

        // Soft delete (conserve les données en base)
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    public function banUser(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas bannir votre propre compte.',
            ], 422);
        }

        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        return response()->json(['message' => 'Utilisateur banni et déconnecté.']);
    }

    public function unbanUser($id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->update(['is_active' => true]);

        // Restore si soft-deleted
        if ($user->trashed()) {
            $user->restore();
        }

        return response()->json(['message' => 'Utilisateur réactivé.']);
    }

    public function verifyUser($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['is_verified' => true]);

        return response()->json(['message' => 'Utilisateur vérifié.']);
    }

    // ─────────────────────────────────────────────────────────────
    //  CRUD ANNONCES
    // ─────────────────────────────────────────────────────────────

    public function listings(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);

        $query = Listing::with('user:id,name,email,avatar')
            ->withTrashed()
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'ilike', "%{$search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return response()->json($query->paginate($perPage));
    }

    public function deleteListing(Request $request, $id): JsonResponse
    {
        $listing = Listing::withTrashed()->findOrFail($id);
        $listing->forceDelete();

        return response()->json(['message' => 'Annonce supprimée définitivement.']);
    }

    public function toggleListingActive($id): JsonResponse
    {
        $listing = Listing::findOrFail($id);
        $listing->update(['is_active' => !$listing->is_active]);

        return response()->json([
            'message'   => $listing->is_active ? 'Annonce activée.' : 'Annonce désactivée.',
            'is_active' => $listing->is_active,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  CRUD PRODUITS (boutique) — création réservée à l'admin
    // ─────────────────────────────────────────────────────────────

    public function products(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);

        // ← withTrashed : l'admin voit aussi les produits supprimés
        // récemment (utile pour vérifier avant suppression définitive).
        $query = Product::withTrashed()->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->input('search') . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        return response()->json($query->paginate($perPage));
    }

    public function createProduct(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create([
            ...$request->validated(),
            'created_by'  => $request->user()->id,
            'is_active'   => $request->validated()['is_active'] ?? true,
            'views_count' => 0,
        ]);

        return response()->json($product, 201);
    }

    public function updateProduct(UpdateProductRequest $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());

        return response()->json($product->fresh());
    }

    public function toggleProductActive($id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);

        return response()->json([
            'message'   => $product->is_active ? 'Produit activé.' : 'Produit désactivé.',
            'is_active' => $product->is_active,
        ]);
    }

    public function deleteProduct($id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->forceDelete();

        return response()->json(['message' => 'Produit supprimé définitivement.']);
    }

    // ─────────────────────────────────────────────────────────────
    //  GESTION DES COMMANDES (paiement à la livraison)
    // ─────────────────────────────────────────────────────────────

    public function orders(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 1), 100);

        $query = Order::with(['user:id,name,email,phone', 'items'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('shipping_name', 'ilike', "%{$search}%")
                    ->orWhere('shipping_phone', 'ilike', "%{$search}%");
            });
        }

        return response()->json($query->paginate($perPage));
    }

    public function updateOrderStatus(Request $request, $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status'      => ['required', Rule::in(['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'])],
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $order->update($validated);

        return response()->json([
            'message' => 'Statut de la commande mis à jour.',
            'order'   => $order->fresh(),
        ]);
    }
}