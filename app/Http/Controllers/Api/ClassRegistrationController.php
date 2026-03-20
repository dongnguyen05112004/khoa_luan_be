<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRegistration;
use Illuminate\Http\Request;

class ClassRegistrationController extends Controller
{
    /** GET /api/class-registrations */
    public function index(Request $request)
    {
        $regs = ClassRegistration::with(['user', 'gymClass'])
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->get();
        return response()->json($regs);
    }

    /** POST /api/class-registrations */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'           => 'required|exists:users,id',
            'class_id'          => 'required|exists:classes,id',
            'registration_date' => 'nullable|date',
            'status'            => 'nullable|in:registered,cancelled,completed',
        ]);
        return response()->json(ClassRegistration::create($data)->load(['user', 'gymClass']), 201);
    }

    /** GET /api/class-registrations/{id} */
    public function show($id)
    {
        return response()->json(ClassRegistration::with(['user', 'gymClass'])->findOrFail($id));
    }

    /** PUT /api/class-registrations/{id} */
    public function update(Request $request, $id)
    {
        $reg = ClassRegistration::findOrFail($id);
        $data = $request->validate([
            'status' => 'required|in:registered,cancelled,completed',
        ]);
        $reg->update($data);
        return response()->json($reg);
    }

    /** DELETE /api/class-registrations/{id} */
    public function destroy($id)
    {
        ClassRegistration::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã hủy đăng ký lớp']);
    }
}
