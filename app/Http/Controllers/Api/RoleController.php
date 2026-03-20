<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /** GET /api/roles */
    public function index()
    {
        return response()->json(Role::all());
    }

    /** POST /api/roles */
    public function store(Request $request)
    {
        $data = $request->validate(['role_name' => 'required|string|max:50|unique:roles']);
        return response()->json(Role::create($data), 201);
    }

    /** GET /api/roles/{id} */
    public function show($id)
    {
        return response()->json(Role::with('users')->findOrFail($id));
    }

    /** PUT /api/roles/{id} */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $data = $request->validate(['role_name' => 'sometimes|string|max:50|unique:roles,role_name,' . $id]);
        $role->update($data);
        return response()->json($role);
    }

    /** DELETE /api/roles/{id} */
    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa vai trò']);
    }
}
