<?php

namespace App\Services;

class SizeRecommendationService
{
    /**
     * Return a size recommendation when both height and weight are present.
     *
     * @return array{height_cm: float, weight_kg: float, size: string, audience: string, note: string}|null
     */
    public function recommend(string $message): ?array
    {
        $height = $this->parseHeight($message);
        $weight = $this->parseWeight($message);

        if ($height === null || $weight === null) {
            return null;
        }

        $isChildren = $this->isChildrenRequest($message);
        $size = $isChildren
            ? $this->recommendChildrenSize($height, $weight)
            : $this->recommendAdultSize($height, $weight);

        return [
            'height_cm' => $height,
            'weight_kg' => $weight,
            'size' => $size,
            'audience' => $isChildren ? 'children' : 'adult',
            'note' => $isChildren
                ? 'Đây là size trẻ em tham khảo theo chiều cao và cân nặng; bạn nên đối chiếu bảng size riêng của sản phẩm trước khi đặt.'
                : 'Đây là size tham khảo theo form phổ thông; bạn nên đối chiếu bảng size của từng sản phẩm và ưu tiên số đo vòng ngực/vòng eo nếu có.',
        ];
    }

    public function isSizingRequest(string $message): bool
    {
        return preg_match('/\b(size|kích\s*cỡ|chọn\s*cỡ|mặc\s*cỡ|vừa\s*người)\b/iu', $message) === 1;
    }

    private function isChildrenRequest(string $message): bool
    {
        return preg_match('/trẻ\s*em|thiếu\s*nhi|\bbé\b|\bkids?\b|\bchild(?:ren)?\b|\bteen(?:ager)?\b/iu', $message) === 1;
    }

    private function recommendAdultSize(float $height, float $weight): string
    {
        return match (true) {
            $weight <= 55 && $height <= 165 => 'S',
            $weight <= 65 && $height <= 172 => 'M',
            $weight <= 75 && $height <= 178 => 'L',
            $weight <= 88 && $height <= 185 => 'XL',
            default => 'XXL',
        };
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

    private function parseHeight(string $message): ?float
    {
        if (preg_match('/\b(1|2)\s*m\s*(\d{1,2})\b/iu', $message, $match)) {
            return ((float) $match[1] * 100) + (float) $match[2];
        }

        if (preg_match('/(?:cao|chiều\s*cao|height)\s*:?\s*(\d+(?:[.,]\d+)?)\s*(m|cm)?/iu', $message, $match)) {
            return $this->normalizeHeight((float) str_replace(',', '.', $match[1]), $match[2] ?? null);
        }

        if (preg_match('/\b(1[3-9]\d|20\d)\s*cm\b/iu', $message, $match)) {
            return (float) $match[1];
        }

        if (preg_match('/\b(1[3-9]\d|20\d)\s*(?:mét|met)\b/iu', $message, $match)) {
            return (float) $match[1];
        }

        return null;
    }

    private function parseWeight(string $message): ?float
    {
        if (preg_match('/(?:nặng|cân\s*nặng|weight)\s*:?\s*(\d+(?:[.,]\d+)?)\s*(?:kg|kilô?gam)?/iu', $message, $match)) {
            return (float) str_replace(',', '.', $match[1]);
        }

        if (preg_match('/\b(\d+(?:[.,]\d+)?)\s*kg\b/iu', $message, $match)) {
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
}
