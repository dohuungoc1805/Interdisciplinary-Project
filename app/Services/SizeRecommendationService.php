<?php

namespace App\Services;

class SizeRecommendationService
{
    /**
     * Return a size recommendation when both height and weight are present.
     *
     * @return array{height_cm: float, weight_kg: float, size: string, note: string}|null
     */
    public function recommend(string $message): ?array
    {
        $height = $this->parseHeight($message);
        $weight = $this->parseWeight($message);

        if ($height === null || $weight === null) {
            return null;
        }

        $size = match (true) {
            $weight <= 55 && $height <= 165 => 'S',
            $weight <= 65 && $height <= 172 => 'M',
            $weight <= 75 && $height <= 178 => 'L',
            $weight <= 88 && $height <= 185 => 'XL',
            default => 'XXL',
        };

        return [
            'height_cm' => $height,
            'weight_kg' => $weight,
            'size' => $size,
            'note' => 'Đây là size tham khảo theo form phổ thông; bạn nên đối chiếu bảng size của từng sản phẩm và ưu tiên số đo vòng ngực/vòng eo nếu có.',
        ];
    }

    public function isSizingRequest(string $message): bool
    {
        return preg_match('/\b(size|kích\s*cỡ|chọn\s*cỡ|mặc\s*cỡ|vừa\s*người)\b/iu', $message) === 1;
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
