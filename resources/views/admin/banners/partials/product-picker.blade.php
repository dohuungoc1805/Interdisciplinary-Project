@props([
    'selectedProductId' => null,
    'selectedProductName' => '',
])
@php
    $hiddenValue = old('product_id', $selectedProductId !== null && $selectedProductId !== '' ? (string) $selectedProductId : '');
@endphp
<div
    x-data="{
        productId: @js($selectedProductId !== null && $selectedProductId !== '' ? (int) $selectedProductId : null),
        label: @js($selectedProductName ?? ''),
        q: '',
        results: [],
        loading: false,
        url: @js(route('admin.products.quick-search')),
        syncHidden() {
            const el = document.getElementById('admin-banner-product-id');
            if (! el) return;
            el.value = this.productId == null ? '' : String(this.productId);
        },
        async search() {
            this.loading = true;
            try {
                const r = await fetch(this.url + '?q=' + encodeURIComponent(this.q), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                const j = await r.json();
                this.results = Array.isArray(j.data) ? j.data : [];
            } catch (e) {
                this.results = [];
            }
            this.loading = false;
        },
        pick(p) {
            this.productId = p.id;
            this.label = p.name;
            this.results = [];
            this.q = '';
            this.syncHidden();
        },
        clear() {
            this.productId = null;
            this.label = '';
            this.results = [];
            this.syncHidden();
        },
    }"
    x-init="syncHidden()"
    class="space-y-3 rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4 shadow-sm"
>
    <input type="hidden" name="product_id" id="admin-banner-product-id" value="{{ $hiddenValue }}" autocomplete="off" />
    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ảnh từ sản phẩm</p>
    <p class="text-xs text-slate-600">Chọn sản phẩm để dùng ảnh chính hoặc ảnh gallery đầu tiên. Bạn vẫn có thể tải ảnh riêng bên dưới.</p>
    <div class="flex flex-wrap items-center gap-2">
        <input
            type="text"
            x-model="q"
            @input.debounce.300ms="search()"
            class="admin-input min-w-[12rem] flex-1"
            placeholder="Gõ tên hoặc SKU…"
            autocomplete="off"
        />
        <button type="button" class="admin-btn-outline shrink-0 border-red-200 text-red-700 hover:bg-red-50" @click="clear()">Bỏ chọn</button>
    </div>
    <p x-show="label" class="text-sm text-slate-800"><span class="text-slate-500">Đã chọn:</span> <strong x-text="label"></strong></p>
    <ul x-show="results.length" x-cloak class="max-h-48 divide-y divide-slate-100 overflow-y-auto rounded-lg border border-slate-200 bg-white text-sm shadow-inner">
        <template x-for="item in results" :key="item.id">
            <li>
                <button type="button" class="w-full px-3 py-2.5 text-left transition hover:bg-orange-50/80" @click="pick(item)" x-text="item.name"></button>
            </li>
        </template>
    </ul>
    <p x-show="loading" class="text-xs text-slate-500">Đang tìm…</p>
</div>
