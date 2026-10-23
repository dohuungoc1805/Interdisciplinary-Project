<?php

namespace App\Services;

use Illuminate\Support\Str;

class SizeRecommendationService
{
    /**
     * Recommend a size from the store's reference tables.
     * The result is intentionally a reference because each product can have a different fit.
     */
    public function recommend(string $message): ?array
    {
        $height = $this->parseHeight($message);
        $weight = $this->parseWeight($message);

        if ($height === null || $weight === null) {
            return null;
        }

        $audience = $this->isChildrenRequest($message) ? 'children' : 'adult';
        $gender = $audience === 'adult' ? $this->detectGender($message) : null;
        $size = $audience === 'children'
            ? $this->recommendChildrenSize($height, $weight)
            : $this->recommendAdultSize($height, $weight, $gender);

        return [
            'height_cm' => $height,
            'weight_kg' => $weight,
            'size' => $size,
            'audience' => $audience,
            'gender' => $gender,
            'age_note' => $audience === 'children' ? $this->childrenSizeNote($size) : null,
            'note' => $audience === 'children'
                ? 'Size trẻ em được tham khảo theo chiều cao, cân nặng và độ tuổi. Bạn nên đối chiếu thêm bảng size riêng của sản phẩm.'
                : 'Size người lớn được tham khảo theo bảng Nam/Nữ của cửa hàng. Bạn nên ưu tiên bảng size riêng của từng sản phẩm nếu sản phẩm có form đặc biệt.',
        ];
    }

    public function isSizingRequest(string $message): bool
    {
        return preg_match('/\b(size|kich\s*co|chon\s*co|mac\s*co|vua\s*nguoi)\b/u', $this->normalized($message)) === 1;
    }

    public function isChildrenCategory(?string $categoryName): bool
    {
        return $categoryName !== null
            && preg_match('/tre\s*em|thieu\s*nhi|be\s*trai|be\s*gai|kids?|child(?:ren)?/u', $this->normalized($categoryName)) === 1;
    }

    public function isFootwearCategory(?string $categoryName): bool
    {
        return $categoryName !== null
            && preg_match('/giay|sandal|dep|sneaker/u', $this->normalized($categoryName)) === 1;
    }

    public function isAdultApparelCategory(?string $categoryName): bool
    {
        return $categoryName !== null
            && preg_match('/thoi\s*trang|ao|quan|vay|jean|hoodie|so\s*mi/u', $this->normalized($categoryName)) === 1;
    }

