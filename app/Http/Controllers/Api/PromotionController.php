<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /** GET /api/promotions */
    public function index()
    {
        return response()->json(Promotion::all());
    }

    /**
     * GET /api/promotions/active
     * Chỉ trả về các khuyn mãi còn hiệu lực (thời gian + nạp dùng + is_active)
     * Dùng cho dropdown form tạo hợp đồng / hội viên mới.
     * Response: [{ id, title, code, discount, end_date }]
     */
    public function activeOnly()
    {
        $today = Carbon::today();
        $promotions = Promotion::where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->whereColumn('current_usage', '<', 'usage_limit')
            ->orderBy('discount', 'desc')
            ->get(['id', 'title', 'code', 'discount', 'end_date']);

        return response()->json($promotions);
    }

    /** POST /api/promotions */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:150',
            'code'        => 'nullable|string|max:50|unique:promotions',
            'description' => 'nullable|string',
            'discount'    => 'nullable|numeric|min:0|max:100',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:0',
        ]);
        return response()->json(Promotion::create($data), 201);
    }

    /** GET /api/promotions/{id} */
    public function show($id)
    {
        return response()->json(Promotion::findOrFail($id));
    }

    /** PUT /api/promotions/{id} */
    public function update(Request $request, $id)
    {
        $promotion = Promotion::findOrFail($id);
        $data = $request->validate([
            'title'       => 'sometimes|string|max:150',
            'code'        => 'nullable|string|max:50|unique:promotions,code,' . $id,
            'description' => 'nullable|string',
            'discount'    => 'nullable|numeric|min:0|max:100',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:0',
        ]);
        $promotion->update($data);
        return response()->json($promotion);
    }

    /** DELETE /api/promotions/{id} */
    public function destroy($id)
    {
        Promotion::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa khuyến mãi']);
    }
}
