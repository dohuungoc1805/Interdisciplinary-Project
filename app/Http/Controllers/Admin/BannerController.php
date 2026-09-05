<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = Banner::query()->with(['product.images'])->orderBy('sort_order')->latest()->paginate(20);

        return view('admin.banners.index', compact('banners'));
    }

    public function create(): View
    {
        return view('admin.banners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeProductId($request);
        $data = $this->validateData($request, true);
        $data['product_id'] = $request->filled('product_id') ? $request->integer('product_id') : null;

        if (! $data['product_id'] && ! $request->hasFile('image')) {
            return back()->withErrors(['product_id' => 'Chọn sản phẩm hoặc tải ảnh banner.'])->withInput();
        }

        if ($data['product_id']) {
            $product = Product::query()->findOrFail($data['product_id']);
            $data['image_path'] = null;
            if (empty($data['link_url'])) {
                $data['link_url'] = route('products.show', $product->slug, absolute: false);
            }
        } else {
            $data['product_id'] = null;
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }

        Banner::query()->create($data);

        return redirect()->route('admin.banners.index')->with('status', 'Đã tạo banner.');
    }

    public function edit(Banner $banner): View
    {
        $banner->load('product');

        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $this->normalizeProductId($request);
        $data = $this->validateData($request, false);
        $data['product_id'] = $request->filled('product_id') ? $request->integer('product_id') : null;

        if ($data['product_id']) {
            $product = Product::query()->findOrFail($data['product_id']);
            if (! empty($banner->image_path) && ! preg_match('#^https?://#i', (string) $banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $data['image_path'] = null;
            if (empty($data['link_url'])) {
                $data['link_url'] = route('products.show', $product->slug, absolute: false);
            }
        } elseif ($request->hasFile('image')) {
            $data['product_id'] = null;
            if (! empty($banner->image_path) && ! preg_match('#^https?://#i', (string) $banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $data['image_path'] = $request->file('image')->store('banners', 'public');
        } else {
            unset($data['image_path']);
            if (! $data['product_id'] && empty($banner->image_path)) {
                return back()->withErrors(['product_id' => 'Chọn sản phẩm hoặc tải ảnh banner.'])->withInput();
            }
            if (! $data['product_id']) {
                unset($data['product_id']);
            }
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('status', 'Đã cập nhật banner.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if (! empty($banner->image_path) && ! preg_match('#^https?://#i', (string) $banner->image_path)) {
            Storage::disk('public')->delete($banner->image_path);
        }
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', 'Đã xóa banner.');
    }

    private function validateData(Request $request, bool $isCreate): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'link_url' => [
                'nullable',
                'string',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    if (! is_string($value)) {
                        $fail('The :attribute must be a string, URL, or path starting with /.');

                        return;
                    }
                    if (str_starts_with($value, '/')) {
                        return;
                    }
                    if (filter_var($value, FILTER_VALIDATE_URL)) {
                        return;
                    }
                    $fail('The :attribute must be a valid URL or an absolute path starting with /.');
                },
            ],
            'sort_order' => 'integer|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:4096',
        ];
        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active', true);
        unset($data['image']);

        return $data;
    }

    /** Normalize empty string from hidden inputs so validation and DB store null correctly. */
    private function normalizeProductId(Request $request): void
    {
        $raw = $request->input('product_id');
        if ($raw === null || $raw === '') {
            $request->merge(['product_id' => null]);

            return;
        }
        $id = (int) $raw;
        $request->merge(['product_id' => $id > 0 ? $id : null]);
    }
}
