<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherExpense extends Model
{
    use \App\Traits\LogsActivity;

    use HasFactory, SoftDeletes;

    protected $fillable = ['branch_id', 'created_by', 'expense_type', 'description', 'amount', 'expense_date'];

    protected $casts = ['expense_date' => 'date'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    // Lọc chi phí theo tháng và năm
    public function scopeMonthly($query, $month, $year)
    {
        $from = \Carbon\Carbon::create((int) $year, (int) $month, 1);
        $to = $from->copy()->addMonth();

        return $query->where('expense_date', '>=', $from->toDateString())
                    ->where('expense_date', '<', $to->toDateString());
    }
}
