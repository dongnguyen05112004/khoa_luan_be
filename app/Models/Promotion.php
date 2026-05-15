<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use \App\Traits\LogsActivity;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'code',
        'description',
        'discount',
        'start_date',
        'end_date',
        'usage_limit',
        'current_usage', // Cột mới thêm (Đếm lượt đã dùng)
        'is_active'      // Cột mới thêm (Trạng thái Bật/Tắt)
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(MemberSubscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    // Tính tỷ lệ sử dụng (Usage Rate) để AI đánh giá sức hút
    public function getUsageRateAttribute()
    {
        if ($this->usage_limit > 0) {
            // Trả về phần trăm (ví dụ: 75.5%)
            return round(($this->current_usage / $this->usage_limit) * 100, 2);
        }
        return 0;
    }

    // Kiểm tra xem mã còn hiệu lực không (cả về thời gian và số lượng)
    public function getIsValidAttribute()
    {
        $now = now();
        $timeValid = (!$this->start_date || $now->greaterThanOrEqualTo($this->start_date))
            && (!$this->end_date || $now->lessThanOrEqualTo($this->end_date));
        $usageValid = is_null($this->usage_limit) || $this->current_usage < $this->usage_limit;

        return $this->is_active && $timeValid && $usageValid;
    }
}
