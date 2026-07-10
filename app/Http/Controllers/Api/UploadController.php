<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file'   => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
            'folder' => 'nullable|string|in:listings,avatars,vets,stores,lost-found,products',
        ]);

        $folder = $request->input('folder', 'listings');

        // ← Le dossier "products" est réservé aux photos de la boutique,
        // qui ne peuvent être gérées que par l'admin (cohérent avec
        // ProductController/AdminController::createProduct).
        if ($folder === 'products' && $request->user()->role !== 'admin') {
            return response()->json([
                'message' => "Seul l'administrateur peut uploader des photos de produits.",
            ], 403);
        }

        $path = $request->file('file')->store("uploads/{$folder}", 'public');

        return response()->json([
            'url'  => asset("storage/{$path}"),
            'path' => $path,
        ]);
    }
}