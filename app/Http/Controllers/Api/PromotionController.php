<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /** GET /api/promotions */
    public function index()
    {
        return response()->json(Promotion::all());
    }

    /** POST /api/promotions */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:150',
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
