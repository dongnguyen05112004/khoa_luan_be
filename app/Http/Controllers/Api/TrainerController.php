<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trainer;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    /** GET /api/trainers */
    public function index(Request $request)
    {
        $trainers = Trainer::with(['user', 'branch'])
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->get();
        return response()->json($trainers);
    }

    /** POST /api/trainers */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'        => 'required|exists:users,id|unique:trainers',
            'branch_id'      => 'nullable|exists:branches,id',
            'specialization' => 'nullable|string|max:150',
            'pt_rate'        => 'nullable|numeric|min:0',
            'max_sessions'   => 'nullable|integer|min:0',
        ]);
        return response()->json(Trainer::create($data)->load(['user', 'branch']), 201);
    }

    /** GET /api/trainers/{id} */
    public function show($id)
    {
        return response()->json(Trainer::with(['user', 'branch', 'classes', 'ptContracts'])->findOrFail($id));
    }

    /** PUT /api/trainers/{id} */
    public function update(Request $request, $id)
    {
        $trainer = Trainer::findOrFail($id);
        $data = $request->validate([
            'branch_id'      => 'nullable|exists:branches,id',
            'specialization' => 'nullable|string|max:150',
            'pt_rate'        => 'nullable|numeric|min:0',
            'max_sessions'   => 'nullable|integer|min:0',
        ]);
        $trainer->update($data);
        return response()->json($trainer->load(['user', 'branch']));
    }

    /** DELETE /api/trainers/{id} */
    public function destroy($id)
    {
        Trainer::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa huấn luyện viên']);
    }

    /** GET /api/trainers/{id}/schedule */
    public function schedule($id)
    {
        $trainer = Trainer::findOrFail($id);
        return response()->json($trainer->ptBookings()->with('contract.user')->get());
    }
}
