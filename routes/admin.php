<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductPromotionController;
use App\Http\Controllers\Admin\PurchaseReceiptController;
use App\Http\Controllers\Admin\ReviewModerationController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/admin', '/admin/dashboard');

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::get('products/quick-search', [ProductController::class, 'quickSearch'])->name('products.quick-search');
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('products/template', [ProductController::class, 'template'])->name('products.template');
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('coupons', CouponController::class)->except(['show']);
    Route::resource('banners', BannerController::class)->except(['show']);
    Route::resource('promotions', ProductPromotionController::class)->except(['show', 'destroy']);
    Route::get('purchases', [PurchaseReceiptController::class, 'index'])->name('purchases.index');
    Route::get('purchases/create', [PurchaseReceiptController::class, 'create'])->name('purchases.create');
    Route::post('purchases', [PurchaseReceiptController::class, 'store'])->name('purchases.store');
    Route::get('purchases/{purchase}', [PurchaseReceiptController::class, 'show'])->name('purchases.show');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::post('/orders/{order}/confirm-bank-transfer', [OrderController::class, 'confirmBankTransfer'])->name('orders.confirm-bank-transfer');
    Route::post('/orders/bulk-status', [OrderController::class, 'bulkStatus'])->name('orders.bulk');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/toggle-block', [UserController::class, 'toggleBlock'])->name('users.toggle-block');

    Route::get('/reviews', [ReviewModerationController::class, 'index'])->name('reviews-moderation.index');
    Route::post('/reviews/{review}/approve', [ReviewModerationController::class, 'approve'])->name('reviews-moderation.approve');
    Route::delete('/reviews/{review}', [ReviewModerationController::class, 'reject'])->name('reviews-moderation.reject');
});
