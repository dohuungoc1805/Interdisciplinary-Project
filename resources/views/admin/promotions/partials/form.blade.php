<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label class="admin-label" for="name">Tên chương trình</label>
        <input id="name" name="name" class="admin-input" required value="{{ old('name', $promotion?->name) }}" />
    </div>

    <div>
        <label class="admin-label" for="type">Kiểu giảm</label>
        <select id="type" name="type" class="admin-input" required>
            <option value="percent" @selected(old('type', $promotion?->type) === 'percent')>Phần trăm (%)</option>
            <option value="fixed" @selected(old('type', $promotion?->type) === 'fixed')>Số tiền cố định (₫)</option>
        </select>
    </div>

    <div>
        <label class="admin-label" for="value">Giá trị giảm</label>
        <input id="value" type="number" step="0.01" min="0" name="value" class="admin-input" required value="{{ old('value', $promotion?->value) }}" />
    </div>

    <div>
        <label class="admin-label" for="category_id">Danh mục áp dụng</label>
        <select id="category_id" name="category_id" class="admin-input">
            <option value="">Toàn bộ sản phẩm</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $promotion?->category_id) === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex items-center gap-2 pt-7">
        <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $promotion?->is_active ?? true)) />
        <label class="admin-label !mb-0" for="is_active">Kích hoạt</label>
    </div>

    <div>
        <label class="admin-label" for="starts_at">Bắt đầu</label>
        <input id="starts_at" type="datetime-local" name="starts_at" class="admin-input" value="{{ old('starts_at', $promotion?->starts_at?->format('Y-m-d\TH:i')) }}" />
    </div>

    <div>
        <label class="admin-label" for="ends_at">Kết thúc</label>
        <input id="ends_at" type="datetime-local" name="ends_at" class="admin-input" value="{{ old('ends_at', $promotion?->ends_at?->format('Y-m-d\TH:i')) }}" />
    </div>
</div>
