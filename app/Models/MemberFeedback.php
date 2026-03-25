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
        'user_id', 'trainer_id', 'class_id', 'rating', 'comment', 'title', 'email', 'ai_sentiment', 'ai_score',
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
}
