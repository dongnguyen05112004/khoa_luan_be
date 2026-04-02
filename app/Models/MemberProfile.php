<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberProfile extends Model
{
    protected $fillable = [
        'user_id', 'date_of_birth', 'join_date', 'profile_picture', 'membership_type', 'emergency_contact', 'health_notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date'     => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function memberProfile()
    {
        return $this->hasOne(MemberProfile::class);
    }
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }
    public function healthMetrics()
    {
        return $this->hasMany(HealthMetric::class, 'user_id', 'user_id');
    }
}
