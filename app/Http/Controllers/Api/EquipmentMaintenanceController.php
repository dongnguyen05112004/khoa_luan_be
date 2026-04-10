<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentMaintenance;
use Illuminate\Http\Request;

/**
 * EquipmentMaintenanceController
 *
 * Quản lý lịch bảo trì thiết bị.
 * Base URL: /api/equipment-maintenance
 * Auth: Bearer Token (Sanctum)
 */
class EquipmentMaintenanceController extends Controller
{
    /**
     * GET /api/equipment-maintenance
     *
     * Danh sách lịch bảo trì. Hỗ trợ filter:
     *   ?equipment_id=1         — lọc theo thiết bị
     *   ?periodic=1             — chỉ lấy lịch định kỳ
     *   ?due_soon=7             — lịch sắp đến hạn trong N ngày
     */
    public function index(Request $request)
    {
        $query = EquipmentMaintenance::with(['equipment.branch', 'technician'])
            ->when($request->equipment_id, fn($q) => $q->where('equipment_id', $request->equipment_id))
            ->when($request->periodic,     fn($q) => $q->periodic())
            ->when($request->due_soon,     fn($q) => $q->dueSoon((int) $request->due_soon))
            ->latest('maintenance_date');

        $items = $query->get();

        return response()->json([
            'success' => true,
            'total'   => $items->count(),
            'data'    => $items,
        ]);
    }

    /**
     * POST /api/equipment-maintenance
     *
     * Tạo lịch bảo trì (1 lần hoặc định kỳ).
     *
     * Body (JSON):
     *   equipment_id       int     required  ID thiết bị
     *   technician_id      int     optional  ID kỹ thuật viên (users.id)
     *   maintenance_date   date    required  Ngày bảo trì (YYYY-MM-DD)
     *   description        string  optional  Mô tả công việc
     *   cost               number  optional  Chi phí (VNĐ)
     *   is_periodic        bool    optional  true = lịch định kỳ (default: false)
     *   interval_days      int     optional  Chu kỳ (ngày). Bắt buộc khi is_periodic=true
     *
     * Khi is_periodic=true, hệ thống tự tính next_maintenance_date = maintenance_date + interval_days
     * và tự động cập nhật status thiết bị → 'maintenance'.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'equipment_id'     => 'required|exists:equipment,id',
            'technician_id'    => 'nullable|exists:users,id',
            'maintenance_date' => 'required|date',
            'description'      => 'nullable|string|max:1000',
            'cost'             => 'nullable|numeric|min:0',
            'is_periodic'      => 'nullable|boolean',
            'interval_days'    => 'nullable|integer|min:1|max:3650|required_if:is_periodic,true',
        ]);

        $data['is_periodic']  = $data['is_periodic']  ?? false;

        $record = EquipmentMaintenance::create($data);

        // Nếu đặt lịch bảo trì → cập nhật status thiết bị sang 'maintenance'
        if ($data['is_periodic'] || (isset($data['maintenance_date']) && $data['maintenance_date'] >= now()->toDateString())) {
            Equipment::where('id', $data['equipment_id'])->update(['status' => 'maintenance']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo lịch bảo trì' . ($data['is_periodic'] ? ' định kỳ' : ''),
            'data'    => $record->load(['equipment.branch', 'technician']),
        ], 201);
    }

    /**
     * GET /api/equipment-maintenance/{id}
     *
     * Chi tiết một bản ghi bảo trì.
     */
    public function show($id)
    {
        $record = EquipmentMaintenance::with(['equipment.branch', 'technician'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $record,
        ]);
    }

    /**
     * PUT /api/equipment-maintenance/{id}
     *
     * Cập nhật lịch bảo trì.
     *
     * Body (JSON) — tất cả optional:
     *   maintenance_date   date
     *   description        string
     *   cost               number
     *   is_periodic        bool
     *   interval_days      int
     *   technician_id      int
     */
    public function update(Request $request, $id)
    {
        $record = EquipmentMaintenance::findOrFail($id);

        $data = $request->validate([
            'technician_id'    => 'nullable|exists:users,id',
            'maintenance_date' => 'sometimes|date',
            'description'      => 'nullable|string|max:1000',
            'cost'             => 'nullable|numeric|min:0',
            'is_periodic'      => 'nullable|boolean',
            'interval_days'    => 'nullable|integer|min:1|max:3650',
        ]);

        $record->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật lịch bảo trì',
            'data'    => $record->load(['equipment.branch', 'technician']),
        ]);
    }

    /**
     * DELETE /api/equipment-maintenance/{id}
     *
     * Xóa mềm bản ghi bảo trì.
     */
    public function destroy($id)
    {
        EquipmentMaintenance::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa lịch bảo trì',
        ]);
    }

    /**
     * GET /api/equipment-maintenance/due-soon
     *
     * Danh sách lịch bảo trì sắp đến hạn.
     *   ?days=N  — số ngày tới (default: 7)
     */
    public function dueSoon(Request $request)
    {
        $days  = (int) ($request->days ?? 7);
        $items = EquipmentMaintenance::with(['equipment.branch', 'technician'])
            ->dueSoon($days)
            ->orderBy('next_maintenance_date')
            ->get();

        return response()->json([
            'success' => true,
            'message' => "Lịch bảo trì sắp đến hạn trong {$days} ngày",
            'total'   => $items->count(),
            'data'    => $items,
        ]);
    }
}
