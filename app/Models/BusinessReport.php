<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessReport extends Model
{
    protected $fillable = ['id_report', 'date_from_summary', 'date_from', 'amount', 'ai_diagnosis'];

    protected $casts = ['date_from_summary' => 'date'];
}
