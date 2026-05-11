<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SystemSettingController extends Controller
{
    private $sensitiveKeys = ['apiKey', 'gemini_api_key', 'groq_api_key'];

    /** GET /api/system-settings */
    public function index(Request $request)
    {
        $group = $request->group ?? 'all';
        $cacheKey = "system_settings_{$group}";

        $settings = \Cache::rememberForever($cacheKey, function() use ($request) {
            return SystemSetting::when($request->group, fn($q) => $q->where('setting_group', $request->group))->get();
        });
        
        $settings = clone $settings; 
        foreach ($settings as $setting) {
            if (in_array($setting->setting_key, $this->sensitiveKeys) && !empty($setting->setting_value)) {
                try {
                    $decrypted = \Crypt::decryptString($setting->setting_value);
                    // Masking: Chỉ hiển thị 4 ký tự đầu và 4 ký tự cuối cho chuyên nghiệp
                    $len = strlen($decrypted);
                    if ($len > 10) {
                        $setting->setting_value = substr($decrypted, 0, 4) . '****************' . substr($decrypted, -4);
                    } else {
                        $setting->setting_value = '****************';
                    }
                } catch (\Exception $e) {
                    $setting->setting_value = '****************';
                }
            }
        }
        
        return response()->json($settings);
    }


    /** POST /api/system-settings/bulk-update */
    public function bulkUpdate(Request $request)
    {
        $user = $request->user();
        
        if (!$user || !$user->role || strtolower($user->role->role_name) !== 'admin') {
            return response()->json(['message' => 'Bạn không có quyền thực hiện chức năng này.'], 403);
        }

        if (!$request->has('password') || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Mật khẩu xác nhận không chính xác.'], 403);
        }

        $settingsInput = $request->input('settings');
        if (is_string($settingsInput)) {
            $settingsInput = json_decode($settingsInput, true);
            $request->merge(['settings' => $settingsInput]);
        }

        $request->validate([
            'settings' => 'nullable|array',
            'settings.*.setting_key' => 'required|string',
            'settings.*.setting_value' => 'nullable|string',
            'logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        DB::beginTransaction();
        try {
            if ($request->has('settings') && is_array($request->settings)) {
                foreach ($request->settings as $setting) {
                    $key   = $setting['setting_key'];
                    $value = $setting['setting_value'];
                    
                    // Nếu là key nhạy cảm và giá trị gửi lên chứa '***' 
                    // (nghĩa là người dùng không sửa, vẫn giữ nguyên bản đã bị che)
                    // thì chúng ta bỏ qua không cập nhật key này.
                    if (in_array($key, $this->sensitiveKeys) && str_contains($value, '***')) {
                        continue;
                    }

                    // Mã hóa nếu là key nhạy cảm mới
                    if (in_array($key, $this->sensitiveKeys) && !empty($value)) {
                        $value = \Crypt::encryptString($value);
                    }

                    SystemSetting::updateOrCreate(
                        ['setting_key' => $key],
                        ['setting_value' => $value]
                    );
                }
            }


            if ($request->hasFile('logo_file')) {
                $file = $request->file('logo_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/logos'), $filename);
                $logoUrl = asset('uploads/logos/' . $filename);

                SystemSetting::updateOrCreate(
                    ['setting_key' => 'logo'],
                    ['setting_value' => $logoUrl]
                );
            }

            DB::commit();

            // Xóa cache để cập nhật dữ liệu mới
            \Cache::flush(); 

            return response()->json([
                'message' => 'Cập nhật cấu hình hệ thống thành công.',
                'data' => SystemSetting::all()
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Đã xảy ra lỗi hệ thống khi cập nhật: ' . $e->getMessage()], 500);
        }
    }


    /** GET /api/system-settings/{id} */
    public function show($id)
    {
        $setting = SystemSetting::findOrFail($id);
        if (in_array($setting->setting_key, $this->sensitiveKeys) && !empty($setting->setting_value)) {
            try {
                $setting->setting_value = \Crypt::decryptString($setting->setting_value);
            } catch (\Exception $e) {}
        }
        return response()->json($setting);
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
