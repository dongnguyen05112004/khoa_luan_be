<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /** GET /api/users */
    public function index(Request $request)
    {
        $users = User::with(['role', 'branch'])
            ->when($request->role_id, fn($q) => $q->where('role_id', $request->role_id))
            ->when($request->branch_id, fn($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->state, fn($q) => $q->where('state', $request->state))
            ->when($request->search, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('name', 'like', '%' . $request->search . '%')
                   ->orWhere('email', 'like', '%' . $request->search . '%')
                   ->orWhere('phone', 'like', '%' . $request->search . '%');
            }))
            ->paginate($request->per_page ?? 20);

        return response()->json($users);
    }

    /** POST /api/users */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string|min:8',
            'role_id'   => 'nullable|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'full_name' => 'nullable|string|max:150',
            'phone'     => 'nullable|string|max:20',
            'gender'    => 'nullable|in:male,female,other',
            'avatar'    => 'nullable|string|max:255',
            'card_number' => 'nullable|string|max:50',
            'e_number'  => 'nullable|string|max:50',
            'state'     => 'nullable|in:active,inactive,banned',
        ]);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        return response()->json($user->load(['role', 'branch']), 201);
    }

    /** GET /api/users/{id} */
    public function show($id)
    {
        $user = User::with([
            'role', 'branch', 'memberProfile', 'employeeProfile',
            'trainer', 'activeSubscription.plan',
        ])->findOrFail($id);
        return response()->json($user);
    }

    /** PUT /api/users/{id} */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $id,
            'password'  => 'sometimes|string|min:8',
            'role_id'   => 'nullable|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'full_name' => 'nullable|string|max:150',
            'phone'     => 'nullable|string|max:20',
            'gender'    => 'nullable|in:male,female,other',
            'avatar'    => 'nullable|string|max:255',
            'card_number' => 'nullable|string|max:50',
            'e_number'  => 'nullable|string|max:50',
            'state'     => 'nullable|in:active,inactive,banned',
        ]);
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);
        return response()->json($user->load(['role', 'branch']));
    }

    /** DELETE /api/users/{id} */
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa tài khoản']);
    }

    /** GET /api/users/{id}/subscriptions */
    public function subscriptions($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user->subscriptions()->with('plan', 'promotion')->get());
    }

    /** GET /api/users/{id}/checkins */
    public function checkins($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user->checkins()->with('branch')->latest()->get());
    }

    /** GET /api/users/{id}/health-metrics */
    public function healthMetrics($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user->healthMetrics()->orderBy('record_date', 'desc')->get());
    }
}
