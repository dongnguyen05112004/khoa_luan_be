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
}
