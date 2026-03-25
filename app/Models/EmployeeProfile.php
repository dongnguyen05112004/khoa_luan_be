<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    protected $fillable = ['user_id', 'hire_date', 'position', 'department', 'salary'];

    protected $casts = ['hire_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
