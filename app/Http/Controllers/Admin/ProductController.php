<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Exports\ProductsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
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
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new ProductsImport;
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

        return view('admin.products.create', compact('categories'));
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
        unset($data['variants'], $data['image'], $data['gallery']);
        $product = null;
        DB::transaction(function () use (&$product, $data, $request, $variants) {
            $product = Product::query()->create($data);
            foreach ($variants as $v) {
                ProductVariant::query()->create([
                    'product_id' => $product->id,
                    'size' => $v['size'],
                    'color' => $v['color'],
                    'sku' => $v['sku'] ?? null,
                    'price' => $v['price'] ?? null,
                    'stock' => (int) $v['stock'],
                ]);
            }
            if ($request->hasFile('gallery')) {
                $pos = 0;
                foreach ($request->file('gallery') as $file) {
                    $path = $file->store('products', 'public');
                    ProductImage::query()->create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'position' => $pos++,
                    ]);
                }
            }
        });

        return redirect()->route('admin.products.index')->with('status', 'Đã tạo sản phẩm.');
    }

    public function edit(Product $product): View
    {
        $product->load(['images', 'variants']);
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
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
        unset($data['variants'], $data['image'], $data['gallery']);
        if (! isset($data['main_image'])) {
            unset($data['main_image']);
        }
        DB::transaction(function () use ($request, $product, $data, $variants, $oldMainImage) {
            $product->update($data);
            if (isset($data['main_image']) && $oldMainImage && ! preg_match('#^https?://#i', (string) $oldMainImage)) {
                Storage::disk('public')->delete($oldMainImage);
            }
            $product->variants()->delete();
            foreach ($variants as $v) {
                ProductVariant::query()->create([
                    'product_id' => $product->id,
                    'size' => $v['size'],
                    'color' => $v['color'],
                    'sku' => $v['sku'] ?? null,
                    'price' => $v['price'] ?? null,
                    'stock' => (int) $v['stock'],
                ]);
            }
            if ($request->hasFile('gallery')) {
                $pos = (int) $product->images()->max('position') + 1;
                foreach ($request->file('gallery') as $file) {
                    $path = $file->store('products', 'public');
                    ProductImage::query()->create([
                        'product_id' => $product->id,
                        'path' => $path,
                        'position' => $pos++,
                    ]);
                }
            }
        });

        return redirect()->route('admin.products.index')->with('status', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        DB::transaction(function () use ($product) {
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img->path);
            }
            if (! empty($product->main_image)) {
                Storage::disk('public')->delete($product->main_image);
            }
            $product->delete();
        });

        return redirect()->route('admin.products.index')->with('status', 'Đã xóa sản phẩm.');
    }

    private function validateProduct(Request $request, ?int $productId = null, bool $requireImage = false): array
    {
        $this->compactVariantRows($request);
        $this->normalizeProductNumericInputs($request);

        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:64',
            'is_featured' => 'boolean',
            'is_hot' => 'boolean',
            'is_on_sale' => 'boolean',
            'is_new' => 'boolean',
            'is_active' => 'boolean',
            'image' => ($requireImage ? 'required' : 'nullable').'|image|max:4096',
            'gallery.*' => 'nullable|image|max:4096',
            'variants' => 'required|array|min:1',
            'variants.*.size' => 'required|string|max:32',
            'variants.*.color' => 'required|string|max:64',
            'variants.*.sku' => 'nullable|string|max:64',
            'variants.*.stock' => 'required|integer|min:0|max:1000',
            'variants.*.price' => 'nullable|numeric|min:0',
        ];
        $data = $request->validate($rules);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_hot'] = $request->boolean('is_hot');
        $data['is_on_sale'] = $request->boolean('is_on_sale');
        $data['is_new'] = $request->boolean('is_new');
        $data['is_active'] = $request->boolean('is_active');

        $variantKeys = collect($data['variants'])
            ->map(fn (array $variant) => Str::lower(trim($variant['size'])).'|'.Str::lower(trim($variant['color'])));
        if ($variantKeys->count() !== $variantKeys->unique()->count()) {
            throw ValidationException::withMessages([
                'variants' => 'Mỗi tổ hợp size và màu chỉ được khai báo một lần.',
            ]);
        }

        return $data;
    }

    private function compactVariantRows(Request $request): void
    {
        $variants = $request->input('variants', []);
        if (! is_array($variants)) {
            $request->merge(['variants' => []]);

            return;
        }
        $kept = [];
        foreach ($variants as $row) {
            if (! is_array($row)) {
                continue;
            }
            $size = trim((string) ($row['size'] ?? ''));
            $color = trim((string) ($row['color'] ?? ''));
            if ($size === '' || $color === '') {
                continue;
            }
            $row['size'] = $size;
            $row['color'] = $color;
            $kept[] = $row;
        }
        $request->merge(['variants' => array_values($kept)]);
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
