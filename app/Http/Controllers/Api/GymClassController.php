<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GymClass;
use Illuminate\Http\Request;

class GymClassController extends Controller
{
    /** GET /api/classes */
    public function index(Request $request)
    {
        $classes = GymClass::with(['trainer.user', 'branch'])
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->trainer_id, fn($q) => $q->where('trainer_id', $request->trainer_id))
            ->withCount('registrations')
            ->get();
        return response()->json($classes);
    }

    /** POST /api/classes */
    public function store(Request $request)
    {
        $data = $request->validate([
            'class_name'    => 'required|string|max:150',
            'trainer_id'    => 'nullable|exists:trainers,id',
            'branch_id'     => 'nullable|exists:branches,id',
            'max_members'   => 'nullable|integer|min:1',
            'class_cost'    => 'nullable|numeric|min:0',
            'description'   => 'nullable|string',
            'schedule_date' => 'nullable|date_format:Y-m-d H:i:s',
        ]);
        return response()->json(GymClass::create($data)->load(['trainer.user', 'branch']), 201);
    }

    /** GET /api/classes/{id} */
    public function show($id)
    {
        return response()->json(GymClass::with(['trainer.user', 'branch', 'registrations.user'])->findOrFail($id));
    }

    /** PUT /api/classes/{id} */
    public function update(Request $request, $id)
    {
        $class = GymClass::findOrFail($id);
        $data = $request->validate([
            'class_name'    => 'sometimes|string|max:150',
            'trainer_id'    => 'nullable|exists:trainers,id',
            'branch_id'     => 'nullable|exists:branches,id',
            'max_members'   => 'nullable|integer|min:1',
            'class_cost'    => 'nullable|numeric|min:0',
            'description'   => 'nullable|string',
            'schedule_date' => 'nullable|date_format:Y-m-d H:i:s',
        ]);
        $class->update($data);
        return response()->json($class->load(['trainer.user', 'branch']));
    }

    /** DELETE /api/classes/{id} */
    public function destroy($id)
    {
        GymClass::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa lớp học']);
    }
}
