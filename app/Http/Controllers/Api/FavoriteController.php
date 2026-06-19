<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ToggleFavoriteRequest;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\PetStore;
use App\Models\Vet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()
            ->favorites()
            ->with('favoritable')
            ->latest()
            ->limit(300)
            ->get()
            ->filter(fn (Favorite $favorite) => $favorite->favoritable !== null)
            ->values();

        return response()->json($favorites);
    }

    public function toggle(ToggleFavoriteRequest $request)
    {
        $data = $request->validated();
        $modelClass = $this->modelClass($data['type']);
        $target = $this->findFavoritable($modelClass, (int) $data['id']);

        $favorited = DB::transaction(function () use ($request, $target) {
            $existing = Favorite::query()
                ->where('user_id', $request->user()->id)
                ->where('favoritable_type', $target::class)
                ->where('favoritable_id', $target->getKey())
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->delete();
                return false;
            }

            Favorite::query()->create([
                'user_id' => $request->user()->id,
                'favoritable_type' => $target::class,
                'favoritable_id' => $target->getKey(),
            ]);

            return true;
        });

        return response()->json(['favorited' => $favorited]);
    }

    private function modelClass(string $type): string
    {
        return match ($type) {
            'listing' => Listing::class,
            'vet' => Vet::class,
            'pet_store' => PetStore::class,
        };
    }

    private function findFavoritable(string $modelClass, int $id): Model
    {
        $query = $modelClass::query()->whereKey($id);

        if ($modelClass === Listing::class) {
            $query->where('is_active', true);
        }

        if (in_array($modelClass, [Vet::class, PetStore::class], true)) {
            $query->where('is_active', true);
        }

        return $query->firstOrFail();
    }
}
