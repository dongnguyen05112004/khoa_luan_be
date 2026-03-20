<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberProfile extends Model
{
    protected $fillable = [
        'user_id', 'date_of_birth', 'join_date', 'emergency_contact', 'health_notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date'     => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
