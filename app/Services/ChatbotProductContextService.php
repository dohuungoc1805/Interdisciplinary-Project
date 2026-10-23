<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

class ChatbotProductContextService
{
    public function __construct(private ProductSearchService $productSearchService) {}

    public function findCandidates(string $message, int $limit = 6): array
    {
        $keywords = $this->productKeywords($message);
        $categoryTerms = $this->audienceCategoryTerms($message);

        $query = Product::query()
            ->with(['category:id,name', 'variants:id,product_id,size,color,stock'])
            ->where('is_active', true);

        if ($categoryTerms !== []) {
            $query->whereHas('category', function ($categoryQuery) use ($categoryTerms) {
                $categoryQuery->where(function ($matchQuery) use ($categoryTerms) {
                    foreach ($categoryTerms as $term) {
                        $matchQuery->orWhereRaw('LOWER(name) LIKE ?', ['%'.$term.'%']);
                    }
                })->orWhereHas('parent', function ($parentQuery) use ($categoryTerms) {
                    $parentQuery->where(function ($matchQuery) use ($categoryTerms) {
                        foreach ($categoryTerms as $term) {
                            $matchQuery->orWhereRaw('LOWER(name) LIKE ?', ['%'.$term.'%']);
                        }
                    });
                });
            });
        }

        $productsQuery = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('is_new')
            ->orderByDesc('is_on_sale')
            ->orderByDesc('average_rating')
            ->orderByDesc('review_count');

        if ($keywords->isNotEmpty()) {
            $rankedProducts = $this->productSearchService->rank($productsQuery->get(), $keywords->implode(' '));

            // For multi-word requests, keep products that match the actual phrase strongly.
            // This prevents a generic word such as "áo" from mixing shirts with hoodies.
            if ($keywords->count() >= 2 && $rankedProducts->isNotEmpty()) {
                $topScore = (int) $rankedProducts->first()->getAttribute('search_score');
                // Keep typo-tolerant two-word matches (often scoring around 45-55),
                // while filtering products that only match a generic word.
                $minimumScore = max(45, (int) ceil($topScore * 0.45));
                $rankedProducts = $rankedProducts
                    ->filter(fn (Product $product) => (int) $product->getAttribute('search_score') >= $minimumScore)
                    ->values();
            }

            $products = $rankedProducts->take($limit);
        } else {
            $products = $productsQuery->limit($limit)->get();
        }

        return $products->map(function (Product $product) {
            $inStock = $product->variants->sum('stock') > 0;
            $availableSizes = $product->variants
                ->filter(fn ($variant) => $variant->stock > 0)
                ->pluck('size')
                ->filter()
                ->unique()
                ->values()
                ->all();
            $availableColors = $product->variants
                ->filter(fn ($variant) => $variant->stock > 0)
                ->pluck('color')
                ->filter()
                ->unique()
                ->values()
                ->all();

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
                'available_sizes' => $availableSizes,
                'available_colors' => $availableColors,
                'product_url' => route('products.show', $product->slug),
            ];
        })->all();
    }

    private function productKeywords(string $message): \Illuminate\Support\Collection
    {
        $ignoredWords = [
            'mình', 'tôi', 'muốn', 'tìm', 'mua', 'cho', 'cần', 'gợi', 'ý', 'tư', 'vấn',
            'xem', 'giúp', 'với', 'cái', 'loại', 'mẫu', 'đồ', 'được', 'không', 'nhé', 'ạ', 'ơi',
            'trẻ', 'em', 'bé', 'trai', 'gái', 'nam', 'nữ', 'kids', 'kid', 'child', 'children',
        ];

        return collect(preg_split('/[^\p{L}\p{N}]+/u', Str::lower($message)) ?: [])
            ->filter(fn (string $word) => mb_strlen($word) >= 2 && ! in_array($word, $ignoredWords, true))
            ->unique()
            ->values();
    }

    private function audienceCategoryTerms(string $message): array
    {
        $normalizedMessage = Str::lower($message);

        if (preg_match('/trẻ\s*em|thiếu\s*nhi|\bbé\b|\bkids?\b|\bchild(?:ren)?\b/u', $normalizedMessage)) {
            return ['trẻ em', 'bé trai', 'bé gái'];
        }

        if (preg_match('/\bnam\b|đàn\s*ông|\bmen\b/u', $normalizedMessage)) {
            return ['thời trang nam', 'nam'];
        }

        if (preg_match('/\bnữ\b|phụ\s*nữ|\bwomen\b/u', $normalizedMessage)) {
            return ['thời trang nữ', 'nữ'];
        }

        return [];
    }

    public function fallbackReply(
        string $message,
        array $candidates,
        ?array $sizeRecommendation = null,
        bool $isSizingRequest = false,
    ): string
    {
        if ($casualReply = $this->casualReply($message)) {
            return $casualReply;
        }

        $sizeLine = $sizeRecommendation !== null
            ? sprintf(
                'Theo chiều cao %.0f cm và cân nặng %.1f kg, size tham khảo của bạn là %s%s. %s',
                $sizeRecommendation['height_cm'],
                $sizeRecommendation['weight_kg'],
                $sizeRecommendation['size'],
                ! empty($sizeRecommendation['age_note']) ? ' ('.$sizeRecommendation['age_note'].')' : '',
                $sizeRecommendation['note']
            )
            : null;
        $measurementPrompt = $isSizingRequest && $sizeRecommendation === null
            ? 'Để tư vấn size chính xác hơn, bạn cho mình chiều cao (cm) và cân nặng (kg) nhé. Ví dụ: cao 170cm, nặng 60kg.'
            : null;

        if ($candidates === []) {
            return implode("\n\n", array_filter([
                $measurementPrompt,
                $sizeLine,
                'Mình chưa tìm thấy sản phẩm thật sự phù hợp từ yêu cầu này. Bạn có thể nói rõ thêm kiểu dáng, danh mục hoặc mức giá để mình gợi ý chính xác hơn.',
            ]));
        }

        $lines = [
            'Mình gợi ý một số sản phẩm phù hợp bạn có thể xem ngay:',
        ];
        if ($sizeLine !== null) {
            array_unshift($lines, $sizeLine);
        }
        if ($measurementPrompt !== null) {
            array_unshift($lines, $measurementPrompt);
        }

        foreach (array_slice($candidates, 0, 3) as $candidate) {
            $price = number_format((int) $candidate['price'], 0, ',', '.').' ₫';
            $stockText = $candidate['in_stock'] ? 'còn hàng' : 'tạm hết hàng';
            $lines[] = sprintf('- %s (%s, %s): %s', $candidate['name'], $price, $stockText, $candidate['product_url']);
            $sizeAvailability = $this->sizeAvailabilityLine($candidate, $sizeRecommendation);
            if ($sizeAvailability !== null) {
                $lines[] = '  '.$sizeAvailability;
            }
            if (($candidate['available_colors'] ?? []) !== []) {
                $lines[] = '  Màu còn hàng: '.implode(', ', $candidate['available_colors']).'.';
            }
        }

        $lines[] = 'Bạn có thể bấm trực tiếp vào đường dẫn ở từng sản phẩm.';

        $lines[] = 'Nếu bạn muốn, mình có thể lọc tiếp theo ngân sách hoặc phong cách cụ thể.';

        return implode("\n", $lines);
    }

    private function casualReply(string $message): ?string
    {
        $normalized = trim(Str::lower(Str::ascii($message)));
        $normalized = preg_replace('/[^a-z0-9\s!?.,]+/', ' ', $normalized) ?? $normalized;
        $normalized = trim(preg_replace('/\s+/', ' ', $normalized) ?? $normalized);

        if (preg_match('/^(xin chao|chao|hello|hi|hey|alo|good morning|good evening)[\s!?.,]*$/', $normalized)) {
            return 'Chào bạn! Mình là trợ lý của QUDENA. Bạn đang muốn tìm sản phẩm, chọn size hay xem ưu đãi nào hôm nay?';
        }

        if (preg_match('/^(cam on|thanks|thank you|ok|oke|duoc roi|hay qua)[\s!?.,]*$/', $normalized)) {
            return 'Không có gì nhé! Khi cần tìm sản phẩm hoặc tư vấn size, bạn cứ nhắn mình.';
        }

        if (preg_match('/^(tam biet|bye|hen gap lai)[\s!?.,]*$/', $normalized)) {
            return 'Tạm biệt bạn nhé! Chúc bạn chọn được món đồ thật ưng ý.';
        }

        return null;
    }

    private function sizeAvailabilityLine(array $candidate, ?array $sizeRecommendation): ?string
    {
        $availableSizes = $candidate['available_sizes'] ?? [];
        if ($availableSizes === []) {
            return $sizeRecommendation !== null ? 'Mẫu này hiện chưa còn size để chọn.' : null;
        }

        $sizeList = implode(', ', $availableSizes);
        if ($sizeRecommendation === null) {
            return 'Size còn hàng: '.$sizeList.'.';
        }

        $recommendedSize = $sizeRecommendation['size'];
        if (in_array($recommendedSize, $availableSizes, true)) {
            return sprintf('Size còn hàng: %s. Size %s đang phù hợp theo số đo tham khảo của bạn.', $sizeList, $recommendedSize);
        }

        return sprintf('Size còn hàng: %s. Size %s theo số đo tham khảo hiện không có trên mẫu này; bạn nên đối chiếu bảng size trước khi chọn.', $sizeList, $recommendedSize);
    }
}
