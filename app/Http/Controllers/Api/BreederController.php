<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Breeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BreederController extends Controller
{
    // ── GET /api/breeders ──────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Breeder::where('is_active', true);

        // Recherche par nom ou ville
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', $search)
                  ->orWhere('city', 'ilike', $search)
                  ->orWhere('speciality', 'ilike', $search);
            });
        }

        // Filtre par ville
        if ($request->filled('city')) {
            $query->where('city', 'ilike', '%' . $request->city . '%');
        }

        // Filtre par spécialité
        if ($request->filled('speciality')) {
            $query->where('speciality', 'ilike', '%' . $request->speciality . '%');
        }

        // Filtre certifiés uniquement
        if ($request->boolean('certified')) {
            $query->where('is_certified', true);
        }

        // Filtre vérifiés uniquement
        if ($request->boolean('verified')) {
            $query->where('verified', true);
        }

        // Tri
        $sort = $request->get('sort', 'rating');
        $allowed = ['rating', 'name', 'created_at', 'years_experience'];
        if (in_array($sort, $allowed)) {
            $query->orderBy($sort, 'desc');
        }

        $breeders = $query->paginate(12);

        return response()->json($breeders);
    }

    // ── GET /api/breeders/{id} ─────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $breeder = Breeder::where('is_active', true)->findOrFail($id);

        return response()->json([
            'data' => array_merge($breeder->toArray(), [
                'logo_url'        => $breeder->logo_url,
                'cover_image_url' => $breeder->cover_image_url,
            ]),
        ]);
    }

    // ── POST /api/breeders ─────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'tagline'          => 'nullable|string|max:255',
            'address'          => 'required|string|max:255',
            'city'             => 'required|string|max:100',
            'phone'            => 'required|string|max:20',
            'email'            => 'nullable|email|max:255',
            'website'          => 'nullable|url|max:255',
            'description'      => 'nullable|string|max:2000',
            'speciality'       => 'nullable|string|max:255',
            'years_experience' => 'nullable|integer|min:0',
            'is_certified'     => 'nullable|boolean',
        ]);

        $breeder = Breeder::create(array_merge($validated, [
            'user_id'   => $request->user()->id,
            'is_active' => true,
        ]));

        return response()->json([
            'message' => 'Éleveur créé avec succès.',
            'data'    => $breeder,
        ], 201);
    }

    // ── PUT /api/breeders/{id} ─────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $breeder = Breeder::where('user_id', $request->user()->id)
                          ->findOrFail($id);

        $validated = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'tagline'          => 'nullable|string|max:255',
            'address'          => 'sometimes|string|max:255',
            'city'             => 'sometimes|string|max:100',
            'phone'            => 'sometimes|string|max:20',
            'email'            => 'nullable|email|max:255',
            'website'          => 'nullable|url|max:255',
            'description'      => 'nullable|string|max:2000',
            'speciality'       => 'nullable|string|max:255',
            'years_experience' => 'nullable|integer|min:0',
            'animals_sold_total'=> 'nullable|integer|min:0',
            'is_certified'     => 'nullable|boolean',
            'logo'             => 'nullable|string',
            'cover_image'      => 'nullable|string',
        ]);

        $breeder->update($validated);

        return response()->json([
            'message' => 'Éleveur mis à jour.',
            'data'    => $breeder,
        ]);
    }

    // ── DELETE /api/breeders/{id} ──────────────────────────────
    public function destroy(Request $request, int $id): JsonResponse
    {
        $breeder = Breeder::where('user_id', $request->user()->id)
                          ->findOrFail($id);

        $breeder->update(['is_active' => false]);

        return response()->json([
            'message' => 'Éleveur désactivé avec succès.',
        ]);
    }
}