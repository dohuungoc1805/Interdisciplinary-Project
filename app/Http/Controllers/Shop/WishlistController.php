<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $items = WishlistItem::query()
            ->where('user_id', auth()->id())
            ->whereHas('product')
            ->with(['product.images'])
            ->latest()
            ->get();

        return view('shop.wishlist', compact('items'));
    }

    public function toggle(Product $product): RedirectResponse
    {
        $row = WishlistItem::query()->where('user_id', auth()->id())->where('product_id', $product->id)->first();
        if ($row) {
            $row->delete();
            $msg = 'Removed from wishlist.';
        } else {
            WishlistItem::query()->create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ]);
            $msg = 'Saved to wishlist.';
        }

        return back()->with('status', $msg);
    }
}
