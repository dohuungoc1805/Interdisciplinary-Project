<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CouponType;
use App\Enums\CouponKind;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::query()->latest()->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, false);
        $data['code'] = strtoupper(trim($data['code']));
        Coupon::query()->create($data);

        return redirect()->route('admin.coupons.index')->with('status', 'Đã tạo mã giảm giá.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $data = $this->validated($request, true, $coupon);
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }
        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('status', 'Đã cập nhật mã giảm giá.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('status', 'Đã xóa mã giảm giá.');
    }

    private function validated(Request $request, bool $isUpdate, ?Coupon $coupon = null): array
    {
        $rules = [
            'code' => 'required|string|max:32|unique:coupons,code',
            'kind' => 'required|in:'.implode(',', array_column(CouponKind::cases(), 'value')),
            'type' => 'required|in:'.implode(',', array_column(CouponType::cases(), 'value')),
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'used_count' => 'sometimes|integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ];
        if ($isUpdate && $coupon) {
            $rules['code'] = 'sometimes|required|string|max:32|unique:coupons,code,'.$coupon->id;
            $rules['kind'] = 'sometimes|required|in:'.implode(',', array_column(CouponKind::cases(), 'value'));
        }
        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
