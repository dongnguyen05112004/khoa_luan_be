<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /** GET /api/branches */
    public function index()
    {
        return response()->json(Branch::withCount(['users', 'trainers', 'equipment'])->get());
    }

    /** POST /api/branches */
    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_name' => 'required|string|max:150',
            'address'     => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'capacity'    => 'nullable|integer|min:1',
        ]);
        $branch = Branch::create($data);
        return response()->json($branch, 201);
    }

    /** GET /api/branches/{id} */
    public function show($id)
    {
        $branch = Branch::with(['users', 'trainers', 'equipment'])->findOrFail($id);
        return response()->json($branch);
    }

    /** PUT /api/branches/{id} */
    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $data = $request->validate([
            'branch_name' => 'sometimes|string|max:150',
            'address'     => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'capacity'    => 'nullable|integer|min:1',
        ]);
        $branch->update($data);
        return response()->json($branch);
    }

    /** DELETE /api/branches/{id} */
    public function destroy($id)
    {
        Branch::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa chi nhánh']);
    }
}