    /** Data used by the product page's size guide. */
    public function sizeGuide(?string $categoryName = null): array
    {
        if ($this->isFootwearCategory($categoryName)) {
            return [
                'audience' => 'footwear',
                'title' => 'Bảng size giày - dép tham khảo',
                'note' => 'Đo chiều dài và chiều ngang bàn chân bằng cm, sau đó đối chiếu với size giày của sản phẩm.',
                'rows' => [
                    ['size' => '36', 'length' => '22.5', 'width' => '8.5'],
                    ['size' => '37', 'length' => '23', 'width' => '8.5 - 9'],
                    ['size' => '38', 'length' => '23.5', 'width' => '9'],
                    ['size' => '39', 'length' => '24.5', 'width' => '9.5'],
                    ['size' => '40', 'length' => '25', 'width' => '9.5 - 10'],
                    ['size' => '41', 'length' => '25.5', 'width' => '10'],
                    ['size' => '42', 'length' => '26', 'width' => '10 - 10.5'],
                    ['size' => '43', 'length' => '27', 'width' => '10.5'],
                    ['size' => '44', 'length' => '27.5', 'width' => '10.5 - 11'],
                ],
            ];
        }
        if ($this->isChildrenCategory($categoryName)) {
            return [
                'audience' => 'children',
                'title' => 'Bảng size trẻ em tham khảo',
                'note' => 'Độ tuổi chỉ mang tính tham khảo. Hãy ưu tiên chiều cao, cân nặng và bảng size riêng của sản phẩm.',
                'rows' => [
                    ['size' => '4 tuổi', 'height' => '95-110 cm', 'weight' => '14-18 kg', 'age' => 'Khoảng 4 tuổi'],
                    ['size' => '6 tuổi', 'height' => '111-125 cm', 'weight' => '19-25 kg', 'age' => 'Khoảng 6 tuổi'],
                    ['size' => '8 tuổi', 'height' => '126-135 cm', 'weight' => '26-35 kg', 'age' => 'Khoảng 8 tuổi'],
                    ['size' => '10 tuổi', 'height' => '136-150 cm', 'weight' => '36-45 kg', 'age' => 'Khoảng 10 tuổi'],
                    ['size' => '12 tuổi', 'height' => 'Trên 150 cm', 'weight' => 'Trên 45 kg', 'age' => 'Khoảng 12 tuổi'],
                ],
            ];
        }

        if (! $this->isAdultApparelCategory($categoryName)) {
            return [
                'audience' => 'none',
                'title' => 'Thông tin kích thước',
                'note' => 'Sản phẩm này không dùng bảng size quần áo. Vui lòng xem thông tin chi tiết và biến thể của sản phẩm.',
            ];
        }

        return [
            'audience' => 'adult',
            'title' => 'Bảng size người lớn tham khảo',
            'note' => 'Nếu khách chưa chọn Nam hoặc Nữ, chatbot mặc định tham khảo bảng Nam. Số đo thực tế của từng sản phẩm có thể khác.',
            'male' => [
                ['size' => 'S', 'height' => '160-165 cm', 'weight' => '55-60 kg'],
                ['size' => 'M', 'height' => '164-169 cm', 'weight' => '60-65 kg'],
                ['size' => 'L', 'height' => '170-174 cm', 'weight' => '66-70 kg'],
                ['size' => 'XL', 'height' => '174-176 cm', 'weight' => '70-76 kg'],
                ['size' => 'XXL', 'height' => '165-177 cm', 'weight' => '76-80 kg'],
            ],
            'female' => [
                ['size' => 'S', 'height' => '148-153 cm', 'weight' => '38-43 kg'],
                ['size' => 'M', 'height' => '153-155 cm', 'weight' => '43-46 kg'],
                ['size' => 'L', 'height' => '153-158 cm', 'weight' => '46-53 kg'],
                ['size' => 'XL', 'height' => '155-162 cm', 'weight' => '53-57 kg'],
                ['size' => 'XXL', 'height' => '155-166 cm', 'weight' => '57-66 kg'],
            ],
        ];
    }

    private function recommendAdultSize(float $height, float $weight, ?string $gender): string
    {
        $rows = $gender === 'female'
            ? [
                ['size' => 'S', 'min_height' => 148, 'max_height' => 153, 'min_weight' => 38, 'max_weight' => 43],
                ['size' => 'M', 'min_height' => 153, 'max_height' => 155, 'min_weight' => 43, 'max_weight' => 46],
                ['size' => 'L', 'min_height' => 153, 'max_height' => 158, 'min_weight' => 46, 'max_weight' => 53],
                ['size' => 'XL', 'min_height' => 155, 'max_height' => 162, 'min_weight' => 53, 'max_weight' => 57],
                ['size' => 'XXL', 'min_height' => 155, 'max_height' => 166, 'min_weight' => 57, 'max_weight' => 66],
            ]
            : [
                ['size' => 'S', 'min_height' => 160, 'max_height' => 165, 'min_weight' => 55, 'max_weight' => 60],
                ['size' => 'M', 'min_height' => 164, 'max_height' => 169, 'min_weight' => 60, 'max_weight' => 65],
                ['size' => 'L', 'min_height' => 170, 'max_height' => 174, 'min_weight' => 66, 'max_weight' => 70],
                ['size' => 'XL', 'min_height' => 174, 'max_height' => 176, 'min_weight' => 70, 'max_weight' => 76],
                ['size' => 'XXL', 'min_height' => 165, 'max_height' => 177, 'min_weight' => 76, 'max_weight' => 80],
            ];

        $best = $rows[0];
        $bestScore = PHP_INT_MAX;
        foreach ($rows as $row) {
            $heightDistance = $this->rangeDistance($height, $row['min_height'], $row['max_height']);
            $weightDistance = $this->rangeDistance($weight, $row['min_weight'], $row['max_weight']);
            $score = ($weightDistance * 3) + $heightDistance;
            if ($score < $bestScore) {
                $best = $row;
                $bestScore = $score;
            }
        }

        return $best['size'];
    }

