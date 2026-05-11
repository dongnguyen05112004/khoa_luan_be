<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymClass extends Model
{
    use \App\Traits\LogsActivity;

    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'class_name', 'trainer_id', 'branch_id', 'max_members', 'class_cost', 'description', 'schedule_date',
    ];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function registrations()
    {
        return $this->hasMany(ClassRegistration::class, 'class_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(MemberFeedback::class, 'class_id');
    }
    // Tính số lượng hội viên đã đăng ký lớp này
    public function getRegisteredCountAttribute()
    {
        return $this->registrations()->count();
    }

    // Kiểm tra xem lớp đã đầy chưa
    public function getIsFullAttribute()
    {
        return $this->registered_count >= $this->max_members;
    }
}
