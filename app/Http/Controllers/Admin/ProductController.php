<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Exports\ProductsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\InventoryMovement;
use App\Models\OrderItem;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request): View
    {
        $q = Product::query()->with('category')->withSum('variants as stock_total', 'stock');
        if ($request->filled('q')) {
            $term = trim((string) $request->query('q', ''));
            if ($term !== '') {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('name', 'like', $like)
                        ->orWhere('sku', 'like', $like);
                });
            }
        }
        $categoryId = $request->integer('category');
        if ($categoryId > 0) {
            $q->where('category_id', $categoryId);
        }
        if ($request->filled('availability')) {
            match ($request->string('availability')->toString()) {
                'in_stock' => $q->whereHas('variants', fn ($variantQuery) => $variantQuery->where('stock', '>', 0)),
                'out_of_stock' => $q->whereDoesntHave('variants', fn ($variantQuery) => $variantQuery->where('stock', '>', 0)),
                'low_stock' => $q->whereHas('variants', fn ($variantQuery) => $variantQuery->whereBetween('stock', [1, (int) config('shop.low_stock_threshold')])),
                default => null,
            };
        }
        if ($request->filled('visibility')) {
            $q->where('is_active', $request->string('visibility')->toString() === 'active');
        }
        if ($request->filled('status')) {
            $q->where('status', $request->string('status')->toString());
        }
        if ($request->boolean('sale')) {
            $q->where('is_on_sale', true);
        }
        $products = $q->latest()->paginate(20)->withQueryString();
        $categories = Category::query()->orderBy('position')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function quickSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'nullable|string|max:120',
        ]);
        $term = trim((string) $request->query('q', ''));
        $query = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(30);

        if ($term !== '') {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->where(function ($sub) use ($like) {
                $sub->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like);
            });
        }

        $rows = $query->get(['id', 'name', 'slug'])->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
        ]);

        return response()->json(['data' => $rows]);
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new ProductsExport, 'products-export.xlsx');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new ProductsTemplateExport, 'products-template.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new ProductsImport($request->file('file')->getRealPath());
        Excel::import($import, $request->file('file'));

        $message = "Import hoàn tất. Tạo mới: {$import->created}, cập nhật: {$import->updated}.";
        if (count($import->errors) > 0) {
            $message .= ' Lỗi: '.implode(' | ', array_slice($import->errors, 0, 5));
        }

        return redirect()->route('admin.products.index')->with('status', $message);
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $categoryTypes = $categories->mapWithKeys(fn (Category $category) => [$category->id => $this->categoryType($category)]);

        return view('admin.products.create', compact('categories', 'categoryTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request, null, true);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['main_image'] = $request->file('image')->store('products', 'public');
        $variants = array_values(array_filter($data['variants'], fn ($v) => ! empty($v['size']) && ! empty($v['color'])));
        if (count($variants) < 1) {
            return back()->with('error', 'Add at least one variant with size and color.')->withInput();
        }
        unset($data['variants'], $data['image']);
        $product = null;
        DB::transaction(function () use (&$product, $data, $variants) {
            $product = Product::query()->create($data);
            foreach ($variants as $v) {
                ProductVariant::query()->create([
                    'product_id' => $product->id,
                    'size' => $v['size'],
                    'color' => $v['color'],
                    'image_path' => isset($v['image']) && $v['image'] instanceof \Illuminate\Http\UploadedFile ? $v['image']->store('products/variants', 'public') : ($v['image_path'] ?? null),
                    'sku' => $v['sku'] ?? null,
                    'price' => $v['price'] ?? null,
                    'stock' => (int) $v['stock'],
                ]);
            }
        });

        return redirect()->route('admin.products.index')->with('status', 'Đã tạo sản phẩm.');
    }

    public function edit(Product $product): View
    {
        $product->load(['images', 'variants']);
        $categories = Category::query()->orderBy('name')->get();
        $categoryTypes = $categories->mapWithKeys(fn (Category $category) => [$category->id => $this->categoryType($category)]);

        return view('admin.products.edit', compact('product', 'categories', 'categoryTypes'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request, $product->id, false);
        $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        $oldMainImage = $product->main_image;
        if ($request->hasFile('image')) {
            $data['main_image'] = $request->file('image')->store('products', 'public');
        }
        $variants = array_values(array_filter($data['variants'], fn ($v) => ! empty($v['size']) && ! empty($v['color'])));
        if (count($variants) < 1) {
            return back()->with('error', 'Add at least one variant.')->withInput();
        }
        unset($data['variants'], $data['image']);
        if (! isset($data['main_image'])) {
            unset($data['main_image']);
        }
        $existingVariants = $product->variants()->get()->keyBy('id');
        $submittedVariantIds = collect($variants)->pluck('id')->filter()->map(fn ($id) => (int) $id)->values()->all();
        $oldVariantImages = $existingVariants->pluck('image_path')->filter()->values()->all();
        $keptVariantImages = [];
        DB::transaction(function () use ($product, $data, $variants, $oldMainImage, $existingVariants, $submittedVariantIds, &$keptVariantImages) {
            $product->update($data);
            if (isset($data['main_image']) && $oldMainImage && ! preg_match('#^https?://#i', (string) $oldMainImage)) {
                Storage::disk('public')->delete($oldMainImage);
            }
            foreach ($variants as $v) {
                $variant = ! empty($v['id']) ? $existingVariants->get((int) $v['id']) : null;
                if (! $variant) {
                    $variant = new ProductVariant(['product_id' => $product->id]);
                }
                $oldVariantImage = $variant->image_path;
                $variantImage = $oldVariantImage;
                if (isset($v['image']) && $v['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $variantImage = $v['image']->store('products/variants', 'public');
                } elseif (! empty($v['image_path'])) {
                    $variantImage = $v['image_path'];
                }
                if ($variantImage) {
                    $keptVariantImages[] = $variantImage;
                }
                $variant->fill([
                    'product_id' => $product->id,
                    'size' => $v['size'],
                    'color' => $v['color'],
                    'image_path' => $variantImage,
                    'sku' => $v['sku'] ?? null,
                    'price' => $v['price'] ?? null,
                    'stock' => (int) $v['stock'],
                ])->save();
            }
            foreach ($existingVariants as $existingVariant) {
                if (! in_array((int) $existingVariant->id, $submittedVariantIds, true)) {
                    // Keep historical variants referenced by inventory/order records, but make them unavailable.
                    $existingVariant->update(['stock' => 0]);
                    if ($existingVariant->image_path) {
                        $keptVariantImages[] = $existingVariant->image_path;
                    }
                }
            }
        });
        foreach ($oldVariantImages as $oldVariantImage) {
            if (! in_array($oldVariantImage, $keptVariantImages, true)) {
                Storage::disk('public')->delete($oldVariantImage);
            }
        }

        return redirect()->route('admin.products.index')->with('status', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['is_active' => false, 'status' => 'discontinued']);

        return redirect()->route('admin.products.index')->with('status', 'Đã chuyển sản phẩm sang trạng thái Ngừng kinh doanh.');
    }

    public function forceDestroy(Product $product): RedirectResponse
    {
        if (OrderItem::query()->where('product_id', $product->id)->exists()) {
            return back()->with('error', 'Không thể xóa vĩnh viễn sản phẩm đã phát sinh đơn hàng. Hãy chuyển sang Ngừng kinh doanh.');
        }

        $files = array_filter([
            $product->main_image,
            ...$product->images()->pluck('path')->all(),
            ...$product->variants()->pluck('image_path')->all(),
        ]);

        DB::transaction(function () use ($product) {
            $product->delete();
        });

        foreach (array_unique($files) as $file) {
            if (! preg_match('#^https?://#i', $file)) {
                Storage::disk('public')->delete($file);
            }
        }

        return redirect()->route('admin.products.index')->with('status', 'Đã xóa sản phẩm.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'action' => ['required', 'in:activate,deactivate,delete,category,price,price_increase_percent,price_decrease_percent,stock,stock_increase,stock_decrease'],
            'category_id' => ['required_if:action,category', 'nullable', 'exists:categories,id'],
            'price' => ['required_if:action,price', 'nullable', 'numeric', 'min:0'],
            'stock' => ['required_if:action,stock', 'nullable', 'integer', 'min:0', 'max:100000'],
            'percent' => ['required_if:action,price_increase_percent,price_decrease_percent', 'nullable', 'numeric', 'min:0', 'max:1000'],
            'quantity' => ['required_if:action,stock_increase,stock_decrease', 'nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $products = Product::query()->whereIn('id', $data['product_ids'])->get();
        DB::transaction(function () use ($products, $data) {
            match ($data['action']) {
                'activate' => $products->each->update(['is_active' => true, 'status' => 'active']),
                'deactivate' => $products->each->update(['is_active' => false, 'status' => 'discontinued']),
                'delete' => $products->each(function (Product $product) {
                    if (OrderItem::query()->where('product_id', $product->id)->exists()) {
                        $product->update(['is_active' => false, 'status' => 'discontinued']);
                    } else {
                        $product->delete();
                    }
                }),
                'category' => $products->each->update(['category_id' => (int) $data['category_id']]),
                'price' => $products->each->update(['price' => (float) $data['price']]),
                'stock' => $products->each(fn (Product $product) => $product->variants->each(fn (ProductVariant $variant) => $this->inventoryService->setStock($variant, (int) $data['stock']))),
                'price_increase_percent' => $products->each(fn (Product $product) => $product->update(['price' => round((float) $product->price * (1 + ((float) $data['percent'] / 100)), 2)])),
                'price_decrease_percent' => $products->each(fn (Product $product) => $product->update(['price' => round(max(0, (float) $product->price * (1 - ((float) $data['percent'] / 100))), 2)])),
                'stock_increase' => $products->each(fn (Product $product) => $product->variants->each(fn (ProductVariant $variant) => $this->inventoryService->setStock($variant, min(100000, (int) $variant->stock + (int) $data['quantity'])))),
                'stock_decrease' => $products->each(fn (Product $product) => $product->variants->each(fn (ProductVariant $variant) => $this->inventoryService->setStock($variant, max(0, (int) $variant->stock - (int) $data['quantity'])))),
            };
        });

        $message = match ($data['action']) {
            'activate' => 'Đã bật bán các sản phẩm đã chọn.',
            'deactivate' => 'Đã chuyển các sản phẩm đã chọn sang Ngừng kinh doanh.',
            'delete' => 'Đã xóa sản phẩm chưa phát sinh đơn; sản phẩm đã có đơn được chuyển sang Ngừng kinh doanh.',
            'category' => 'Đã cập nhật danh mục cho các sản phẩm đã chọn.',
            'price' => 'Đã cập nhật giá cho các sản phẩm đã chọn.',
            'stock' => 'Đã cập nhật tồn kho cho các biến thể của sản phẩm đã chọn.',
            'price_increase_percent' => 'Đã tăng giá theo phần trăm cho các sản phẩm đã chọn.',
            'price_decrease_percent' => 'Đã giảm giá theo phần trăm cho các sản phẩm đã chọn.',
            'stock_increase' => 'Đã tăng tồn kho cho các biến thể của sản phẩm đã chọn.',
            'stock_decrease' => 'Đã giảm tồn kho cho các biến thể của sản phẩm đã chọn.',
        };

        return back()->with('status', $message);
    }

    public function inventoryMovements(Request $request): View
    {
        $movements = InventoryMovement::query()->with('variant.product')->latest('moved_at')->paginate(30)->withQueryString();

        return view('admin.products.inventory-movements', compact('movements'));
    }

    private function validateProduct(Request $request, ?int $productId = null, bool $requireImage = false): array
    {
        $category = Category::query()->findOrFail((int) $request->input('category_id'));
        $categoryType = $this->categoryType($category);
        $this->compactVariantRows($request, $categoryType);
        $this->normalizeProductNumericInputs($request);

        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'details' => 'nullable|array',
            'details.material' => 'nullable|string|max:120',
            'details.dimensions' => 'nullable|string|max:120',
            'details.volume' => 'nullable|string|max:80',
            'details.expiry' => 'nullable|date',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:64',
            'is_featured' => 'boolean',
            'is_hot' => 'boolean',
            'is_on_sale' => 'boolean',
            'is_new' => 'boolean',
            'is_active' => 'boolean',
            'status' => 'nullable|in:draft,active,hidden,discontinued',
            'image' => ($requireImage ? 'required' : 'nullable').'|image|max:4096',
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|integer',
            'variants.*.size' => 'required|string|max:32',
            'variants.*.color' => 'required|string|max:64',
            'variants.*.sku' => 'nullable|string|max:64',
            'variants.*.stock' => 'required|integer|min:0|max:1000',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.image' => 'nullable|image|max:4096',
            'variants.*.image_path' => 'nullable|string|max:500',
        ];
        $data = $request->validate($rules);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_hot'] = $request->boolean('is_hot');
        $data['is_on_sale'] = $request->boolean('is_on_sale');
        $data['is_new'] = $request->boolean('is_new');
        $data['status'] = $request->input('status') ?: ($request->boolean('is_active') ? 'active' : 'hidden');
        $data['is_active'] = $data['status'] === 'active';
        $data['details'] = collect($data['details'] ?? [])->filter(fn ($value) => filled($value))->all();

        if (! empty($data['sku']) && Product::query()->where('sku', $data['sku'])->when($productId, fn ($q) => $q->where('id', '!=', $productId))->exists()) {
            throw ValidationException::withMessages(['sku' => 'SKU sản phẩm đã tồn tại.']);
        }

        $variantKeys = collect($data['variants'])
            ->map(fn (array $variant) => Str::lower(trim($variant['size'])).'|'.Str::lower(trim($variant['color'])));
        if ($variantKeys->count() !== $variantKeys->unique()->count()) {
            throw ValidationException::withMessages([
                'variants' => 'Mỗi tổ hợp size và màu chỉ được khai báo một lần.',
            ]);
        }

        $variantSkus = collect($data['variants'])->pluck('sku')->filter()->map(fn ($sku) => Str::lower(trim((string) $sku)));
        if ($variantSkus->count() !== $variantSkus->unique()->count()) {
            throw ValidationException::withMessages(['variants' => 'SKU của các biến thể không được trùng nhau.']);
        }
        if ($variantSkus->isNotEmpty() && ProductVariant::query()->whereIn('sku', $variantSkus->values()->all())->when($productId, fn ($q) => $q->where('product_id', '!=', $productId))->exists()) {
            throw ValidationException::withMessages(['variants' => 'SKU biến thể đã tồn tại ở sản phẩm khác.']);
        }

        return $data;
    }

    private function compactVariantRows(Request $request, string $categoryType = 'apparel'): void
    {
        $variants = $request->input('variants', []);
        $variantFiles = $request->file('variants', []);
        if (! is_array($variants)) {
            $request->merge(['variants' => []]);

            return;
        }
        $kept = [];
        foreach ($variants as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            $size = trim((string) ($row['size'] ?? ''));
            $color = trim((string) ($row['color'] ?? ''));
            if ($categoryType !== 'apparel' && $size === '') {
                $size = 'Mặc định';
            }
            if ($categoryType !== 'apparel' && $color === '') {
                $color = 'Mặc định';
            }
            if ($size === '' || $color === '') {
                continue;
            }
            $row['size'] = $size;
            $row['color'] = $color;
            $row['image'] = $variantFiles[$i]['image'] ?? null;
            $kept[] = $row;
        }
        $request->merge(['variants' => array_values($kept)]);
    }

    private function categoryType(Category $category): string
    {
        $name = Str::lower(Str::ascii(collect([$category->name, $category->parent?->name])->filter()->implode(' ')));

        return match (true) {
            Str::contains($name, ['giay', 'sandal', 'dep']) => 'footwear',
            Str::contains($name, ['tui', 'balo', 'vi', 'phu kien']) => 'bag',
            Str::contains($name, ['my pham', 'lam dep', 'son', 'serum', 'kem']) => 'cosmetics',
            default => 'apparel',
        };
    }

    private function normalizeProductNumericInputs(Request $request): void
    {
        if ($request->input('compare_price') === '' || $request->input('compare_price') === null) {
            $request->merge(['compare_price' => null]);
        }
        $variants = $request->input('variants', []);
        if (! is_array($variants)) {
            return;
        }
        foreach ($variants as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            if (($row['price'] ?? null) === '') {
                $variants[$i]['price'] = null;
            }
        }
        $request->merge(['variants' => $variants]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Product::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
