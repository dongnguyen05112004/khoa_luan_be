<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name', 'email', 'password',
        'branch_id', 'role_id', 'full_name', 'phone',
        'gender', 'avatar', 'card_number', 'e_number', 'state',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function memberProfile()
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function employeeProfile()
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function trainer()
    {
        return $this->hasOne(Trainer::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(MemberSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(MemberSubscription::class)->where('status', 'active')->latestOfMany();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    public function classRegistrations()
    {
        return $this->hasMany(ClassRegistration::class);
    }

    public function ptContracts()
    {
        return $this->hasMany(PtContract::class);
    }

    public function healthMetrics()
    {
        return $this->hasMany(HealthMetric::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(MemberFeedback::class);
    }

    public function aiRecommendations()
    {
        return $this->hasMany(AiRecommendation::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
