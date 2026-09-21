<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'payment_method',
        'payment_status',
        'payment_confirmed_at',
        'subtotal',
        'discount_total',
        'shipping',
        'total',
        'coupon_id',
        'coupon_code',
        'recipient_name',
        'phone',
        'line1',
        'line2',
        'city',
        'state',
        'postal_code',
        'country',
        'customer_note',
        'admin_note',
        'tracking_number',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'payment_confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusEnum(): ?OrderStatus
    {
        return OrderStatus::tryFrom($this->status);
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            default => 'Thanh toán khi nhận hàng (COD)',
        };
    }

    public function paymentStatusLabel(): string
    {
        if ($this->payment_method === 'cod') {
            return $this->payment_status === 'paid' ? 'Đã thanh toán' : 'Thanh toán khi nhận hàng';
        }

        return $this->payment_status === 'paid' ? 'Đã xác nhận thanh toán' : 'Chờ xác nhận thanh toán';
    }
}
