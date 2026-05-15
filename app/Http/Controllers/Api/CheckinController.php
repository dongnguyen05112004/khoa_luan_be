<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CheckinController extends Controller
{
    /** GET /api/checkins */
    public function index(Request $request)
    {
        $checkins = Checkin::with(['user', 'branch'])
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->date, function ($q) use ($request) {
                $from = Carbon::parse($request->date)->startOfDay();
                $to = $from->copy()->addDay();

                $q->where('check_in_at', '>=', $from)
                    ->where('check_in_at', '<', $to);
            })
            ->latest('check_in_at')
            ->paginate($request->per_page ?? 20);
        return response()->json($checkins);
    }

    /**
     * GET /api/checkins/my
     * Lấy lịch sử check-in của hội viên đang đăng nhập
     */
    public function myCheckins(Request $request)
    {
        $user = $request->user();

        $checkins = Checkin::with(['branch'])
            ->where('user_id', $user->id)
            ->when($request->date, fn($q) => $q->whereDate('check_in_at', $request->date))
            ->latest('check_in_at')
            ->paginate($request->per_page ?? 30);

        // Thống kê nhanh
        $now = Carbon::now();
        $todayCount = Checkin::where('user_id', $user->id)
            ->whereDate('check_in_at', $now->toDateString())->count();
        $weekCount = Checkin::where('user_id', $user->id)
            ->whereBetween('check_in_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count();
        $monthCount = Checkin::where('user_id', $user->id)
            ->whereBetween('check_in_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count();
        $totalCount = Checkin::where('user_id', $user->id)->count();

        return response()->json([
            'checkins'    => $checkins,
            'today_count' => $todayCount,
            'week_count'  => $weekCount,
            'month_count' => $monthCount,
            'total_count' => $totalCount,
        ]);
    }

    /** POST /api/checkins */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'branch_id'     => 'nullable|exists:branches,id',
            'check_in_at'   => 'required|date',
            'check_out_at'  => 'nullable|date|after:check_in_at',
            'duration'      => 'nullable|integer|min:0',
            'schedule_done' => 'nullable|boolean',
            'method'        => 'nullable|in:qr,face,manual',
            'notes'         => 'nullable|string',
        ]);
        return response()->json(Checkin::create($data)->load(['user', 'branch']), 201);
    }

    /** GET /api/checkins/{id} */
    public function show($id)
    {
        return response()->json(Checkin::with(['user', 'branch'])->findOrFail($id));
    }

    /** PUT /api/checkins/{id} – checkout */
    public function update(Request $request, $id)
    {
        $checkin = Checkin::findOrFail($id);
        $data = $request->validate([
            'check_out_at'  => 'nullable|date',
            'duration'      => 'nullable|integer|min:0',
            'schedule_done' => 'nullable|boolean',
            'notes'         => 'nullable|string',
        ]);
        $checkin->update($data);
        return response()->json($checkin);
    }

    /** DELETE /api/checkins/{id} */
    public function destroy($id)
    {
        Checkin::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa check-in']);
    }
}
