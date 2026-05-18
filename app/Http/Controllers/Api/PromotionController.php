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
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $today))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today))
            ->where(fn($q) => $q->whereNull('usage_limit')->orWhereColumn('current_usage', '<', 'usage_limit'))
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
            'is_active'   => 'nullable|boolean',
        ]);
        $promotion->update($data);
        return response()->json($promotion);
    }

    /**
     * PATCH /api/promotions/{id}/toggle
     * Bật / Tắt trạng thái is_active của khuyến mãi
     */
    public function toggleStatus($id)
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->is_active = !$promotion->is_active;
        $promotion->save();

        return response()->json([
            'id'        => $promotion->id,
            'is_active' => $promotion->is_active,
            'message'   => $promotion->is_active ? 'Đã kích hoạt khuyến mãi' : 'Đã ngừng khuyến mãi',
        ]);
    }

    /** DELETE /api/promotions/{id} */
    public function destroy($id)
    {
        Promotion::findOrFail($id)->delete();
        return response()->json(['message' => 'Đã xóa khuyến mãi']);
    }

    /**
     * GET /api/promotions/check?code=...
     * Kiểm tra mã khuyến mãi theo code, trả về trạng thái hợp lệ và % giảm giá.
     * Response: { is_valid: bool, id, discount, title, message }
     */
    public function checkByCode(Request $request)
    {
        $code = trim($request->query('code', ''));

        if (!$code) {
            return response()->json([
                'is_valid' => false,
                'message'  => 'Vui lòng nhập mã khuyến mãi.',
            ], 422);
        }

        $promo = Promotion::where('code', $code)->first();

        if (!$promo) {
            return response()->json([
                'is_valid' => false,
                'message'  => 'Mã khuyến mãi không tồn tại.',
            ]);
        }

        // Dùng accessor getIsValidAttribute() đã có sẵn trong Model
        if (!$promo->is_valid) {
            $now    = now();
            $reason = '';
            if (!$promo->is_active) {
                $reason = 'Mã khuyến mãi đã bị vô hiệu hoá.';
            } elseif ($now->lt($promo->start_date)) {
                $reason = 'Mã khuyến mãi chưa đến ngày áp dụng.';
            } elseif ($now->gt($promo->end_date)) {
                $reason = 'Mã khuyến mãi đã hết hạn.';
            } elseif ($promo->current_usage >= $promo->usage_limit) {
                $reason = 'Mã khuyến mãi đã hết lượt sử dụng.';
            } else {
                $reason = 'Mã khuyến mãi không hợp lệ hoặc đã hết hạn.';
            }

            return response()->json([
                'is_valid' => false,
                'message'  => $reason,
            ]);
        }

        return response()->json([
            'is_valid' => true,
            'id'       => $promo->id,
            'discount' => (float) $promo->discount,
            'title'    => $promo->title,
            'message'  => "Áp dụng thành công! Giảm {$promo->discount}%",
        ]);
    }

}
