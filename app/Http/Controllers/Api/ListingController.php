<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::with('user:id,name,avatar,city,is_verified')
            ->where('is_active', true);

        if ($request->type)    $query->where('type', $request->type);
        if ($request->species) $query->where('species', $request->species);
        if ($request->city)    $query->where('city', $request->city);
        if ($request->region)  $query->where('region', $request->region);
        if ($request->search)  $query->where('title', 'ilike', '%'.$request->search.'%');

        if ($request->min_price) $query->where('price', '>=', $request->min_price);
        if ($request->max_price) $query->where('price', '<=', $request->max_price);

        $listings = $query
            ->orderByDesc('is_premium')
            ->orderByDesc('created_at')
            ->paginate(12);

        return response()->json($listings);
    }

    public function show($id)
    {
        $listing = Listing::with('user:id,name,avatar,city,phone,is_verified')
            ->findOrFail($id);

        $listing->incrementViews();

        return response()->json($listing);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:200',
            'description'   => 'nullable|string',
            'type'          => 'required|in:adoption,vente,perdu,trouve,accouplement,conseils',
            'species'       => 'nullable|string|max:50',
            'breed'         => 'nullable|string|max:100',
            'price'         => 'nullable|numeric|min:0',
            'is_free'       => 'boolean',
            'city'          => 'nullable|string|max:100',
            'region'        => 'nullable|string|max:100',
            'photos'        => 'nullable|array',
            'photos.*'      => 'string',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email',
            'is_vaccinated' => 'boolean',
            'is_sterilized' => 'boolean',
        ]);

        $listing = $request->user()->listings()->create($data);

        return response()->json($listing, 201);
    }

    public function update(Request $request, $id)
    {
        $listing = Listing::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'title'         => 'sometimes|string|max:200',
            'description'   => 'nullable|string',
            'price'         => 'nullable|numeric|min:0',
            'city'          => 'nullable|string|max:100',
            'region'        => 'nullable|string|max:100',
            'photos'        => 'nullable|array',
            'contact_phone' => 'nullable|string|max:20',
            'is_active'     => 'boolean',
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
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json($listings);
    }
}