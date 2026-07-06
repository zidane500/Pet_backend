<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shelter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShelterController extends Controller
{
    // ── GET /api/shelters ──────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Shelter::where('is_active', true);

        // Recherche par nom ou ville
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', $search)
                  ->orWhere('city', 'ilike', $search);
            });
        }

        // Filtre par ville
        if ($request->filled('city')) {
            $query->where('city', 'ilike', '%' . $request->city . '%');
        }

        // Filtre vérifiés uniquement
        if ($request->boolean('verified')) {
            $query->where('verified', true);
        }

        // Tri
        $sort = $request->get('sort', 'rating');
        $allowed = ['rating', 'name', 'created_at', 'current_animals'];
        if (in_array($sort, $allowed)) {
            $query->orderBy($sort, 'desc');
        }

        $shelters = $query->paginate(12);

        return response()->json($shelters);
    }

    // ── GET /api/shelters/{id} ─────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $shelter = Shelter::where('is_active', true)->findOrFail($id);

        return response()->json([
            'data' => array_merge($shelter->toArray(), [
                'logo_url'        => $shelter->logo_url,
                'cover_image_url' => $shelter->cover_image_url,
            ]),
        ]);
    }

    // ── POST /api/shelters ─────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'tagline'     => 'nullable|string|max:255',
            'address'     => 'required|string|max:255',
            'city'        => 'required|string|max:100',
            'phone'       => 'required|string|max:20',
            'email'       => 'nullable|email|max:255',
            'website'     => 'nullable|url|max:255',
            'description' => 'nullable|string|max:2000',
            'capacity'    => 'nullable|integer|min:0',
            'is_nonprofit'=> 'nullable|boolean',
        ]);

        $shelter = Shelter::create(array_merge($validated, [
            'user_id'   => $request->user()->id,
            'is_active' => true,
        ]));

        return response()->json([
            'message' => 'Refuge créé avec succès.',
            'data'    => $shelter,
        ], 201);
    }

    // ── PUT /api/shelters/{id} ─────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $shelter = Shelter::where('user_id', $request->user()->id)
                          ->findOrFail($id);

        $validated = $request->validate([
            'name'                 => 'sometimes|string|max:255',
            'tagline'              => 'nullable|string|max:255',
            'address'              => 'sometimes|string|max:255',
            'city'                 => 'sometimes|string|max:100',
            'phone'                => 'sometimes|string|max:20',
            'email'                => 'nullable|email|max:255',
            'website'              => 'nullable|url|max:255',
            'description'          => 'nullable|string|max:2000',
            'capacity'             => 'nullable|integer|min:0',
            'current_animals'      => 'nullable|integer|min:0',
            'volunteers_count'     => 'nullable|integer|min:0',
            'is_nonprofit'         => 'nullable|boolean',
            'logo'                 => 'nullable|string',
            'cover_image'          => 'nullable|string',
        ]);

        $shelter->update($validated);

        return response()->json([
            'message' => 'Refuge mis à jour.',
            'data'    => $shelter,
        ]);
    }

    // ── DELETE /api/shelters/{id} ──────────────────────────────
    public function destroy(Request $request, int $id): JsonResponse
    {
        $shelter = Shelter::where('user_id', $request->user()->id)
                          ->findOrFail($id);

        $shelter->update(['is_active' => false]);

        return response()->json([
            'message' => 'Refuge désactivé avec succès.',
        ]);
    }
}