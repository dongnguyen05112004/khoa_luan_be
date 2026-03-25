<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /** GET /api/equipment */
    public function index(Request $request)
    {
        return response()->json(
            Equipment::with('branch')
                ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->get()
        );
    }

    /** POST /api/equipment */
    public function store(Request $request)
    {
        $data = $request->validate([
            'equipment_name' => 'required|string|max:150',
            'serial_number'  => 'nullable|string|max:100|unique:equipment',
            'branch_id'      => 'nullable|exists:branches,id',
            'purchase_date'  => 'nullable|date',
            'status'         => 'nullable|in:good,maintenance,broken',
        ]);
        return response()->json(Equipment::create($data)->load('branch'), 201);
    }

    /** GET /api/equipment/{id} */
    public function show($id)
    {
        return response()->json(Equipment::with(['branch', 'maintenances'])->findOrFail($id));
    }

    /** PUT /api/equipment/{id} */
    public function update(Request $request, $id)
    {
        $eq = Equipment::findOrFail($id);
        $data = $request->validate([
            'equipment_name' => 'sometimes|string|max:150',
            'serial_number'  => 'nullable|string|max:100|unique:equipment,serial_number,' . $id,
            'branch_id'      => 'nullable|exists:branches,id',
            'purchase_date'  => 'nullable|date',
            'status'         => 'nullable|in:good,maintenance,broken',
        ]);
        $eq->update($data);
        return response()->json($eq);
    }

    /** DELETE /api/equipment/{id} */
    public function destroy($id)
    {
        Equipment::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa thiết bị']);
    }
}
