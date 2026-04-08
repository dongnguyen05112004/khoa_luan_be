<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;

/**
 * EquipmentController
 *
 * Quản lý thiết bị tập gym.
 * Base URL: /api/equipment
 * Auth: Bearer Token (Sanctum)
 *
 * Status values:
 *   active      → Đang sử dụng
 *   maintenance → Đang bảo trì
 *   broken      → Hỏng / Ngừng sử dụng
 */
class EquipmentController extends Controller
{
    /**
     * GET /api/equipment
     *
     * Danh sách thiết bị. Hỗ trợ filter:
     *   ?branch_id=1
     *   ?status=active|maintenance|broken
     *   ?search=tên hoặc serial
     */
    public function index(Request $request)
    {
        $items = Equipment::with(['branch', 'latestMaintenance'])
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->when($request->search,    fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('equipment_name', 'like', '%' . $request->search . '%')
                   ->orWhere('serial_number', 'like', '%' . $request->search . '%');
            }))
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'total'   => $items->count(),
            'data'    => $items,
        ]);
    }

    /**
     * POST /api/equipment
     *
     * Thêm thiết bị mới vào hệ thống.
     *
     * Body (JSON):
     *   equipment_name  string  required  Tên thiết bị
     *   serial_number   string  required  Số serial (duy nhất)
     *   branch_id       int     required  ID chi nhánh
     *   purchase_date   date    required  Ngày mua (YYYY-MM-DD)
     *   status          string  optional  active|maintenance|broken (default: active)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'equipment_name' => 'required|string|max:150',
            'serial_number'  => 'nullable|string|max:100|unique:equipment,serial_number',
            'branch_id'      => 'nullable|exists:branches,id',
            'purchase_date'  => 'nullable|date',
            'status'         => 'nullable|in:active,maintenance,broken',
            'location'       => 'nullable|string|max:150',
            'type'           => 'nullable|string|max:50',
            'brand'          => 'nullable|string|max:100',
        ]);

        $data['status'] = $data['status'] ?? Equipment::STATUS_ACTIVE;

        $equipment = Equipment::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm thiết bị mới',
            'data'    => $equipment->load('branch'),
        ], 201);
    }

    /**
     * GET /api/equipment/{id}
     *
     * Chi tiết một thiết bị kèm lịch sử bảo trì.
     */
    public function show($id)
    {
        $equipment = Equipment::with([
            'branch',
            'maintenances.technician',
            'latestMaintenance',
            'upcomingMaintenance',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $equipment,
        ]);
    }

    /**
     * PUT /api/equipment/{id}
     *
     * Cập nhật thông tin thiết bị (bao gồm cập nhật trạng thái).
     *
     * Body (JSON) — tất cả optional:
     *   equipment_name  string
     *   serial_number   string
     *   branch_id       int
     *   purchase_date   date
     *   status          active|maintenance|broken
     */
    public function update(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);

        $data = $request->validate([
            'equipment_name' => 'sometimes|string|max:150',
            'serial_number'  => 'sometimes|nullable|string|max:100|unique:equipment,serial_number,' . $id,
            'branch_id'      => 'sometimes|nullable|exists:branches,id',
            'purchase_date'  => 'sometimes|nullable|date',
            'status'         => 'sometimes|in:active,maintenance,broken',
            'location'       => 'sometimes|nullable|string|max:150',
            'type'           => 'sometimes|nullable|string|max:50',
            'brand'          => 'sometimes|nullable|string|max:100',
        ]);

        $equipment->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật thiết bị',
            'data'    => $equipment->load('branch'),
        ]);
    }

    /**
     * PATCH /api/equipment/{id}/status
     *
     * Chỉ cập nhật trạng thái thiết bị (endpoint riêng tiện lợi hơn).
     *
     * Body (JSON):
     *   status  string  required  active|maintenance|broken
     */
    public function updateStatus(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:active,maintenance,broken',
        ]);

        $equipment->update($data);

        $labels = [
            'active'      => 'Đang sử dụng',
            'maintenance' => 'Đang bảo trì',
            'broken'      => 'Hỏng / Ngừng sử dụng',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật trạng thái: ' . ($labels[$data['status']] ?? $data['status']),
            'data'    => $equipment,
        ]);
    }

    /**
     * DELETE /api/equipment/{id}
     *
     * Xóa mềm (soft delete) thiết bị. Thiết bị đã xóa không xuất hiện trong danh sách.
     */
    public function destroy($id)
    {
        Equipment::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa thiết bị khỏi hệ thống',
        ]);
    }
}
