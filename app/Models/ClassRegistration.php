<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRegistration extends Model
{
    use \App\Traits\LogsActivity;

    protected $fillable = ['user_id', 'class_id', 'registration_date', 'status'];

    protected $casts = ['registration_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gymClass()
    {
        return $this->belongsTo(GymClass::class, 'class_id');
    }
}
