<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vet;
use Illuminate\Http\Request;

class VetController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 12), 1), 24);

        $query = Vet::where('is_active', true);

        if ($request->city)   $query->where('city', $request->city);
        if ($request->region) $query->where('region', $request->region);
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('clinic_name', 'ilike', '%'.$request->search.'%')
                  ->orWhere('doctor_name', 'ilike', '%'.$request->search.'%')
                  ->orWhere('speciality', 'ilike', '%'.$request->search.'%');
            });
        }

        return response()->json(
            $query->orderByDesc('is_verified')
                  ->orderByDesc('rating')
                  ->paginate($perPage)
        );
    }

    public function show($id)
    {
        $vet = Vet::with(['reviews.user:id,name,avatar'])->findOrFail($id);
        return response()->json($vet);
    }
}