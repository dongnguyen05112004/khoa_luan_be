<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipment_maintenance';

    protected $fillable = ['equipment_id', 'technician_id', 'maintenance_date', 'description', 'cost'];

    protected $casts = ['maintenance_date' => 'date'];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
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
    }
}
