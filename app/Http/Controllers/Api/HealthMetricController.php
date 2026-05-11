<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthMetric;
use Illuminate\Http\Request;

class HealthMetricController extends Controller
{
    /** GET /api/health-metrics */
    public function index(Request $request)
    {
        return response()->json(
            HealthMetric::with('user')
                ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
                ->orderBy('record_date', 'desc')->get()
        );
    }

    /** POST /api/health-metrics */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'             => 'required|exists:users,id',
            'record_date'         => 'required|date',
            'weight'              => 'nullable|numeric',
            'height'              => 'nullable|numeric',
            'body_fat_percentage' => 'nullable|numeric',
            'muscle_mass_kg'      => 'nullable|numeric',
        ]);

        $metric = HealthMetric::create($data);
        return response()->json($metric->load('user'), 201);
    }

    /** GET /api/health-metrics/{id} */
    public function show($id)
    {
        return response()->json(HealthMetric::with('user')->findOrFail($id));
    }

    /** PUT /api/health-metrics/{id} */
    public function update(Request $request, $id)
    {
        $metric = HealthMetric::findOrFail($id);
        $data = $request->validate([
            'record_date'         => 'sometimes|date',
            'weight'              => 'nullable|numeric',
            'height'              => 'nullable|numeric',
            'body_fat_percentage' => 'nullable|numeric',
            'muscle_mass_kg'      => 'nullable|numeric',
        ]);

        $metric->update($data);
        return response()->json($metric->fresh());
    }

    /** DELETE /api/health-metrics/{id} */
    public function destroy($id)
    {
        HealthMetric::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa chỉ số sức khỏe']);
    }
}
