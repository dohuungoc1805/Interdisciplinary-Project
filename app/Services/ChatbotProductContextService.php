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
        $seasonTerms = $this->seasonTerms($message);
        $categoryTerms = $this->audienceCategoryTerms($message);

        $query = Product::query()
            ->with(['category:id,name,parent_id', 'category.parent:id,name', 'variants:id,product_id,size,color,stock'])
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

        $searchKeywords = $keywords->merge($seasonTerms)->unique()->values();
        if ($searchKeywords->isNotEmpty()) {
            $rankedProducts = $this->productSearchService->rank($productsQuery->get(), $searchKeywords->implode(' '));

            if ($seasonTerms->isNotEmpty()) {
                $rankedProducts = $rankedProducts
                    ->filter(fn (Product $product) => $this->isSeasonalProduct($product))
                    ->values();
            }

            // For multi-word requests, keep products that match the actual phrase strongly.
            // This prevents a generic word such as "áo" from mixing shirts with hoodies.
            if ($seasonTerms->isEmpty() && $keywords->count() >= 2 && $rankedProducts->isNotEmpty()) {
                $topScore = (int) $rankedProducts->first()->getAttribute('search_score');
                // Keep typo-tolerant two-word matches (often scoring around 45-55),
                // while filtering products that only match a generic word.
                $minimumScore = max(45, (int) ceil($topScore * 0.45));
                $rankedProducts = $rankedProducts
                    ->filter(fn (Product $product) => (int) $product->getAttribute('search_score') >= $minimumScore)
                    ->values();
            }

            $products = $rankedProducts->take(max($limit * 4, 24));
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
                'image_url' => $product->mainImageUrl(),
                'description' => $product->description,
                'category_name' => $product->category?->name,
                'audience_group' => $this->audienceGroup($product),
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

    public function applyPreferenceFilters(string $message, array $candidates): array
    {
        $filters = $this->preferenceFilters($message);
        $filtered = collect($candidates);

        if ($filters['min_price'] !== null) {
            $filtered = $filtered->filter(fn (array $candidate) => (float) $candidate['price'] >= $filters['min_price']);
        }
        if ($filters['max_price'] !== null) {
            $filtered = $filtered->filter(fn (array $candidate) => (float) $candidate['price'] <= $filters['max_price']);
        }
        if ($filters['sizes'] !== []) {
            $filtered = $filtered->filter(fn (array $candidate) => $this->candidateHasAnyValue($candidate['available_sizes'] ?? [], $filters['sizes']));
        }
        if ($filters['colors'] !== []) {
            $filtered = $filtered->filter(fn (array $candidate) => $this->candidateHasAnyValue($candidate['available_colors'] ?? [], $filters['colors']));
        }
        if ($filters['styles'] !== []) {
            $filtered = $filtered->filter(function (array $candidate) use ($filters) {
                $text = Str::lower(Str::ascii(implode(' ', array_filter([
                    $candidate['name'] ?? '',
                    $candidate['description'] ?? '',
                    $candidate['category_name'] ?? '',
                ]))));

                foreach ($filters['styles'] as $style) {
                    foreach ($style as $term) {
                        if (str_contains($text, $term)) {
                            return true;
                        }
                    }
                }

                return false;
            });
        }

        return $filtered->values()->all();
    }

    public function availableQuickFilters(array $candidates): array
    {
        $quickReplies = [];
        $prices = collect($candidates)->pluck('price')->map(fn ($price) => (float) $price);
        if ($prices->isNotEmpty() && $prices->max() > 500000) {
            $quickReplies[] = ['label' => 'Dưới 500.000đ', 'message' => 'Lọc sản phẩm dưới 500.000đ.'];
        }

        $colors = collect($candidates)->pluck('available_colors')->flatten()->map(fn ($color) => Str::lower(Str::ascii((string) $color)));
        foreach ([['Màu đen', 'Lọc sản phẩm màu đen.'], ['Màu trắng', 'Lọc sản phẩm màu trắng.'], ['Màu xanh', 'Lọc sản phẩm màu xanh.']] as [$label, $message]) {
            $needle = Str::lower(Str::ascii(Str::after($label, 'Màu ')));
            if ($colors->contains(fn ($color) => str_contains($color, $needle))) {
                $quickReplies[] = ['label' => $label, 'message' => $message];
            }
        }

        $sizes = collect($candidates)->pluck('available_sizes')->flatten()->map(fn ($size) => trim(Str::upper((string) $size)))->unique()->values();
        foreach (['S', 'M', 'L', 'XL'] as $size) {
            if ($sizes->contains($size)) {
                $quickReplies[] = ['label' => 'Size '.$size, 'message' => 'Lọc sản phẩm còn size '.$size.'.'];
                break;
            }
        }

        return array_slice($quickReplies, 0, 3);
    }

    private function preferenceFilters(string $message): array
    {
        $normalized = Str::lower(Str::ascii($message));
        $priceMessage = preg_replace('/\b\d{1,2}\s*(tuoi|thang|nam|cm|kg)\b/u', '', $normalized) ?? $normalized;
        $amounts = $this->extractMoneyAmounts($priceMessage);
        $minPrice = null;
        $maxPrice = null;

        if (count($amounts) >= 2 && preg_match('/tu\b.*den\b|between|[-–]/u', $normalized)) {
            $minPrice = $amounts[0];
            $maxPrice = $amounts[1];
        } elseif ($amounts !== []) {
            $amount = $amounts[0];
            if (preg_match('/duoi|toi\s*da|khong\s*qua|<=|toi\s*da/u', $normalized)) {
                $maxPrice = $amount;
            } elseif (preg_match('/tren|toi\s*thieu|>=|tu\s*\d/u', $normalized)) {
                $minPrice = $amount;
            } elseif (preg_match('/ngan\s*sach|tam\s*gia|gia\s*khoang/u', $normalized)) {
                $maxPrice = $amount;
            }
        }

        preg_match_all('/(?:size|co|mac|mang)\s*(xs|xxs|s|m|l|xl|xxl|xxxl|\d{2})\b/u', $normalized, $sizeMatches);
        $sizes = array_values(array_unique(array_map(fn ($size) => Str::upper($size), $sizeMatches[1] ?? [])));

        $colors = [];
        $colorAliases = [
            'den' => ['den', 'black'],
            'trang' => ['trang', 'white'],
            'do' => ['do', 'red'],
            'xanh' => ['xanh', 'blue'],
            'vang' => ['vang', 'yellow'],
            'hong' => ['hong', 'pink'],
            'nau' => ['nau', 'brown'],
            'xam' => ['xam', 'gray', 'grey'],
            'be' => ['be', 'kem', 'cream'],
        ];
        foreach ($colorAliases as $color => $aliases) {
            if (preg_match('/(?:mau|color)\s+(?:'.implode('|', $aliases).')\b/u', $normalized)) {
                $colors[] = $color;
            }
        }

        $styleAliases = [
            ['thanh lich', 'cong so', 'blazer', 'so mi'],
            ['toi gian', 'minimal', 'basic'],
            ['ca tinh', 'streetwear', 'bomber', 'oversize'],
            ['thoai mai', 'casual', 'hoodie'],
            ['the thao', 'sport', 'activewear'],
            ['vintage', 'co dien'],
        ];
        $styles = array_values(array_filter($styleAliases, function (array $terms) use ($normalized) {
            foreach ($terms as $term) {
                if (str_contains($normalized, $term)) {
                    return true;
                }
            }

            return false;
        }));

        return [
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sizes' => $sizes,
            'colors' => $colors,
            'styles' => $styles,
        ];
    }

    private function extractMoneyAmounts(string $message): array
    {
        preg_match_all('/\b\d[\d.,]*\s*(?:k|nghin|ngan|trieu|tr|d|dong)?\b/u', $message, $matches);

        return array_values(array_filter(array_map(fn ($value) => $this->parseMoneyAmount($value), $matches[0] ?? []), fn ($value) => $value > 0));
    }

    private function parseMoneyAmount(string $value): float
    {
        $value = trim(Str::lower($value));
        $multiplier = 1;
        if (preg_match('/(k|nghin|ngan)\b/', $value)) {
            $multiplier = 1000;
        } elseif (preg_match('/(trieu|tr)\b/', $value)) {
            $multiplier = 1000000;
        }
        $number = preg_replace('/[^0-9.,]/', '', $value) ?? '0';
        if ($multiplier === 1 && (str_contains($number, '.') || str_contains($number, ','))) {
            $number = preg_replace('/[.,]/', '', $number) ?? $number;
        } else {
            $number = str_replace(',', '.', $number);
        }

        return (float) $number * $multiplier;
    }

    private function candidateHasAnyValue(array $available, array $requested): bool
    {
        foreach ($available as $value) {
            $normalizedValue = Str::lower(Str::ascii((string) $value));
            foreach ($requested as $needle) {
                if (str_contains($normalizedValue, Str::lower(Str::ascii((string) $needle)))) {
                    return true;
                }
            }
        }

        return false;
    }

    public function needsAgeClarification(string $message, array $candidates): bool
    {
        if ($candidates === [] || $this->audienceCategoryTerms($message) !== []) {
            return false;
        }

        $normalized = Str::lower(Str::ascii($message));
        if (preg_match('/\b\d{1,2}\s*(tuoi|thang|nam)\b/u', $normalized)) {
            return false;
        }

        $groups = collect($candidates)
            ->pluck('audience_group')
            ->filter()
            ->unique()
            ->values();

        return $groups->count() > 1;
    }

    public function filterForAudience(string $message, array $candidates): array
    {
        $normalized = Str::lower(Str::ascii($message));
        $target = match (true) {
            preg_match('/tre\s*em|thieu\s*nhi|\bbe\b|kids?|child|\b\d{1,2}\s*(thang|tuoi)\b/u', $normalized) === 1
                && ! preg_match('/nguoi\s*lon|adult|\b(1[3-9]|[2-9][0-9])\s*tuoi\b/u', $normalized) => 'children',
            preg_match('/\bnam\b|dan\s*ong|\bmen\b/u', $normalized) === 1 => 'men',
            preg_match('/\bnu\b|phu\s*nu|\bwomen\b/u', $normalized) === 1 => 'women',
            preg_match('/nguoi\s*lon|adult|truong\s*thanh|\b(1[3-9]|[2-9][0-9])\s*tuoi\b/u', $normalized) === 1 => 'adult',
            default => null,
        };

        if ($target === null) {
            return $candidates;
        }

        $filtered = collect($candidates)->filter(function (array $candidate) use ($target) {
            $group = $candidate['audience_group'] ?? 'general';

            return match ($target) {
                'children' => $group === 'children',
                'adult' => $group !== 'children',
                'men', 'women' => $group === $target || $group === 'general',
                default => true,
            };
        })->values()->all();

        return $filtered !== [] ? $filtered : $candidates;
    }

    private function seasonTerms(string $message): \Illuminate\Support\Collection
    {
        $normalized = Str::lower(Str::ascii($message));
        $winterIntent = preg_match('/mua\s*dong|troi\s*lanh|thoi\s*tiet\s*lanh|do\s*am|giu\s*am|ret|lanh/u', $normalized);

        if (! $winterIntent) {
            return collect();
        }

        return collect(['ao khoac', 'hoodie', 'ao len', 'ao ni', 'cardigan', 'boots'])
            ->merge(['jacket', 'sweater', 'coat'])
            ->values();
    }

    private function isSeasonalProduct(Product $product): bool
    {
        $text = Str::lower(Str::ascii(implode(' ', array_filter([
            $product->name,
            $product->description,
            $product->category?->name,
            $product->category?->parent?->name,
        ]))));

        return preg_match('/ao\s*khoac|\bkhoac\b|hoodie|ao\s*len|\blen\b|ao\s*ni|\bni\b|cardigan|jacket|sweater|\bcoat\b|boots?|parka|ao\s*phao|long\s*vu|giu\s*am|mua\s*dong|thoi\s*tiet\s*lanh|\bam\b/u', $text) === 1;
    }

    private function audienceGroup(Product $product): ?string
    {
        $text = Str::lower(Str::ascii(implode(' ', array_filter([
            $product->name,
            $product->category?->name,
            $product->category?->parent?->name,
        ]))));

        return match (true) {
            preg_match('/tre\s*em|thieu\s*nhi|\bbe\b|kids?|child/u', $text) === 1 => 'children',
            preg_match('/\bnam\b|dan\s*ong|men|boy/u', $text) === 1 => 'men',
            preg_match('/\bnu\b|phu\s*nu|women|girl/u', $text) === 1 => 'women',
            default => 'general',
        };
    }

    private function productKeywords(string $message): \Illuminate\Support\Collection
    {
        $ignoredWords = [
            'mình', 'tôi', 'muốn', 'tìm', 'mua', 'cho', 'cần', 'gợi', 'ý', 'tư', 'vấn',
            'xem', 'giúp', 'với', 'cái', 'loại', 'mẫu', 'đồ', 'được', 'không', 'nhé', 'ạ', 'ơi',
            'trẻ', 'em', 'bé', 'trai', 'gái', 'nam', 'nữ', 'kids', 'kid', 'child', 'children',
        ];

        $ignoredWords = array_merge(
            array_map(fn (string $word) => Str::ascii($word), $ignoredWords),
            ['cua', 'hang', 'ban', 'co', 'la', 'mot', 'ko', 'do', 'loc', 'san', 'pham', 'duoi', 'tren', 'toi', 'da', 'qua', 'ngan', 'sach', 'size', 'mau', 'phong', 'cach', 'thanh', 'lich', 'gian', 'ca', 'tinh', 'thoai', 'mai', 'the', 'thao', 'vintage', 'dien', 'minimal', 'casual', 'streetwear', 'oversize']
        );
        $normalizedMessage = Str::lower(Str::ascii($message));

        return collect(preg_split('/[^a-z0-9]+/', $normalizedMessage) ?: [])
            ->filter(fn (string $word) => mb_strlen($word) >= 2
                && ! preg_match('/^\d+[a-z]*$/', $word)
                && ! in_array($word, $ignoredWords, true))
            ->unique()
            ->values();
    }

    private function audienceCategoryTerms(string $message): array
    {
        $normalizedMessage = Str::lower($message);

        $normalizedAsciiMessage = Str::lower(Str::ascii($message));
        if (preg_match('/tre\s*em|thieu\s*nhi|\bbe\b|\bkids?\b|\bchild(?:ren)?\b/u', $normalizedAsciiMessage)) {
            return ['trẻ em', 'bé trai', 'bé gái'];
        }
        if (preg_match('/\bnam\b|dan\s*ong|\bmen\b/u', $normalizedAsciiMessage)) {
            return ['thời trang nam', 'nam'];
        }
        if (preg_match('/\bnu\b|phu\s*nu|\bwomen\b/u', $normalizedAsciiMessage)) {
            return ['thời trang nữ', 'nữ'];
        }

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
        bool $needsAgeClarification = false,
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
            ? app(\App\Services\SizeRecommendationService::class)->missingMeasurementsPrompt($message)
            : null;

        if ($needsAgeClarification) {
            return 'Mình tìm thấy một số mẫu phù hợp với thời tiết lạnh cho nhiều nhóm tuổi. Bạn đang tìm đồ cho bé bao nhiêu tuổi, hay cho người lớn ở độ tuổi nào để mình chọn đúng mẫu hơn nhỉ?';
        }

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
            $lines[] = sprintf('- **%s** (%s, %s): [Xem sản phẩm](%s)', $candidate['name'], $price, $stockText, $candidate['product_url']);
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
