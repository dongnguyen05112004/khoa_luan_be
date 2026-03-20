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
        'amount', 'payment_date', 'payment_method', 'status', 'promotion_id', 'note',
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
}
