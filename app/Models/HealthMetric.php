<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthMetric extends Model
{
    protected $fillable = [
        'user_id', 'record_date', 'weight', 'height', 'body_fat_percentage', 'muscle_mass_kg',
    ];

    protected $casts = ['record_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
