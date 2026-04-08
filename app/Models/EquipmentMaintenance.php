<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipment_maintenance';

    protected $fillable = [
        'equipment_id',
        'technician_id',
        'maintenance_date',
        'description',
        'cost',
        'is_periodic',
        'interval_days',
        'next_maintenance_date',
    ];

    protected $casts = [
        'maintenance_date'      => 'date',
        'next_maintenance_date' => 'date',
        'is_periodic'           => 'boolean',
        'cost'                  => 'decimal:2',
    ];

    // ──── Hook: tự tính next_maintenance_date khi save ─────────────

    protected static function booted(): void
    {
        static::saving(function (self $m) {
            if ($m->is_periodic && $m->interval_days && $m->maintenance_date) {
                $m->next_maintenance_date = $m->maintenance_date
                    ->copy()
                    ->addDays($m->interval_days);
            } else {
                $m->next_maintenance_date = null;
            }
        });
    }

    // ──── Relations ────────────────────────────────────────────────

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
<<<<<<< HEAD
    public function scopeMonthly($query, $month, $year)
    {
        return $query->whereMonth('maintenance_date', $month)
                    ->whereYear('maintenance_date', $year);
    }
    public function scopeLatestForEquipment($query, $equipmentId)
    {
        return $query->where('equipment_id', $equipmentId)
                    ->orderBy('maintenance_date', 'desc')
                    ->first();
=======

    // ──── Scopes ───────────────────────────────────────────────────

    /** Chỉ lấy lịch bảo trì định kỳ */
    public function scopePeriodic($query)
    {
        return $query->where('is_periodic', true);
    }

    /** Lịch sắp đến hạn (trong N ngày tới) */
    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->where('is_periodic', true)
                     ->whereNotNull('next_maintenance_date')
                     ->whereBetween('next_maintenance_date', [
                         now()->toDateString(),
                         now()->addDays($days)->toDateString(),
                     ]);
>>>>>>> trong
    }
}
