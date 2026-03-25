<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    /** GET /api/checkins */
    public function index(Request $request)
    {
        $checkins = Checkin::with(['user', 'branch'])
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->date, fn($q) => $q->whereDate('check_in_at', $request->date))
            ->latest('check_in_at')
            ->paginate($request->per_page ?? 20);
        return response()->json($checkins);
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
