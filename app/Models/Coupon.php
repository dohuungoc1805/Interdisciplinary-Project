<?php

namespace App\Models;

use App\Enums\CouponType;
use App\Enums\CouponKind;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'kind',
        'type',
        'value',
        'min_order_amount',
        'max_uses',
        'used_count',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'used_count' => 'integer',
            'max_uses' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isValidForAmount(float $subtotal): bool
    {
        return $this->validationErrorForAmount($subtotal) === null;
    }

    public function validationErrorForAmount(float $subtotal): ?string
    {
        if (! $this->is_active) {
            return 'Mã giảm giá chưa được kích hoạt.';
        }
        if ($subtotal <= 0) {
            return 'Giỏ hàng chưa có sản phẩm để áp dụng mã.';
        }
        if ($this->min_order_amount > 0 && $subtotal < (float) $this->min_order_amount) {
            return 'Mã này chỉ áp dụng cho đơn hàng từ '.number_format((float) $this->min_order_amount, 0, ',', '.').' ₫.';
        }
        $now = Carbon::now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return 'Mã này bắt đầu từ '.$this->starts_at->timezone(config('app.timezone'))->format('d/m/Y H:i').'.';
        }
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return 'Mã giảm giá đã hết hạn.';
        }
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return 'Mã giảm giá đã hết lượt sử dụng.';
        }

        return null;
    }

    public function discountForSubtotal(float $subtotal): float
    {
        if (! $this->isValidForAmount($subtotal)) {
            return 0.0;
        }
        if ($this->type === CouponType::Percent->value) {
            return round($subtotal * ((float) $this->value / 100), 2);
        }

        return min((float) $this->value, $subtotal);
    }

    public function discountForAmount(float $amount): float
    {
        if (! $this->isValidForAmount($amount)) {
            return 0.0;
        }

        if ($this->type === CouponType::Percent->value) {
            return min($amount, round($amount * ((float) $this->value / 100), 2));
        }

        return min((float) $this->value, $amount);
    }

    public function isShipping(): bool
    {
        return ($this->kind ?? CouponKind::Product->value) === CouponKind::Shipping->value;
    }

    public function kindLabel(): string
    {
        return $this->isShipping() ? 'Giảm vận chuyển' : 'Giảm sản phẩm';
    }
}
