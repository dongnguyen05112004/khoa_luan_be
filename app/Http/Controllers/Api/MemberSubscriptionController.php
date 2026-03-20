<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberSubscription;
use Illuminate\Http\Request;

class MemberSubscriptionController extends Controller
{
    /** GET /api/member-subscriptions */
    public function index(Request $request)
    {
        $subs = MemberSubscription::with(['user', 'plan', 'promotion'])
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->expiring_soon, function ($q) {
                $q->where('status', 'active')
                  ->whereBetween('end_date', [now(), now()->addDays(7)]);
            })
            ->latest()
            ->paginate($request->per_page ?? 20);
        return response()->json($subs);
    }

    /** POST /api/member-subscriptions */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'plan_id'      => 'required|exists:membership_plans,id',
            'promotion_id' => 'nullable|exists:promotions,id',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after:start_date',
            'price'        => 'nullable|numeric|min:0',
            'status'       => 'nullable|in:active,expired,cancelled',
        ]);
        return response()->json(MemberSubscription::create($data)->load(['user', 'plan', 'promotion']), 201);
    }

    /** GET /api/member-subscriptions/{id} */
    public function show($id)
    {
        return response()->json(MemberSubscription::with(['user', 'plan', 'promotion', 'payments'])->findOrFail($id));
    }

    /** PUT /api/member-subscriptions/{id} */
    public function update(Request $request, $id)
    {
        $sub = MemberSubscription::findOrFail($id);
        $data = $request->validate([
            'plan_id'      => 'sometimes|exists:membership_plans,id',
            'promotion_id' => 'nullable|exists:promotions,id',
            'start_date'   => 'sometimes|date',
            'end_date'     => 'sometimes|date',
            'price'        => 'nullable|numeric|min:0',
            'status'       => 'nullable|in:active,expired,cancelled',
        ]);
        $sub->update($data);
        return response()->json($sub->load(['plan', 'promotion']));
    }

    /** DELETE /api/member-subscriptions/{id} */
    public function destroy($id)
    {
        MemberSubscription::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa đăng ký gói tập']);
    }
}
