<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiRecommendation extends Model
{
    protected $fillable = [
        'user_id', 'recommendation_type', 'ai_diagnosis', 'title',
        'ai_suggestions', 'is_system_created', 'created_at_custom', 'ai_next',
    ];

    protected $casts = [
        'is_system_created' => 'boolean',
        'created_at_custom' => 'datetime',
        'ai_next'           => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
