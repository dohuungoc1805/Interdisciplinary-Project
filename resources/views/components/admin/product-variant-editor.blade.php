@props(['variants' => []])

@php
    $rows = collect($variants)
        ->map(fn ($variant) => [
            'size' => $variant['size'] ?? '',
            'color' => $variant['color'] ?? '',
            'stock' => $variant['stock'] ?? 0,
            'price' => $variant['price'] ?? '',
            'sku' => $variant['sku'] ?? '',
        ])
        ->values()
        ->all();

    if (count($rows) === 0) {
        $rows = [['size' => '', 'color' => '', 'stock' => 0, 'price' => '', 'sku' => '']];
    }
@endphp

<section
    class="border-t border-slate-100 pt-5"
    x-data='{
        variants: @json($rows),
        addVariant() {
            this.variants.push({ size: "", color: "", stock: 0, price: "", sku: "" });
        },
        removeVariant(index) {
            if (this.variants.length > 1) this.variants.splice(index, 1);
        }
    }'
>
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-slate-900">Size, màu sắc và tồn kho</h2>
            <p class="mt-1 text-xs leading-relaxed text-slate-500">Mỗi dòng là một tổ hợp size và màu, với số lượng tồn kho riêng.</p>
        </div>
        <button type="button" class="admin-btn-outline !px-3 !py-2 text-xs" x-on:click="addVariant()">+ Thêm size / màu</button>
    </div>

    <div class="space-y-3">
        <template x-for="(variant, index) in variants" :key="index">
            <fieldset class="rounded-lg border border-slate-200 bg-slate-50/70 p-3 sm:p-4">
                <legend class="sr-only">Biến thể sản phẩm</legend>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Biến thể <span x-text="index + 1"></span></p>
                    <button type="button" class="text-xs font-semibold text-red-600 transition hover:text-red-800 disabled:cursor-not-allowed disabled:opacity-40" x-on:click="removeVariant(index)" x-bind:disabled="variants.length === 1">Xóa dòng</button>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="block text-xs font-medium text-slate-600">Size<input class="admin-input mt-1" x-model.trim="variant.size" x-bind:name="'variants[' + index + '][size]'" placeholder="VD: M" required /></label>
                    <label class="block text-xs font-medium text-slate-600">Màu sắc<input class="admin-input mt-1" x-model.trim="variant.color" x-bind:name="'variants[' + index + '][color]'" placeholder="VD: Đen" required /></label>
                    <label class="block text-xs font-medium text-slate-600">Số lượng tồn<input class="admin-input mt-1" x-model.number="variant.stock" x-bind:name="'variants[' + index + '][stock]'" type="number" min="0" max="1000" required /></label>
                    <label class="block text-xs font-medium text-slate-600">Giá riêng<input class="admin-input mt-1" x-model="variant.price" x-bind:name="'variants[' + index + '][price]'" type="number" min="0" step="0.01" placeholder="Dùng giá chung" /></label>
                </div>
                <label class="mt-3 block text-xs font-medium text-slate-600">SKU biến thể <span class="font-normal text-slate-400">(tùy chọn)</span><input class="admin-input mt-1 font-mono text-xs" x-model.trim="variant.sku" x-bind:name="'variants[' + index + '][sku]'" placeholder="VD: AO-HOODIE-M-DEN" /></label>
            </fieldset>
        </template>
    </div>
</section>
