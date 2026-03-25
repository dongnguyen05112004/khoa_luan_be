<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PtContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'trainer_id', 'branch_id', 'total_sessions', 'used_sessions',
        'start_date', 'end_date', 'price', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function bookings()
    {
        return $this->hasMany(PtBooking::class, 'contract_id');
    }
}
