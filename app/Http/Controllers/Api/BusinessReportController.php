<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessReport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BusinessReportController extends Controller
{
    /** GET /api/business-reports */
    public function index(Request $request)
    {
        return response()->json(
            BusinessReport::when($request->date_from, fn($q) => $q->where('date_from_summary', '>=', $request->date_from))
                ->when($request->date_to, fn($q) => $q->where('date_from_summary', '<', Carbon::parse($request->date_to)->addDay()->toDateString()))
                ->latest('date_from_summary')->get()
        );
    }

    /** POST /api/business-reports */
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_report'         => 'nullable|string|max:50',
            'date_from_summary' => 'required|date',
            'date_from'         => 'nullable|string|max:50',
            'amount'            => 'nullable|numeric|min:0',
            'ai_diagnosis'      => 'nullable|string',
        ]);
        return response()->json(BusinessReport::create($data), 201);
    }

    /** GET /api/business-reports/{id} */
    public function show($id)
    {
        return response()->json(BusinessReport::findOrFail($id));
    }

    /** PUT /api/business-reports/{id} */
    public function update(Request $request, $id)
    {
        $report = BusinessReport::findOrFail($id);
        $data = $request->validate([
            'date_from_summary' => 'sometimes|date',
            'amount'            => 'nullable|numeric|min:0',
            'ai_diagnosis'      => 'nullable|string',
        ]);
        $report->update($data);
        return response()->json($report);
    }

    /** DELETE /api/business-reports/{id} */
    public function destroy($id)
    {
        BusinessReport::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa báo cáo']);
    }
}
