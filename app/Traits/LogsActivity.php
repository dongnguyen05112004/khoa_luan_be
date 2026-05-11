<?php

namespace App\Traits;

use App\Models\ActivityLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            static::logAction('Tạo mới', $model, 'info', null, $model->toArray());
        });

        static::updated(function ($model) {
            $newValues = $model->getChanges();
            $oldValues = array_intersect_key($model->getOriginal(), $newValues);

            // Bỏ qua các trường không cần thiết
            unset($newValues['updated_at']);
            unset($oldValues['updated_at']);

            if (count($newValues) > 0) {
                static::logAction('Cập nhật', $model, 'warning', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            static::logAction('Xóa', $model, 'danger', $model->toArray(), null);
        });
    }

    protected static function logAction($actionPrefix, $model, $severity, $oldValues, $newValues)
    {
        // Bỏ qua ghi log nếu đang chạy lệnh Terminal (như Seed DB, cronjob)
        if (app()->runningInConsole()) {
            return;
        }

        // Lấy ID người thực hiện (nếu không đăng nhập, kiểm tra xem có phải đang tạo User mới không)
        $userId = Auth::id();
        if (!$userId && $model instanceof \App\Models\User && $actionPrefix === 'Tạo mới') {
            $userId = $model->id; // Gắn chính User mới tạo làm người thực hiện
        }

        // Kiểm tra xem cấu hình hệ thống (audit) có đang bật hay không
        $auditEnabled = SystemSetting::where('setting_key', 'audit')->value('setting_value');
        if ($auditEnabled === 'false' || $auditEnabled === '0') {
            return; // Đã tắt ghi nhật ký hệ thống
        }

        // Tên class để dễ đọc (VD: App\Models\User -> User)
        $modelName = class_basename($model);

        ActivityLog::record(
            "{$actionPrefix} dữ liệu {$modelName}",
            $model,
            $severity,
            $oldValues,
            $newValues,
            $userId
        );
    }
}
