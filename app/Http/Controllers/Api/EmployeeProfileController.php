<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeProfile;
use Illuminate\Http\Request;

class EmployeeProfileController extends Controller
{
    /** GET /api/employee-profiles */
    public function index()
    {
        return response()->json(EmployeeProfile::with('user')->get());
    }

    /** POST /api/employee-profiles */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'    => 'required|exists:users,id|unique:employee_profiles',
            'hire_date'  => 'nullable|date',
            'position'   => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'salary'     => 'nullable|numeric|min:0',
        ]);
        return response()->json(EmployeeProfile::create($data)->load('user'), 201);
    }

    /** GET /api/employee-profiles/{id} */
    public function show($id)
    {
        return response()->json(EmployeeProfile::with('user')->findOrFail($id));
    }

    /** PUT /api/employee-profiles/{id} */
    public function update(Request $request, $id)
    {
        $profile = EmployeeProfile::findOrFail($id);
        $data = $request->validate([
            'hire_date'  => 'nullable|date',
            'position'   => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'salary'     => 'nullable|numeric|min:0',
        ]);
        $profile->update($data);
        return response()->json($profile);
    }

    /** DELETE /api/employee-profiles/{id} */
    public function destroy($id)
    {
        EmployeeProfile::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa hồ sơ nhân viên']);
    }
}
