<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use \App\Traits\LogsActivity;

    protected $fillable = ['role_name'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

}
