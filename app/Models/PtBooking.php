<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PtBooking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contract_id', 'trainer_id', 'schedule_time', 'status', 'notes',
    ];

    protected $casts = ['schedule_time' => 'datetime'];

    public function contract()
    {
        return $this->belongsTo(PtContract::class, 'contract_id');
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }
}
