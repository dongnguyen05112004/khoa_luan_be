<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PtBooking;
use Illuminate\Http\Request;

class PtBookingController extends Controller
{
    /** GET /api/pt-bookings */
    public function index(Request $request)
    {
        $bookings = PtBooking::with(['contract.user', 'trainer.user'])
            ->when($request->trainer_id, fn($q) => $q->where('trainer_id', $request->trainer_id))
            ->when($request->contract_id, fn($q) => $q->where('contract_id', $request->contract_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date, fn($q) => $q->whereDate('schedule_time', $request->date))
            ->get();
        return response()->json($bookings);
    }

    /** POST /api/pt-bookings */
    public function store(Request $request)
    {
        $data = $request->validate([
            'contract_id'   => 'required|exists:pt_contracts,id',
            'trainer_id'    => 'required|exists:trainers,id',
            'schedule_time' => 'required|date',
            'status'        => 'nullable|in:pending,confirmed,done,cancelled',
            'notes'         => 'nullable|string',
        ]);
        return response()->json(PtBooking::create($data)->load(['contract.user', 'trainer.user']), 201);
    }

    /** GET /api/pt-bookings/{id} */
    public function show($id)
    {
        return response()->json(PtBooking::with(['contract.user', 'trainer.user'])->findOrFail($id));
    }

    /** PUT /api/pt-bookings/{id} */
    public function update(Request $request, $id)
    {
        $booking = PtBooking::findOrFail($id);
        $data = $request->validate([
            'schedule_time' => 'sometimes|date',
            'status'        => 'sometimes|in:pending,confirmed,done,cancelled',
            'notes'         => 'nullable|string',
        ]);
        $booking->update($data);
        return response()->json($booking);
    }

    /** DELETE /api/pt-bookings/{id} */
    public function destroy($id)
    {
        PtBooking::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa lịch hẹn PT']);
    }
}
