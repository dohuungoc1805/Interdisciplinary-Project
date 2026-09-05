<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductsImport implements ToCollection
{
    private const MAX_STOCK = 1000;

    public int $created = 0;

    public int $updated = 0;

    public array $errors = [];

    public function collection(Collection $rows): void
    {
        $header = null;

        foreach ($rows as $index => $row) {
            $values = $row->map(fn ($value) => is_string($value) ? trim($value) : $value)->toArray();

            if ($index === 0) {
                $header = array_map(fn ($h) => $this->normalizeHeader((string) $h), $values);
                continue;
            }

            if (! $header || count(array_filter($values, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $data = [];
            foreach ($header as $col => $key) {
                if ($key !== '') {
                    $data[$key] = $values[$col] ?? null;
                }
            }

            $rowNumber = $index + 1;
            $sku = trim((string) ($data['sku'] ?? ''));
            $name = trim((string) ($data['name'] ?? ''));
            $categoryId = $data['category_id'] ?? null;
            $price = $data['price'] ?? null;

            if ($sku === '' || $name === '' || $categoryId === null || $price === null) {
                $this->errors[] = "Dòng {$rowNumber}: thiếu sku/name/category_id/price";
                continue;
            }

            if (! is_numeric($categoryId) || ! Category::query()->whereKey((int) $categoryId)->exists()) {
                $this->errors[] = "Dòng {$rowNumber}: category_id {$categoryId} không tồn tại. Số lượng phải nhập ở cột stock, không nhập vào category_id.";
                continue;
            }

            if (! is_numeric($price) || (float) $price < 0) {
                $this->errors[] = "Dòng {$rowNumber}: price phải là số không âm";
                continue;
            }

            if (($data['compare_price'] ?? null) !== null && $data['compare_price'] !== '' && (! is_numeric($data['compare_price']) || (float) $data['compare_price'] < 0)) {
                $this->errors[] = "Dòng {$rowNumber}: compare_price phải là số không âm hoặc để trống";
                continue;
            }

            $stockValue = $this->parseStock($data['stock'] ?? null, $rowNumber);
            if ($stockValue === false) {
                continue;
            }

            $variantSize = trim((string) ($data['variant_size'] ?? 'Freesize'));
            $variantColor = trim((string) ($data['variant_color'] ?? 'Mặc định'));
            $variantSku = trim((string) ($data['variant_sku'] ?? ''));
            $variantPrice = $data['variant_price'] ?? null;

            if ($variantSize === '') {
                $variantSize = 'Freesize';
            }
            if ($variantColor === '') {
                $variantColor = 'Mặc định';
            }
            if ($variantSku === '') {
                $variantSku = $sku.'-1';
            }
            if ($variantPrice !== null && $variantPrice !== '' && (! is_numeric($variantPrice) || (float) $variantPrice < 0)) {
                $this->errors[] = "Dòng {$rowNumber}: variant_price phải là số không âm hoặc để trống";
                continue;
            }

            $isActive = $data['is_active'] ?? '1';
            $payload = [
                'name' => $name,
                'slug' => Str::slug($name).'-'.Str::lower($sku),
                'category_id' => (int) $categoryId,
                'description' => (string) ($data['description'] ?? ''),
                'price' => (float) $price,
                'compare_price' => ($data['compare_price'] ?? null) !== null && $data['compare_price'] !== '' ? (float) $data['compare_price'] : null,
                'is_active' => $this->truthy($isActive),
            ];

            DB::transaction(function () use ($sku, $payload, $stockValue, $variantSize, $variantColor, $variantSku, $variantPrice) {
                $product = Product::query()->where('sku', $sku)->first();
                if ($product) {
                    $product->update($payload);
                    $this->updated++;
                } else {
                    $product = Product::query()->create(array_merge($payload, ['sku' => $sku]));
                    $this->created++;
                }

                if ($stockValue !== null || ! $product->variants()->exists()) {
                    $variant = $product->variants()
                        ->where('size', $variantSize)
                        ->where('color', $variantColor)
                        ->first()
                        ?? $product->variants()->orderBy('id')->first();

                    $variantPayload = [
                        'size' => $variantSize,
                        'color' => $variantColor,
                        'sku' => $variantSku,
                        'price' => $variantPrice !== null && $variantPrice !== '' ? (float) $variantPrice : null,
                        'stock' => $stockValue ?? 0,
                    ];

                    if ($variant) {
                        $variant->update($variantPayload);
                    } else {
                        ProductVariant::query()->create(array_merge($variantPayload, [
                            'product_id' => $product->id,
                        ]));
                    }
                }
            });
        }
    }

    private function normalizeHeader(string $header): string
    {
        $key = Str::lower(trim($header));

        return match ($key) {
            'quantity', 'qty', 'so_luong', 'soluong', 'số lượng', 'ton_kho', 'tồn kho' => 'stock',
            'category', 'categoryid', 'category id', 'danh_muc', 'danh mục' => 'category_id',
            'variant size', 'size' => 'variant_size',
            'variant color', 'color', 'mau', 'màu' => 'variant_color',
            'variant sku' => 'variant_sku',
            'variant price' => 'variant_price',
            default => $key,
        };
    }

    private function parseStock(mixed $value, int $rowNumber): int|null|false
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value) || (int) $value != (float) $value || (int) $value < 0 || (int) $value > self::MAX_STOCK) {
            $this->errors[] = 'Dòng '.$rowNumber.': stock/số lượng phải là số nguyên từ 0 đến '.self::MAX_STOCK;

            return false;
        }

        return (int) $value;
    }

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return ! in_array(Str::lower(trim((string) $value)), ['0', 'false', 'no', 'khong', 'không'], true);
    }
}
