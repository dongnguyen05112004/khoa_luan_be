<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    /** GET /api/system-settings */
    public function index(Request $request)
    {
        return response()->json(
            SystemSetting::when($request->group, fn($q) => $q->where('setting_group', $request->group))->get()
        );
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
