<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\Vet;
use App\Models\PetStore;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()
            ->favorites()
            ->with('favoritable')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($favorites);
    }

    public function toggle(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:listing,vet,pet_store',
            'id'   => 'required|integer',
        ]);

        $map = [
            'listing'   => Listing::class,
            'vet'       => Vet::class,
            'pet_store' => PetStore::class,
        ];

        $type = $map[$data['type']];

        $existing = Favorite::where('user_id', $request->user()->id)
            ->where('favoritable_type', $type)
            ->where('favoritable_id', $data['id'])
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['favorited' => false]);
        }

        Favorite::create([
            'user_id'          => $request->user()->id,
            'favoritable_type' => $type,
            'favoritable_id'   => $data['id'],
        ]);

        return response()->json(['favorited' => true]);
    }
}