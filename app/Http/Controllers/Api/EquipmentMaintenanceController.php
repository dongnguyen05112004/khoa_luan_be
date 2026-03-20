<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EquipmentMaintenance;
use Illuminate\Http\Request;

class EquipmentMaintenanceController extends Controller
{
    /** GET /api/equipment-maintenance */
    public function index(Request $request)
    {
        return response()->json(
            EquipmentMaintenance::with(['equipment', 'technician'])
                ->when($request->equipment_id, fn($q) => $q->where('equipment_id', $request->equipment_id))
                ->latest('maintenance_date')->get()
        );
    }

    /** POST /api/equipment-maintenance */
    public function store(Request $request)
    {
        $data = $request->validate([
            'equipment_id'    => 'required|exists:equipment,id',
            'technician_id'   => 'nullable|exists:users,id',
            'maintenance_date'=> 'required|date',
            'description'     => 'nullable|string',
            'cost'            => 'nullable|numeric|min:0',
        ]);
        return response()->json(EquipmentMaintenance::create($data)->load(['equipment', 'technician']), 201);
    }

    /** GET /api/equipment-maintenance/{id} */
    public function show($id)
    {
        return response()->json(EquipmentMaintenance::with(['equipment', 'technician'])->findOrFail($id));
    }

    /** PUT /api/equipment-maintenance/{id} */
    public function update(Request $request, $id)
    {
        $m = EquipmentMaintenance::findOrFail($id);
        $data = $request->validate([
            'maintenance_date'=> 'sometimes|date',
            'description'     => 'nullable|string',
            'cost'            => 'nullable|numeric|min:0',
        ]);
        $m->update($data);
        return response()->json($m);
    }

    /** DELETE /api/equipment-maintenance/{id} */
    public function destroy($id)
    {
        EquipmentMaintenance::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa lịch bảo trì']);
    }
}
