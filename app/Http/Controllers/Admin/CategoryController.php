<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()->orderBy('position')->orderBy('name')->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parents = Category::query()->orderBy('name')->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['image_path'] = $request->hasFile('image')
            ? $request->file('image')->store('categories', 'public')
            : null;
        Category::query()->create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Đã tạo danh mục.');
    }

    public function edit(Category $category): View
    {
        $parents = Category::query()->where('id', '!=', $category->id)->orderBy('name')->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        if ($request->hasFile('image')) {
            $this->deleteStoredImage($category);
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        } else {
            unset($data['image_path']);
        }
        $category->update($data);

        return redirect()->route('admin.categories.index')->with('status', 'Đã cập nhật danh mục.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Không xóa được: vẫn còn sản phẩm trong danh mục này.');
        }
        $this->deleteStoredImage($category);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Đã xóa danh mục.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'parent_id' => 'nullable|exists:categories,id',
            'position' => 'integer|min:0',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:4096',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        unset($data['image']);

        return $data;
    }

    private function deleteStoredImage(Category $category): void
    {
        $path = $category->image_path;
        if ($path && ! preg_match('#^https?://#i', (string) $path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Category::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
