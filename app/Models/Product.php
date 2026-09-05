<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'main_image',
        'price',
        'compare_price',
        'sku',
        'is_featured',
        'is_hot',
        'is_on_sale',
        'is_new',
        'is_active',
        'review_count',
        'average_rating',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_hot' => 'boolean',
            'is_on_sale' => 'boolean',
            'is_new' => 'boolean',
            'is_active' => 'boolean',
            'review_count' => 'integer',
            'average_rating' => 'decimal:2',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function mainImageUrl(): string
    {
        if (! $this->main_image) {
            return 'https://placehold.co/480x600/f5f5f4/78716c?text=Product';
        }
        if (preg_match('#^https?://#i', $this->main_image)) {
            return $this->main_image;
        }

        return Storage::disk('public')->url($this->main_image);
    }

    /** Main image, or first gallery image, or placeholder (for banners/cards). */
    public function primaryImageUrl(): string
    {
        if ($this->main_image) {
            return $this->mainImageUrl();
        }
        $first = $this->relationLoaded('images')
            ? $this->images->sortBy('position')->first()
            : $this->images()->orderBy('position')->first();
        if ($first) {
            return $first->publicUrl();
        }

        return $this->mainImageUrl();
    }

    public function discountPercent(): ?int
    {
        if ($this->compare_price === null || (float) $this->compare_price <= (float) $this->price) {
            return null;
        }

        $off = (1 - (float) $this->price / (float) $this->compare_price) * 100;

        return max(1, (int) round($off));
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function recalculateReviewStats(): void
    {
        $agg = $this->reviews()->where('is_approved', true);
        $count = (clone $agg)->count();
        $avg = $count > 0 ? round((float) (clone $agg)->avg('rating'), 2) : 0;
        $this->update([
            'review_count' => $count,
            'average_rating' => $avg,
        ]);
    }
}
