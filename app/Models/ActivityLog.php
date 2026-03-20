<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'old_values', 'new_values', 'ip_address'];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
