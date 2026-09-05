<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'product_id',
        'image_path',
        'link_url',
        'sort_order',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    public function imagePublicUrl(): string
    {
        if ($this->product_id) {
            $product = $this->relationLoaded('product') ? $this->product : $this->product()->with('images')->first();
            if ($product) {
                return $product->primaryImageUrl();
            }
        }
        if (! $this->image_path) {
            return 'https://placehold.co/1200x420/f97316/ffffff?text=Banner';
        }
        if (preg_match('#^https?://#i', $this->image_path)) {
            return $this->image_path;
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
