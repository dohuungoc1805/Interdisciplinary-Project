<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductPromotion;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:apply-product-promotions')]
#[Description('Apply active product promotions to product prices')]
class ApplyProductPromotions extends Command
{
    public function handle(): int
    {
        $promotion = ProductPromotion::query()
            ->where('is_active', true)
            ->latest('id')
            ->get()
            ->first(fn (ProductPromotion $item) => $item->isInWindow());

        if (! $promotion) {
            $this->warn('Không có chương trình khuyến mãi đang hiệu lực.');

            return self::SUCCESS;
        }

        $query = Product::query();
        if ($promotion->category_id) {
            $query->where('category_id', $promotion->category_id);
        }

        $products = $query->get();
        $updated = 0;

        foreach ($products as $product) {
            $basePrice = (float) ($product->compare_price ?? $product->price);
            $newPrice = $promotion->type === 'percent'
                ? $basePrice * (1 - ((float) $promotion->value / 100))
                : max(0, $basePrice - (float) $promotion->value);

            $product->update([
                'compare_price' => $basePrice,
                'price' => round($newPrice, 2),
                'is_on_sale' => true,
            ]);
            $updated++;
        }

        $this->info("Đã áp khuyến mãi cho {$updated} sản phẩm.");

        return self::SUCCESS;
    }
}
