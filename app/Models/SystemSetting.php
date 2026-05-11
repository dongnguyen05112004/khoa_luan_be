<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use \App\Traits\LogsActivity;
    protected $fillable = ['setting_key', 'setting_value', 'setting_group', 'description'];

    /**
     * Lấy giá trị cấu hình theo key (có hỗ trợ Cache và Giải mã)
     * 
     * @param string $key 
     * @param mixed $default
     * @param bool $decrypt
     * @return mixed
     */
    public static function getValue($key, $default = null, $decrypt = false)
    {
        // Sử dụng Cache để tránh truy vấn DB liên tục
        $value = \Cache::rememberForever("sys_setting_{$key}", function() use ($key) {
            $setting = self::where('setting_key', $key)->first();
            return $setting ? $setting->setting_value : null;
        });

        if ($value === null) return $default;

        // Giải mã nếu yêu cầu
        if ($decrypt && !empty($value)) {
            try {
                return \Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value; // Trả về text thuần nếu giải mã lỗi
            }
        }

        return $value;
    }
}

