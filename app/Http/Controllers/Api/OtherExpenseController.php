<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtherExpense;
use Illuminate\Http\Request;

class OtherExpenseController extends Controller
{
    /** GET /api/other-expenses */
    public function index(Request $request)
    {
        return response()->json(
            OtherExpense::with(['branch', 'creator'])
                ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
                ->when($request->date_from, fn($q) => $q->whereDate('expense_date', '>=', $request->date_from))
                ->when($request->date_to, fn($q) => $q->whereDate('expense_date', '<=', $request->date_to))
                ->latest('expense_date')->get()
        );
    }

    /** POST /api/other-expenses */
    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id'    => 'nullable|exists:branches,id',
            'created_by'   => 'nullable|exists:users,id',
            'expense_type' => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
        ]);
        return response()->json(OtherExpense::create($data)->load(['branch', 'creator']), 201);
    }

    /** GET /api/other-expenses/{id} */
    public function show($id)
    {
        return response()->json(OtherExpense::with(['branch', 'creator'])->findOrFail($id));
    }

    /** PUT /api/other-expenses/{id} */
    public function update(Request $request, $id)
    {
        $expense = OtherExpense::findOrFail($id);
        $data = $request->validate([
            'expense_type' => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:255',
            'amount'       => 'sometimes|numeric|min:0',
            'expense_date' => 'sometimes|date',
        ]);
        $expense->update($data);
        return response()->json($expense);
    }

    /** DELETE /api/other-expenses/{id} */
    public function destroy($id)
    {
        OtherExpense::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa chi phí']);
    }
}
