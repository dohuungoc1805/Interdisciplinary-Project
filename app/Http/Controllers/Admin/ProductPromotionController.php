<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductPromotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductPromotionController extends Controller
{
    public function index(): View
    {
        $promotions = ProductPromotion::query()->with('category')->latest()->paginate(20);

        return view('admin.promotions.index', compact('promotions'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.promotions.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'type' => 'required|string|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'category_id' => 'nullable|integer|exists:categories,id',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        ProductPromotion::query()->create($data);

        return redirect()->route('admin.promotions.index')->with('status', 'Đã tạo chương trình khuyến mãi.');
    }

    public function edit(ProductPromotion $promotion): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.promotions.edit', compact('promotion', 'categories'));
    }

    public function update(Request $request, ProductPromotion $promotion): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'type' => 'required|string|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'category_id' => 'nullable|integer|exists:categories,id',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $promotion->update($data);

        return redirect()->route('admin.promotions.index')->with('status', 'Đã cập nhật chương trình khuyến mãi.');
    }
}
