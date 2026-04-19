<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SystemSettingController extends Controller
{
    /** GET /api/system-settings */
    public function index(Request $request)
    {
        return response()->json(
            SystemSetting::when($request->group, fn($q) => $q->where('setting_group', $request->group))->get()
        );
    }

    /** POST /api/system-settings/bulk-update */
    public function bulkUpdate(Request $request)
    {
        $user = $request->user();
        
        // 1. Kiểm tra quyền Admin dựa trên Name hoặc Relationship Role
        if (!$user || !$user->role || $user->role->role_name !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền thực hiện chức năng này.'], 403);
        }

        // 2. Kiểm tra mật khẩu xác nhận
        if (!$request->has('password') || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Mật khẩu xác nhận không chính xác.'], 403);
        }

        // 3. Validation dữ liệu cấu hình
        $request->validate([
            'settings' => 'required|array',
            'settings.*.setting_key' => 'required|string',
            'settings.*.setting_value' => 'nullable|string',
        ]);

        // 4. Cập nhật đồng bộ các cài đặt
        DB::beginTransaction();
        try {
            foreach ($request->settings as $setting) {
                SystemSetting::updateOrCreate(
                    ['setting_key' => $setting['setting_key']],
                    ['setting_value' => $setting['setting_value']]
                );
            }
            DB::commit();
            return response()->json([
                'message' => 'Cập nhật cấu hình hệ thống thành công.',
                'data' => SystemSetting::all()
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Đã xảy ra lỗi hệ thống khi cập nhật: ' . $e->getMessage()], 500);
        }
    }

    /** POST /api/system-settings */
    public function store(Request $request)
    {
        $data = $request->validate([
            'setting_key'   => 'required|string|max:100|unique:system_settings',
            'setting_value' => 'nullable|string',
            'setting_group' => 'nullable|string|max:100',
            'description'   => 'nullable|string',
        ]);
        return response()->json(SystemSetting::create($data), 201);
    }

    /** GET /api/system-settings/{id} */
    public function show($id)
    {
        return response()->json(SystemSetting::findOrFail($id));
    }

    /** PUT /api/system-settings/{id} */
    public function update(Request $request, $id)
    {
        $setting = SystemSetting::findOrFail($id);
        $data = $request->validate([
            'setting_value' => 'nullable|string',
            'setting_group' => 'nullable|string|max:100',
            'description'   => 'nullable|string',
        ]);
        $setting->update($data);
        return response()->json($setting);
    }

    /** DELETE /api/system-settings/{id} */
    public function destroy($id)
    {
        SystemSetting::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa cài đặt']);
    }
}
