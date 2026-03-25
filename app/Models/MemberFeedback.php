<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberFeedback extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'member_feedbacks';

    protected $fillable = [
        'user_id',
        'trainer_id',
        'class_id',
        'rating',
        'comment',
        'title',
        'email',
        'ai_sentiment',
        'ai_score',
        'ai_topic',       // Cột mới thêm (Chủ đề: Vệ sinh, PT, Máy móc)
        'ai_severity',    // Cột mới thêm (Mức độ: Low, Medium, High, Critical)
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function gymClass()
    {
        return $this->belongsTo(GymClass::class, 'class_id');
    }

    // Lấy các phản hồi khẩn cấp (High hoặc Critical)
    public function scopeUrgent($query)
    {
        return $query->whereIn('ai_severity', ['High', 'Critical']);
    }

    // Lấy phản hồi theo chủ đề (Vệ sinh, Thiết bị...)
    public function scopeByTopic($query, $topic)
    {
        return $query->where('ai_topic', $topic);
    }
}
