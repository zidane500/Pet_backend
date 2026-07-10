<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Catalogue public — visible sans compte. Ne renvoie que les
     * produits actifs (is_active = true), quel que soit qui consulte.
     * La gestion complète (y compris produits masqués) se fait via
     * AdminController::products().
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 12), 1), 24);

        $query = Product::query()->where('is_active', true);

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', $search)
                    ->orWhere('description', 'ilike', $search);
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        match ($request->input('sort', 'newest')) {
            'priceAsc'  => $query->orderBy('price'),
            'priceDesc' => $query->orderByDesc('price'),
            default     => $query->orderByDesc('created_at'),
        };

        return response()->json($query->paginate($perPage));
    }

    public function show($id): JsonResponse
    {
        $product = Product::where('is_active', true)->findOrFail($id);

        $product->increment('views_count');

        return response()->json($product);
    }
}