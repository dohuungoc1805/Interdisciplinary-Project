<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use Throwable;

class ProductsImport implements ToCollection, WithMultipleSheets
{
    private const MAX_STOCK = 1000;

    public int $created = 0;

    public int $updated = 0;

    public array $errors = [];

    /** @var array<string, string> */
    private array $embeddedImages = [];

    /** @var array<string, string> */
    private array $categoryMap = [];

    public function __construct(?string $sourcePath = null)
    {
        if ($sourcePath) {
            $this->embeddedImages = $this->extractEmbeddedImages($sourcePath);
            $this->categoryMap = $this->extractCategoryMap($sourcePath);
        }
    }

    public function sheets(): array
    {
        // Only the first sheet contains products; the Categories sheet is a reference.
        return [0 => $this];
    }

    public function collection(Collection $rows): void
    {
        $header = null;
        $productDefinitions = [];
        $mainImages = [];
        $variantSkus = [];

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
            $categoryName = trim((string) ($data['category_name'] ?? ''));
            $price = $data['price'] ?? null;

            if ($sku === '' || $name === '' || ($categoryId === null && $categoryName === '') || $price === null) {
                $this->errors[] = "Dòng {$rowNumber}: thiếu sku/name/category/price";
                continue;
            }

            $categoryLookupName = $categoryName !== '' ? $categoryName : ($this->categoryMap[(string) $categoryId] ?? '');
            if (is_numeric($categoryId) && Category::query()->whereKey((int) $categoryId)->exists()) {
                $categoryId = (int) $categoryId;
            } elseif ($categoryLookupName !== '') {
                $resolvedCategory = Category::query()->where('name', $categoryLookupName)->first();
                $categoryId = $resolvedCategory?->id;
            }
            if (! $categoryId) {
                $this->errors[] = "Dòng {$rowNumber}: không tìm thấy danh mục {$categoryName}";
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
                $variantSku = $sku.'-'.Str::upper(Str::slug($variantSize.'-'.$variantColor));
            }
            if ($variantPrice !== null && $variantPrice !== '' && (! is_numeric($variantPrice) || (float) $variantPrice < 0)) {
                $this->errors[] = "Dòng {$rowNumber}: variant_price phải là số không âm hoặc để trống";
                continue;
            }

            $isActive = $data['is_active'] ?? '1';
            $status = trim((string) ($data['status'] ?? ''));
            if (! in_array($status, ['draft', 'active', 'hidden', 'discontinued'], true)) {
                $status = $this->truthy($isActive) ? 'active' : 'hidden';
            }
            $imageUrl = trim((string) ($data['main_image_url'] ?? $data['image_url'] ?? ''));
            $mainImageColumn = array_search('main_image_url', $header, true);
            $mainImageColumn = is_int($mainImageColumn) ? $mainImageColumn + 1 : null;
            $imageUrl = $imageUrl !== '' ? $imageUrl : ($this->embeddedImages[$rowNumber.':'.$mainImageColumn] ?? '');
            if ($imageUrl !== '' && ! $this->isStoredImage($imageUrl) && ! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                $this->errors[] = "Dòng {$rowNumber}: main_image_url không hợp lệ";
                continue;
            }
            $variantImageUrl = trim((string) ($data['variant_image_url'] ?? ''));
            $variantImageColumn = array_search('variant_image_url', $header, true);
            $variantImageColumn = is_int($variantImageColumn) ? $variantImageColumn + 1 : null;
            $variantImageUrl = $variantImageUrl !== '' ? $variantImageUrl : ($this->embeddedImages[$rowNumber.':'.$variantImageColumn] ?? '');
            if ($variantImageUrl !== '' && ! $this->isStoredImage($variantImageUrl) && ! filter_var($variantImageUrl, FILTER_VALIDATE_URL)) {
                $this->errors[] = "Dòng {$rowNumber}: variant_image_url không hợp lệ";
                continue;
            }

            $definition = [
                'name' => $name,
                'category_id' => (int) $categoryId,
                'description' => (string) ($data['description'] ?? ''),
                'price' => (float) $price,
                'compare_price' => ($data['compare_price'] ?? null) !== null && $data['compare_price'] !== '' ? (float) $data['compare_price'] : null,
                'status' => $status,
            ];
            if (isset($productDefinitions[$sku]) && $productDefinitions[$sku] !== $definition) {
                $this->errors[] = "Row {$rowNumber}: product fields differ for the same sku {$sku}";
                continue;
            }
            $productDefinitions[$sku] = $definition;

            if ($imageUrl !== '') {
                if (isset($mainImages[$sku]) && $mainImages[$sku] !== $imageUrl) {
                    $this->errors[] = "Row {$rowNumber}: main image differs for the same sku {$sku}";
                    continue;
                }
                $mainImages[$sku] = $imageUrl;
            }

            if (isset($variantSkus[$variantSku])) {
                $this->errors[] = "Row {$rowNumber}: variant_sku {$variantSku} is duplicated";
                continue;
            }
            $variantSkus[$variantSku] = true;
            $isActive = $status === 'active';
            $payload = [
                'name' => $name,
                'slug' => Str::slug($name).'-'.Str::lower($sku),
                'category_id' => (int) $categoryId,
                'description' => (string) ($data['description'] ?? ''),
                'price' => (float) $price,
                'compare_price' => ($data['compare_price'] ?? null) !== null && $data['compare_price'] !== '' ? (float) $data['compare_price'] : null,
                'is_active' => $isActive,
                'status' => $status,
            ];
            if ($imageUrl !== '') {
                $payload['main_image'] = $imageUrl;
            }

            DB::transaction(function () use ($sku, $payload, $stockValue, $variantSize, $variantColor, $variantSku, $variantPrice, $variantImageUrl) {
                $product = Product::query()->where('sku', $sku)->first();
                if ($product) {
                    $product->update($payload);
                    $this->updated++;
                } else {
                    $product = Product::query()->create(array_merge($payload, ['sku' => $sku]));
                    $this->created++;
                }

                // Reuse an existing variant SKU when an import assigns its real size.
                // This converts placeholder sizes without leaving duplicate variants.
                $variant = $product->variants()->where('sku', $variantSku)->first()
                    ?? $product->variants()
                        ->where('size', $variantSize)
                        ->where('color', $variantColor)
                        ->first();
                if ($stockValue !== null || ! $variant || $variantImageUrl !== '') {
                    if ($variantSku === $sku.'-1' && $product->variants()->exists()) {
                        $variantSku = $sku.'-'.Str::upper(Str::slug($variantSize.'-'.$variantColor));
                    }

                    $colorImagePath = $product->variants()
                        ->where('color', $variantColor)
                        ->whereNotNull('image_path')
                        ->value('image_path');
                    $variantPayload = [
                        'size' => $variantSize,
                        'color' => $variantColor,
                        'sku' => $variantSku,
                        'price' => $variantPrice !== null && $variantPrice !== '' ? (float) $variantPrice : null,
                        'stock' => $stockValue ?? ($variant?->stock ?? 0),
                        'image_path' => $variantImageUrl !== '' ? $variantImageUrl : ($variant?->image_path ?: $colorImagePath),
                    ];

                    if ($variant) {
                        $variant->update($variantPayload);
                    } else {
                        ProductVariant::query()->create(array_merge($variantPayload, [
                            'product_id' => $product->id,
                        ]));
                    }

                    if ($variantImageUrl !== '') {
                        $product->variants()
                            ->where('color', $variantColor)
                            ->update(['image_path' => $variantImageUrl]);
                    }
                }
            });
        }
    }

    private function normalizeHeader(string $header): string
    {
        $key = Str::lower(trim($header));

        return match ($key) {
            'variant_stock', 'variant stock' => 'stock',
            'quantity', 'qty', 'so_luong', 'soluong', 'số lượng', 'ton_kho', 'tồn kho' => 'stock',
            'category', 'categoryid', 'category id', 'danh_muc', 'danh mục' => 'category_id',
            'variant size', 'size' => 'variant_size',
            'variant color', 'color', 'mau', 'màu' => 'variant_color',
            'variant sku' => 'variant_sku',
            'variant price' => 'variant_price',
            'main image url', 'image url', 'anh', 'ảnh', 'anh_chinh', 'ảnh chính' => 'main_image_url',
            'variant image url', 'variant_image_url', 'anh bien the', 'ảnh biến thể' => 'variant_image_url',
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

    private function isStoredImage(string $value): bool
    {
        return Str::startsWith($value, 'products/imports/');
    }

    /** @return array<string, string> */
    private function extractCategoryMap(string $sourcePath): array
    {
        $map = [];

        try {
            $spreadsheet = IOFactory::load($sourcePath);
            $sheet = $spreadsheet->getSheetByName('Categories');
            if (! $sheet) {
                return $map;
            }

            foreach ($sheet->toArray(null, true, true, false) as $index => $values) {
                if ($index === 0) {
                    continue;
                }
                $id = trim((string) ($values[0] ?? ''));
                $name = trim((string) ($values[1] ?? ''));
                if ($id !== '' && $name !== '') {
                    $map[$id] = $name;
                }
            }
        } catch (Throwable) {
            return $map;
        }

        return $map;
    }

    /** @return array<string, string> */
    private function extractEmbeddedImages(string $sourcePath): array
    {
        $images = [];

        try {
            $spreadsheet = IOFactory::load($sourcePath);
            $worksheet = $spreadsheet->getSheet(0);

            foreach ($worksheet->getDrawingCollection() as $drawing) {
                [$column, $row] = Coordinate::coordinateFromString($drawing->getCoordinates());
                $columnIndex = Coordinate::columnIndexFromString($column);
                $contents = null;
                $extension = 'png';

                if ($drawing instanceof MemoryDrawing) {
                    $resource = $drawing->getImageResource();
                    if (! is_resource($resource) && ! is_object($resource)) {
                        continue;
                    }

                    $extension = match ($drawing->getMimeType()) {
                        MemoryDrawing::MIMETYPE_JPEG => 'jpg',
                        MemoryDrawing::MIMETYPE_GIF => 'gif',
                        default => 'png',
                    };
                    ob_start();
                    match ($extension) {
                        'jpg' => imagejpeg($resource, null, 90),
                        'gif' => imagegif($resource),
                        default => imagepng($resource),
                    };
                    $contents = ob_get_clean();
                } else {
                    $path = $drawing->getPath();
                    if (is_string($path) && $path !== '') {
                        // PhpSpreadsheet exposes embedded XLSX images through zip:// URLs.
                        $contents = @file_get_contents($path);
                        $extension = strtolower($drawing->getExtension()) ?: 'png';
                    }
                }

                if (! is_string($contents) || $contents === '') {
                    continue;
                }

                $storedPath = 'products/imports/'.Str::uuid().'.'.$extension;
                Storage::disk('public')->put($storedPath, $contents);
                $images[$row.':'.$columnIndex] = $storedPath;
            }
        } catch (Throwable) {
            $this->errors[] = 'Khong the doc anh nhung trong file Excel.';
        }

        return $images;
    }

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return ! in_array(Str::lower(trim((string) $value)), ['0', 'false', 'no', 'khong', 'không'], true);
    }
}
