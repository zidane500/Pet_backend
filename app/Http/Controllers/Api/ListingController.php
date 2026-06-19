<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Http\Requests\StoreListingRequest;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 12), 1), 24);

        $query = Listing::with('user:id,name,avatar,city,is_verified')
            ->where('is_active', true)
            ->where('status', 'active');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->boolean('adoptable')) {
            $query->where('type', 'adoption');
        }

        if ($request->filled('species')) {
            $query->where('species', $request->input('species'));
        }

        if ($request->filled('city')) {
            $query->where('city', $request->input('city'));
        }

        if ($request->filled('region')) {
            $query->where('region', $request->input('region'));
        }

        if ($request->filled('is_vaccinated')) {
            $query->where('is_vaccinated', $request->boolean('is_vaccinated'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', $search)
                    ->orWhere('description', 'ilike', $search)
                    ->orWhere('species', 'ilike', $search)
                    ->orWhere('breed', 'ilike', $search);
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        $query->orderByDesc('is_premium');

        match ($request->input('sort', 'newest')) {
            'oldest' => $query->orderBy('created_at'),
            'priceAsc' => $query->orderByRaw('price IS NULL, price ASC')->orderByDesc('created_at'),
            'priceDesc' => $query->orderByRaw('price IS NULL, price DESC')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };

        return response()->json($query->paginate($perPage));
    }

    public function show($id)
    {
        $listing = Listing::with('user:id,name,avatar,city,phone,is_verified')
            ->findOrFail($id);

        $listing->incrementViews();

        return response()->json($listing);
    }

    public function store(StoreListingRequest $request)
    {
        $data = $request->validated();

        $listing = $request->user()->listings()->create([
            ...$data,
            'status'      => $data['status'] ?? 'active',
            'is_active'   => true,
            'views_count' => 0,
            'expires_at'  => now()->addDays(30),
        ]);

        return response()->json($listing, 201);
    }

    public function update(Request $request, $id)
    {
        $listing = Listing::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'title'         => 'sometimes|string|max:200',
            'description'   => 'nullable|string',
            'type'          => 'sometimes|in:adoption,vente,perdu,trouve,accouplement,conseils',
            'status'        => 'sometimes|in:active,paused,sold,adopted,expired,pending',
            'price'         => 'nullable|numeric|min:0',
            'age_months'    => 'nullable|integer|min:0|max:600',
            'city'          => 'nullable|string|max:100',
            'region'        => 'nullable|string|max:100',
            'photos'        => 'nullable|array',
            'contact_phone' => 'nullable|string|max:20',
            'is_active'     => 'boolean',
            'expires_at'    => 'nullable|date',
        ]);

        $listing->update($data);

        return response()->json($listing);
    }

    public function destroy(Request $request, $id)
    {
        $listing = Listing::where('user_id', $request->user()->id)->findOrFail($id);
        $listing->delete();
        return response()->json(['message' => 'Annonce supprimée.']);
    }

    public function myListings(Request $request)
    {
        $listings = $request->user()->listings()
            ->withCount(['favorites', 'messages'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json($listings);
    }
}