    private function rangeDistance(float $value, float $min, float $max): float
    {
        return $value < $min ? $min - $value : ($value > $max ? $value - $max : 0);
    }

    private function recommendChildrenSize(float $height, float $weight): string
    {
        return match (true) {
            $height <= 110 || $weight <= 18 => '4 tuổi',
            $height <= 125 || $weight <= 25 => '6 tuổi',
            $height <= 135 || $weight <= 35 => '8 tuổi',
            $height <= 150 || $weight <= 45 => '10 tuổi',
            default => '12 tuổi',
        };
    }

    private function childrenSizeNote(string $size): string
    {
        return match ($size) {
            '4 tuổi' => 'Phù hợp tham khảo cho bé khoảng 4 tuổi.',
            '6 tuổi' => 'Phù hợp tham khảo cho bé khoảng 6 tuổi.',
            '8 tuổi' => 'Phù hợp tham khảo cho bé khoảng 8 tuổi.',
            '10 tuổi' => 'Phù hợp tham khảo cho bé khoảng 10 tuổi.',
            default => 'Phù hợp tham khảo cho bé khoảng 12 tuổi.',
        };
    }

    private function detectGender(string $message): ?string
    {
        $normalized = $this->normalized($message);
        if (preg_match('/\b(nu|female|women|woman)\b/u', $normalized)) {
            return 'female';
        }
        if (preg_match('/\b(nam|male|men|man)\b/u', $normalized)) {
            return 'male';
        }

        return null;
    }

    private function isChildrenRequest(string $message): bool
    {
        return preg_match('/tre\s*em|thieu\s*nhi|be\s*trai|be\s*gai|\bkids?\b|\bchild(?:ren)?\b|\bteen(?:ager)?\b/u', $this->normalized($message)) === 1;
    }

    private function parseHeight(string $message): ?float
    {
        $normalized = $this->normalized($message);
        if (preg_match('/\b(1|2)\s*m\s*(\d{1,2})\b/u', $normalized, $match)) {
            return ((float) $match[1] * 100) + (float) $match[2];
        }
        if (preg_match('/(?:cao|chieu\s*cao|height)\s*:?\s*(\d+(?:[.,]\d+)?)\s*(m|cm)?/u', $normalized, $match)) {
            return $this->normalizeHeight((float) str_replace(',', '.', $match[1]), $match[2] ?? null);
        }
        if (preg_match('/\b(1[3-9]\d|20\d)\s*(?:cm|met|m)\b/u', $normalized, $match)) {
            return (float) $match[1];
        }

        return null;
    }

    private function parseWeight(string $message): ?float
    {
        $normalized = $this->normalized($message);
        if (preg_match('/(?:nang|can\s*nang|weight)\s*:?\s*(\d+(?:[.,]\d+)?)\s*(?:kg|kilogam)?/u', $normalized, $match)) {
            return (float) str_replace(',', '.', $match[1]);
        }
        if (preg_match('/\b(\d+(?:[.,]\d+)?)\s*kg\b/u', $normalized, $match)) {
            return (float) str_replace(',', '.', $match[1]);
        }

        return null;
    }

    private function normalizeHeight(float $value, ?string $unit): float
    {
        if (strtolower((string) $unit) === 'm' || ($unit === null && $value < 3)) {
            return $value * 100;
        }

        return $value;
    }

    private function normalized(string $value): string
    {
        return Str::lower(Str::ascii($value));
    }
}
