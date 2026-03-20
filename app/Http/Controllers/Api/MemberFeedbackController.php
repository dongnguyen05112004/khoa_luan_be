<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberFeedback;
use Illuminate\Http\Request;

class MemberFeedbackController extends Controller
{
    /** GET /api/member-feedbacks */
    public function index(Request $request)
    {
        return response()->json(
            MemberFeedback::with(['user', 'trainer.user', 'gymClass'])
                ->when($request->trainer_id, fn($q) => $q->where('trainer_id', $request->trainer_id))
                ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
                ->latest()->get()
        );
    }

    /** POST /api/member-feedbacks */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'trainer_id'   => 'nullable|exists:trainers,id',
            'class_id'     => 'nullable|exists:classes,id',
            'rating'       => 'nullable|integer|min:1|max:5',
            'email'        => 'nullable|string',
            'ai_sentiment' => 'nullable|string',
            'ai_score'     => 'nullable|numeric',
        ]);
        return response()->json(MemberFeedback::create($data)->load(['user', 'trainer.user', 'gymClass']), 201);
    }

    /** GET /api/member-feedbacks/{id} */
    public function show($id)
    {
        return response()->json(MemberFeedback::with(['user', 'trainer.user', 'gymClass'])->findOrFail($id));
    }

    /** PUT /api/member-feedbacks/{id} */
    public function update(Request $request, $id)
    {
        $fb = MemberFeedback::findOrFail($id);
        $data = $request->validate([
            'rating'       => 'nullable|integer|min:1|max:5',
            'email'        => 'nullable|string',
            'ai_sentiment' => 'nullable|string',
            'ai_score'     => 'nullable|numeric',
        ]);
        $fb->update($data);
        return response()->json($fb);
    }

    /** DELETE /api/member-feedbacks/{id} */
    public function destroy($id)
    {
        MemberFeedback::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa phản hồi']);
    }
}
