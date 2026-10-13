<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'color',
        'image_path',
        'sku',
        'price',
        'avg_cost',
        'last_cost',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'avg_cost' => 'decimal:2',
            'last_cost' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function displayPrice(): string
    {
        $p = $this->price;

        return $p !== null ? (string) $p : (string) $this->product->price;
    }

    public function unitPriceCents(): float|int
    {
        $raw = $this->price !== null ? $this->price : $this->product->price;

        return (float) $raw;
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }
        if (preg_match('#^https?://#i', $this->image_path)) {
            return $this->image_path;
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
