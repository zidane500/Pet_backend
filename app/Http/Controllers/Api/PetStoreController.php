<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PetStore;
use Illuminate\Http\Request;

class PetStoreController extends Controller
{
    public function index(Request $request)
    {
        $query = PetStore::where('is_active', true);

        if ($request->city)   $query->where('city', $request->city);
        if ($request->region) $query->where('region', $request->region);
        if ($request->search) $query->where('store_name', 'ilike', '%'.$request->search.'%');

        return response()->json(
            $query->orderByDesc('is_verified')
                  ->orderByDesc('rating')
                  ->paginate(12)
        );
    }

    public function show($id)
    {
        $store = PetStore::with(['reviews.user:id,name,avatar'])->findOrFail($id);
        return response()->json($store);
    }
}