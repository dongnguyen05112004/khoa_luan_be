<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PtContract;
use Illuminate\Http\Request;

class PtContractController extends Controller
{
    /** GET /api/pt-contracts */
    public function index(Request $request)
    {
        $contracts = PtContract::with(['user', 'trainer.user'])
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->trainer_id, fn($q) => $q->where('trainer_id', $request->trainer_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate($request->per_page ?? 20);
        return response()->json($contracts);
    }

    /** POST /api/pt-contracts */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'        => 'required|exists:users,id',
            'trainer_id'     => 'required|exists:trainers,id',
            'total_sessions' => 'required|integer|min:1',
            'used_sessions'  => 'nullable|integer|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after:start_date',
            'price'          => 'nullable|numeric|min:0',
            'status'         => 'nullable|in:active,completed,cancelled',
        ]);
        return response()->json(PtContract::create($data)->load(['user', 'trainer.user']), 201);
    }

    /** GET /api/pt-contracts/{id} */
    public function show($id)
    {
        return response()->json(PtContract::with(['user', 'trainer.user', 'bookings'])->findOrFail($id));
    }

    /** PUT /api/pt-contracts/{id} */
    public function update(Request $request, $id)
    {
        $contract = PtContract::findOrFail($id);
        $data = $request->validate([
            'used_sessions' => 'nullable|integer|min:0',
            'end_date'      => 'nullable|date',
            'price'         => 'nullable|numeric|min:0',
            'status'        => 'nullable|in:active,completed,cancelled',
        ]);
        $contract->update($data);
        return response()->json($contract);
    }

    /** DELETE /api/pt-contracts/{id} */
    public function destroy($id)
    {
        PtContract::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa hợp đồng PT']);
    }
}
