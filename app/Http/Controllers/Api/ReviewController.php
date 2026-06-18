<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Vet;
use App\Models\PetStore;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'type'    => 'required|in:vet,pet_store',
            'id'      => 'required|integer',
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $map = ['vet' => Vet::class, 'pet_store' => PetStore::class];
        $type = $map[$data['type']];

        $review = Review::updateOrCreate(
            [
                'user_id'         => $request->user()->id,
                'reviewable_type' => $type,
                'reviewable_id'   => $data['id'],
            ],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null]
        );

        $this->updateRating($type, $data['id']);

        return response()->json($review->load('user:id,name,avatar'), 201);
    }

    public function destroy(Request $request, $id)
    {
        $review = Review::where('user_id', $request->user()->id)->findOrFail($id);
        $type = $review->reviewable_type;
        $itemId = $review->reviewable_id;
        $review->delete();
        $this->updateRating($type, $itemId);
        return response()->json(['message' => 'Avis supprimé.']);
    }

    private function updateRating(string $type, int $id): void
    {
        $model = $type::find($id);
        if (!$model) return;
        $avg = Review::where('reviewable_type', $type)->where('reviewable_id', $id)->avg('rating');
        $count = Review::where('reviewable_type', $type)->where('reviewable_id', $id)->count();
        $model->update(['rating' => round($avg, 2), 'reviews_count' => $count]);
    }
}