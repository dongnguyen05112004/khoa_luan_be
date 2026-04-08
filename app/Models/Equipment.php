<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Trạng thái thiết bị:
     *  active      = Đang sử dụng
     *  maintenance = Đang bảo trì
     *  broken      = Hỏng / Ngừng sử dụng
     */
    const STATUS_ACTIVE      = 'active';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_BROKEN      = 'broken';

    const STATUSES = [self::STATUS_ACTIVE, self::STATUS_MAINTENANCE, self::STATUS_BROKEN];

    protected $fillable = [
        'equipment_name',
        'serial_number',
        'branch_id',
        'purchase_date',
        'status',
        'location',
        'type',
        'brand',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    // ──── Relations ────────────────────────────────────────────────

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function maintenances()
    {
        return $this->hasMany(EquipmentMaintenance::class);
    }

    /** Lần bảo trì gần nhất */
    public function latestMaintenance()
    {
        return $this->hasOne(EquipmentMaintenance::class)->latestOfMany('maintenance_date');
    }

    /** Lịch bảo trì sắp tới (is_periodic = true, next_maintenance_date >= hôm nay) */
    public function upcomingMaintenance()
    {
        return $this->hasMany(EquipmentMaintenance::class)
                    ->where('is_periodic', true)
                    ->whereNotNull('next_maintenance_date')
                    ->where('next_maintenance_date', '>=', now()->toDateString())
                    ->orderBy('next_maintenance_date');
    }

    // ──── Scopes ───────────────────────────────────────────────────

    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOfBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}

