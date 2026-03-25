<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberProfile;
use Illuminate\Http\Request;

class MemberProfileController extends Controller
{
    /** GET /api/member-profiles */
    public function index()
    {
        return response()->json(MemberProfile::with('user')->get());
    }

    /** POST /api/member-profiles */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'           => 'required|exists:users,id|unique:member_profiles',
            'date_of_birth'     => 'nullable|date',
            'join_date'         => 'nullable|date',
            'profile_picture'   => 'nullable|string|max:255',
            'membership_type'   => 'nullable|string|max:50',
            'emergency_contact' => 'nullable|string|max:100',
            'health_notes'      => 'nullable|string',
        ]);
        return response()->json(MemberProfile::create($data)->load('user'), 201);
    }

    /** GET /api/member-profiles/{id} */
    public function show($id)
    {
        return response()->json(MemberProfile::with('user')->findOrFail($id));
    }

    /** PUT /api/member-profiles/{id} */
    public function update(Request $request, $id)
    {
        $profile = MemberProfile::findOrFail($id);
        $data = $request->validate([
            'date_of_birth'     => 'nullable|date',
            'join_date'         => 'nullable|date',
            'profile_picture'   => 'nullable|string|max:255',
            'membership_type'   => 'nullable|string|max:50',
            'emergency_contact' => 'nullable|string|max:100',
            'health_notes'      => 'nullable|string',
        ]);
        $profile->update($data);
        return response()->json($profile);
    }

    /** DELETE /api/member-profiles/{id} */
    public function destroy($id)
    {
        MemberProfile::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa hồ sơ hội viên']);
    }
}
