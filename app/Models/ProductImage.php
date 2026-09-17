<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'position'];

    public function publicUrl(): string
    {
        if (! $this->path) {
            return 'https://placehold.co/160x160/e7e5e4/57534e?text=+';
        }
        if (preg_match('#^https?://#i', $this->path)) {
            return $this->path;
        }

        if (Storage::disk('public')->exists($this->path)) {
            return Storage::disk('public')->url($this->path);
        }

        return 'https://placehold.co/600x750/f5f5f4/78716c?text=Product';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
