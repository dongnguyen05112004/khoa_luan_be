<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /** GET /api/payments */
    public function index(Request $request)
    {
        $payments = Payment::with(['user', 'subscription.plan', 'promotion'])
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('payment_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('payment_date', '<=', $request->date_to))
            ->latest('payment_date')
            ->paginate($request->per_page ?? 20);
        return response()->json($payments);
    }

    /** POST /api/payments */
    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_number' => 'nullable|string|max:50|unique:payments',
            'user_id'        => 'required|exists:users,id',
            'subscription_id'=> 'nullable|exists:member_subscriptions,id',
            'amount'         => 'required|numeric|min:0',
            'payment_date'   => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'status'         => 'nullable|in:pending,paid,refunded',
            'promotion_id'   => 'nullable|exists:promotions,id',
            'note'           => 'nullable|string',
        ]);
        return response()->json(Payment::create($data)->load(['user', 'subscription.plan']), 201);
    }

    /** GET /api/payments/{id} */
    public function show($id)
    {
        return response()->json(Payment::with(['user', 'subscription.plan', 'promotion'])->findOrFail($id));
    }

    /** PUT /api/payments/{id} */
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $data = $request->validate([
            'status'         => 'sometimes|in:pending,paid,refunded',
            'payment_method' => 'nullable|string|max:50',
            'payment_date'   => 'nullable|date',
            'note'           => 'nullable|string',
        ]);
        $payment->update($data);
        return response()->json($payment);
    }

    /** DELETE /api/payments/{id} */
    public function destroy($id)
    {
        Payment::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa giao dịch']);
    }
}
