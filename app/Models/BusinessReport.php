<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessReport extends Model
{
    use \App\Traits\LogsActivity;

    protected $fillable = [
        'id_report',
        'date_from_summary',
        'date_from',
        'amount',
        'report_type',         // Cột mới thêm (Phân loại: Thiết bị, Nhân sự...)
        'raw_data_summary',    // Cột mới thêm (Dữ liệu thô JSON)
        'ai_diagnosis',
        'ai_forecast',         // Cột mới thêm (Dự báo)
        'ai_suggestions',      // Cột mới thêm (Chiến lược)
        'created_by'           // Cột mới thêm (Người tạo)
    ];
    protected $casts = [
        'date_from_summary' => 'date',
        'raw_data_summary' => 'array', // Tự động encode/decode JSON từ DB thành Array
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
