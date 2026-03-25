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
    // Lấy các buổi tập đã hoàn thành (Để tính lương cho PT hoặc trừ buổi tập trong hợp đồng)
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Lấy lịch tập trong ngày hôm nay
    public function scopeToday($query)
    {
        return $query->whereDate('schedule_time', now()->toDateString());
    }
}
