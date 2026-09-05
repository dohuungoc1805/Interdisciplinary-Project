<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReceipt extends Model
{
    protected $fillable = [
        'code',
        'supplier_name',
        'received_at',
        'total_cost',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'date',
            'total_cost' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }
}
