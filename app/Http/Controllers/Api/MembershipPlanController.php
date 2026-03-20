<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    /** GET /api/membership-plans */
    public function index()
    {
        return response()->json(MembershipPlan::withCount('subscriptions')->get());
    }

    /** POST /api/membership-plans */
    public function store(Request $request)
    {
        $data = $request->validate([
            'plan_name'     => 'required|string|max:150',
            'duration_days' => 'required|integer|min:1',
            'price'         => 'required|numeric|min:0',
            'description'   => 'nullable|string',
            'status'        => 'nullable|in:active,inactive',
        ]);
        return response()->json(MembershipPlan::create($data), 201);
    }

    /** GET /api/membership-plans/{id} */
    public function show($id)
    {
        return response()->json(MembershipPlan::withCount('subscriptions')->findOrFail($id));
    }

    /** PUT /api/membership-plans/{id} */
    public function update(Request $request, $id)
    {
        $plan = MembershipPlan::findOrFail($id);
        $data = $request->validate([
            'plan_name'     => 'sometimes|string|max:150',
            'duration_days' => 'sometimes|integer|min:1',
            'price'         => 'sometimes|numeric|min:0',
            'description'   => 'nullable|string',
            'status'        => 'nullable|in:active,inactive',
        ]);
        $plan->update($data);
        return response()->json($plan);
    }

    /** DELETE /api/membership-plans/{id} */
    public function destroy($id)
    {
        MembershipPlan::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa gói tập']);
    }
}
