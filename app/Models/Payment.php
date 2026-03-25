<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'user_id', 'subscription_id', 'payable_id', 'payable_type',
        'amount', 'payment_date', 'payment_method', 'status', 'payment_confirmed', 'promotion_id', 'note',
    ];

    protected $casts = ['payment_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(MemberSubscription::class, 'subscription_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    // Chỉ lấy các thanh toán đã thành công
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Tính tổng tiền đã thu trong một khoảng thời gian
    public function scopeRevenueBetween($query, $from, $to)
    {
        return $query->where('status', 'paid')
                    ->whereBetween('payment_date', [$from, $to]);
    }
}
