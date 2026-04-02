<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'severity', 'target_type', 'target_id', 'old_values', 'new_values', 'ip_address'];

    public $timestamps = true;
    protected $casts = [
        'old_values' => 'json', // Tự động convert string trong DB thành Array khi lấy ra
        'new_values' => 'json',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record($action, $target = null, $severity = 'info', $old = null, $new = null)
    {
        self::create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'severity'    => $severity,
            'target_type' => $target ? get_class($target) : null,
            'target_id'   => $target ? $target->id : null,
            'old_values'  => $old,
            'new_values'  => $new,
            'ip_address'  => request()->ip(),
        ]);
    }
}
