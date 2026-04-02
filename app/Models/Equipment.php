<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['equipment_name', 'serial_number', 'branch_id', 'purchase_date', 'status'];

    protected $casts = ['purchase_date' => 'date'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function maintenances()
    {
        return $this->hasMany(EquipmentMaintenance::class);
    }
    // Lấy danh sách máy đang hỏng
    public function scopeBroken($query)
    {
        return $query->where('status', 'broken');
    }

    // Lấy danh sách máy đang bảo trì
    public function scopeInMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }
    // Tính tổng chi phí bảo trì của thiết bị này từ trước đến nay
    public function getTotalMaintenanceCostAttribute()
    {
        return $this->maintenances()->sum('cost');
    }
}
