<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['plan_name', 'duration_days', 'price', 'value', 'description', 'status'];

    public function subscriptions()
    {
        return $this->hasMany(MemberSubscription::class, 'plan_id');
    }

    // Chỉ lấy các gói tập đang mở bán
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    // Tính tổng doanh thu thực tế mà gói tập này đã mang lại
    public function getTotalRevenueAttribute()
    {
        // Sum cột 'price' từ các đăng ký (subscriptions) của gói này
        return $this->subscriptions()->sum('price');
    }
}
