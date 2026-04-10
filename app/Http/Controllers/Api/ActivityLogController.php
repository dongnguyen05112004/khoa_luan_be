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
        $query = ActivityLog::with('user');

        // Lọc theo user_id nếu có
        $query->when($request->filled('user_id'), function ($q) use ($request) {
            $q->where('user_id', $request->user_id);
        });

        // Khu vực tìm kiếm theo tên người dùng
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->whereHas('user', function ($uQuery) use ($search) {
                $uQuery->where('name', 'like', "%{$search}%")
                       ->orWhere('full_name', 'like', "%{$search}%");
            });
        });

        // Lọc theo loại hành động
        $query->when($request->filled('action'), function ($q) use ($request) {
            $q->where('action', $request->action);
        });

        // Lọc theo khoảng ngày
        $query->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->date_from);
        });
        $query->when($request->filled('date_to'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->date_to);
        });

        // Lấy danh sách phân trang
        $paginator = $query->latest()->paginate($request->per_page ?? 10);

        // Nối thêm thống kê (stats) vào response (Dành riêng cho FE nếu cần)
        if ($request->boolean('with_stats')) {
            $today = \Carbon\Carbon::today();
            $customResponse = $paginator->toArray();
            $customResponse['stats'] = [
                'total_today' => ActivityLog::whereDate('created_at', $today)->count(),
                'high_severity' => ActivityLog::where('action', 'like', '%delete%')
                                              ->orWhere('action', 'like', '%destroy%')->count(),
            ];
            return response()->json($customResponse);
        }

        return response()->json($paginator);
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
