<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_name', 'address', 'phone', 'capacity',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function trainers()
    {
        return $this->hasMany(Trainer::class);
    }

    public function classes()
    {
        return $this->hasMany(GymClass::class, 'branch_id');
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class);
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }

    public function otherExpenses()
    {
        return $this->hasMany(OtherExpense::class);
    }
}
