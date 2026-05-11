<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthMetric extends Model
{
    use \App\Traits\LogsActivity;

    protected $fillable = [
        'user_id', 'record_date', 'weight', 'height', 'body_fat_percentage', 'muscle_mass_kg', 'bmi',
    ];

    protected $casts = ['record_date' => 'date'];

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->weight > 0 && $model->height > 0) {
                $heightInMeters = $model->height / 100;
                $model->bmi = round($model->weight / ($heightInMeters * $heightInMeters), 2);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function calculateBmi()
    {
        if ($this->height > 0) {
            // Công thức: Cân nặng (kg) / [Chiều cao (m) ^ 2]
            $heightInMeters = $this->height / 100;
            return round($this->weight / ($heightInMeters * $heightInMeters), 2);
        }
        return 0;
    }
    // Lấy bản ghi sức khỏe ngay trước bản ghi hiện tại của cùng 1 User
    public function previousRecord()
    {
        return self::where('user_id', $this->user_id)
                ->where('record_date', '<', $this->record_date)
                ->orderBy('record_date', 'desc')
                ->first();
    }
}
