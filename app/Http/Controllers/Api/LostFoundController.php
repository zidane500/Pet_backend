<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LostFound;
use Illuminate\Http\Request;

class LostFoundController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 12), 1), 24);

        $query = LostFound::with('user:id,name,avatar')
            ->where('is_resolved', false);

        if ($request->type)    $query->where('type', $request->type);
        if ($request->species) $query->where('species', $request->species);
        if ($request->city)    $query->where('last_seen_location', 'ilike', '%'.$request->city.'%');

        return response()->json(
            $query->orderByDesc('created_at')->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'               => 'required|in:lost,found',
            'animal_name'        => 'nullable|string|max:100',
            'species'            => 'required|string|max:50',
            'breed'              => 'nullable|string|max:100',
            'color'              => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'last_seen_location' => 'required|string|max:255',
            'latitude'           => 'nullable|numeric',
            'longitude'          => 'nullable|numeric',
            'date_lost_found'    => 'required|date',
            'photos'             => 'nullable|array',
            'contact_phone'      => 'nullable|string|max:20',
        ]);

        $report = $request->user()->lostFoundReports()->create($data);

        return response()->json($report, 201);
    }

    public function update(Request $request, $id)
    {
        $report = LostFound::where('user_id', $request->user()->id)->findOrFail($id);
        $report->update($request->validate([
            'description'   => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'photos'        => 'nullable|array',
        ]));
        return response()->json($report);
    }

    public function destroy(Request $request, $id)
    {
        LostFound::where('user_id', $request->user()->id)->findOrFail($id)->delete();
        return response()->json(['message' => 'Signalement supprimé.']);
    }

    public function resolve(Request $request, $id)
    {
        $report = LostFound::where('user_id', $request->user()->id)->findOrFail($id);
        $report->update(['is_resolved' => true]);
        return response()->json(['message' => 'Marqué comme résolu.']);
    }
}