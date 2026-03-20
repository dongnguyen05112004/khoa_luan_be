<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiRecommendation;
use Illuminate\Http\Request;

class AiRecommendationController extends Controller
{
    /** GET /api/ai-recommendations */
    public function index(Request $request)
    {
        return response()->json(
            AiRecommendation::with('user')
                ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
                ->when($request->type, fn($q) => $q->where('recommendation_type', $request->type))
                ->latest()->paginate($request->per_page ?? 20)
        );
    }

    /** POST /api/ai-recommendations */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'             => 'required|exists:users,id',
            'recommendation_type' => 'nullable|string|max:100',
            'ai_diagnosis'        => 'nullable|string',
            'title'               => 'nullable|string',
            'ai_suggestions'      => 'nullable|string',
            'is_system_created'   => 'nullable|boolean',
            'created_at_custom'   => 'nullable|date',
            'ai_next'             => 'nullable|date',
        ]);
        return response()->json(AiRecommendation::create($data)->load('user'), 201);
    }

    /** GET /api/ai-recommendations/{id} */
    public function show($id)
    {
        return response()->json(AiRecommendation::with('user')->findOrFail($id));
    }

    /** PUT /api/ai-recommendations/{id} */
    public function update(Request $request, $id)
    {
        $rec = AiRecommendation::findOrFail($id);
        $data = $request->validate([
            'recommendation_type' => 'nullable|string|max:100',
            'ai_diagnosis'        => 'nullable|string',
            'title'               => 'nullable|string',
            'ai_suggestions'      => 'nullable|string',
            'ai_next'             => 'nullable|date',
        ]);
        $rec->update($data);
        return response()->json($rec);
    }

    /** DELETE /api/ai-recommendations/{id} */
    public function destroy($id)
    {
        AiRecommendation::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa gợi ý AI']);
    }
}
