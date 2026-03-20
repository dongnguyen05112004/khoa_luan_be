<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /** GET /api/activity-logs */
    public function index(Request $request)
    {
        return response()->json(
            ActivityLog::with('user')
                ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
                ->latest()->paginate($request->per_page ?? 50)
        );
    }

    /** POST /api/activity-logs */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'    => 'nullable|exists:users,id',
            'action'     => 'required|string|max:255',
            'old_values' => 'nullable|string',
            'new_values' => 'nullable|string',
            'ip_address' => 'nullable|string|max:45',
        ]);
        return response()->json(ActivityLog::create($data), 201);
    }

    /** GET /api/activity-logs/{id} */
    public function show($id)
    {
        return response()->json(ActivityLog::with('user')->findOrFail($id));
    }

    /** DELETE /api/activity-logs/{id} */
    public function destroy($id)
    {
        ActivityLog::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa nhật ký']);
    }
}
