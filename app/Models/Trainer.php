<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trainer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'branch_id', 'specialization', 'experience', 'description', 'pt_rate', 'max_sessions',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function classes()
    {
        return $this->hasMany(GymClass::class, 'trainer_id');
    }

    public function ptContracts()
    {
        return $this->hasMany(PtContract::class);
    }

    public function ptBookings()
    {
        return $this->hasMany(PtBooking::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(MemberFeedback::class);
    }

}
