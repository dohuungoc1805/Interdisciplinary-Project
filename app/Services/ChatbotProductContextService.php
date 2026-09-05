<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

class ChatbotProductContextService
{
    public function findCandidates(string $message, int $limit = 6): array
    {
        $keywords = collect(preg_split('/\s+/u', Str::lower(trim($message))) ?: [])
            ->map(fn (string $word) => trim($word))
            ->filter(fn (string $word) => mb_strlen($word) >= 2)
            ->unique()
            ->values();

        $query = Product::query()
            ->with(['category:id,name', 'variants:id,product_id,stock'])
            ->where('is_active', true);

        if ($keywords->isNotEmpty()) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhereRaw('LOWER(products.name) LIKE ?', ['%'.$keyword.'%'])
                        ->orWhereHas('category', function ($categoryQuery) use ($keyword) {
                            $categoryQuery->whereRaw('LOWER(name) LIKE ?', ['%'.$keyword.'%']);
                        });
                }
            });
        }

        $products = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('is_new')
            ->orderByDesc('is_on_sale')
            ->orderByDesc('average_rating')
            ->orderByDesc('review_count')
            ->limit($limit)
            ->get();

        return $products->map(function (Product $product) {
            $inStock = $product->variants->sum('stock') > 0;

            return [
                'name' => $product->name,
                'slug' => $product->slug,
                'category_name' => $product->category?->name,
                'price' => (float) $product->price,
                'compare_price' => $product->compare_price !== null ? (float) $product->compare_price : null,
                'is_on_sale' => (bool) $product->is_on_sale,
                'is_new' => (bool) $product->is_new,
                'is_featured' => (bool) $product->is_featured,
                'rating' => (float) $product->average_rating,
                'review_count' => (int) $product->review_count,
                'in_stock' => $inStock,
                'product_url' => route('products.show', $product->slug),
            ];
        })->all();
    }

    public function fallbackReply(string $message, array $candidates): string
    {
        if ($candidates === []) {
            return 'Mình chưa tìm thấy sản phẩm thật sự phù hợp từ yêu cầu này. Bạn có thể nói rõ thêm kiểu dáng, danh mục hoặc mức giá để mình gợi ý chính xác hơn.';
        }

        $lines = [
            'Mình gợi ý một số sản phẩm phù hợp bạn có thể xem ngay:',
        ];

        foreach (array_slice($candidates, 0, 3) as $candidate) {
            $price = number_format((int) $candidate['price'], 0, ',', '.').' ₫';
            $stockText = $candidate['in_stock'] ? 'còn hàng' : 'tạm hết hàng';
            $lines[] = sprintf('- %s (%s, %s): %s', $candidate['name'], $price, $stockText, $candidate['product_url']);
        }

        $lines[] = 'Bạn có thể bấm trực tiếp vào đường dẫn ở từng sản phẩm.';

        $lines[] = 'Nếu bạn muốn, mình có thể lọc tiếp theo ngân sách hoặc phong cách cụ thể.';

        return implode("\n", $lines);
    }
}
