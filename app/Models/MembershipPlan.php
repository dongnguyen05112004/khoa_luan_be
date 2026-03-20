<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['plan_name', 'duration_days', 'price', 'description', 'status'];

    public function subscriptions()
    {
        return $this->hasMany(MemberSubscription::class, 'plan_id');
    }
}
