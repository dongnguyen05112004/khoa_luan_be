<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberSubscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'plan_id', 'promotion_id', 'start_date', 'end_date', 'price', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(MembershipPlan::class, 'plan_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'subscription_id');
    }

    // Lấy các gói tập sắp hết hạn trong 7 ngày tới
    public function scopeExpiringSoon($query)
    {
        return $query->where('status', 'active')
                    ->whereBetween('end_date', [now(), now()->addDays(7)]);
    }
    // Kiểm tra xem đăng ký này có dùng khuyến mãi không
    public function getHasPromotionAttribute()
    {
        return !is_null($this->promotion_id);
    }
}
