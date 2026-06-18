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
            'folder' => 'nullable|string|in:listings,avatars,vets,stores,lost-found',
        ]);

        $folder = $request->input('folder', 'listings');
        $path   = $request->file('file')->store("uploads/{$folder}", 'public');

        return response()->json([
            'url'  => asset("storage/{$path}"),
            'path' => $path,
        ]);
    }
}