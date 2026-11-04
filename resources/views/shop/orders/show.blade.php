@extends('layouts.shop')

@section('title', $order->order_number.' — '.config('app.name'))

@section('content')
    <a href="{{ route('purchases.index') }}" class="text-sm font-medium text-[color:var(--accent)] hover:underline">&larr; Danh sách đơn mua</a>
    <h1 class="shop-heading mt-3 text-2xl font-bold text-[color:var(--primary)]">{{ $order->order_number }}</h1>
    <p class="mt-2 text-[color:var(--text-muted)]">
        Trạng thái: <strong class="text-[color:var(--primary)]">{{ $order->statusEnum()?->label() ?? $order->status }}</strong>
        @if($order->tracking_number) — Mã vận đơn: {{ $order->tracking_number }}@endif
    </p>
    <x-order-status-timeline :status="$order->status" />
    <div class="shop-shell mt-6 space-y-1 p-4 text-sm">
        <p class="text-[color:var(--primary)]">{{ $order->recipient_name }} — {{ $order->phone }}</p>
        <p>{{ $order->line1 }}@if($order->line2), {{ $order->line2 }}@endif</p>
        <p>{{ $order->city }} {{ $order->postal_code }} {{ $order->country }}</p>
    </div>
    <h2 class="mt-8 font-semibold text-[color:var(--primary)]">Sản phẩm</h2>
    <ul class="shop-shell mt-2 divide-y divide-[color:var(--line-soft)]">
        @foreach($order->items as $i)
            <li class="flex justify-between gap-4 p-4 text-sm">
                <span>
                    @if($i->product)
                        <a href="{{ route('products.show', $i->product->slug) }}" class="font-medium text-[color:var(--primary)] underline-offset-2 hover:text-[color:var(--accent)] hover:underline">
                            {{ $i->name }}
                        </a>
                    @else
                        {{ $i->name }}
                    @endif
                    @if($i->size) ({{ $i->size }} / {{ $i->color }}) @endif × {{ $i->quantity }}
                </span>
                <span class="shrink-0 font-semibold text-[color:var(--accent)]">{{ number_format((float) $i->line_total, 0, ',', '.') }} ₫</span>
            </li>
        @endforeach
    </ul>
    <p class="mt-4 text-right text-lg font-bold text-[color:var(--primary)]">Tổng cộng: {{ number_format((float) $order->total, 0, ',', '.') }} ₫ (COD)</p>
    @if($order->status === 'completed')
        <section class="shop-shell mt-8 space-y-4 p-5" aria-labelledby="order-review-title">
            <div>
                <h2 id="order-review-title" class="font-semibold text-[color:var(--primary)]">Đánh giá sản phẩm</h2>
                <p class="mt-1 text-sm text-[color:var(--text-muted)]">Chia sẻ trải nghiệm của bạn để giúp cửa hàng phục vụ tốt hơn.</p>
            </div>
            @foreach($order->items->unique('product_id') as $item)
                @if($item->product)
                    <div class="rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-white p-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $item->product->mainImageUrl() }}" alt="{{ $item->product->name }}" class="h-16 w-14 shrink-0 rounded-lg border border-[color:var(--line-soft)] object-cover" />
                            <p class="font-semibold text-[color:var(--primary)]">{{ $item->product->name }}</p>
                        </div>
                        @if(in_array($item->product_id, $reviewedProductIds, true))
                            <p class="mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">Bạn đã gửi đánh giá cho sản phẩm này. Đánh giá đang chờ duyệt hoặc đã được hiển thị.</p>
                        @else
                            <form method="post" action="{{ route('reviews.store', $item->product) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                                @csrf
                                <input type="hidden" name="order_id" value="{{ $order->id }}" />
                                <div class="grid gap-3 sm:grid-cols-[160px_1fr]">
                                    <label class="text-sm font-semibold text-[color:var(--primary)]">Số sao
                                        <select name="rating" class="field-control mt-1" required>
                                            <option value="5">5 sao — Rất hài lòng</option>
                                            <option value="4">4 sao — Hài lòng</option>
                                            <option value="3">3 sao — Bình thường</option>
                                            <option value="2">2 sao — Chưa hài lòng</option>
                                            <option value="1">1 sao — Không hài lòng</option>
                                        </select>
                                    </label>
                                    <label class="text-sm font-semibold text-[color:var(--primary)]">Nhận xét
                                        <textarea name="comment" rows="2" maxlength="2000" class="field-control mt-1" placeholder="Chất lượng, kiểu dáng, giao hàng..."></textarea>
                                    </label>
                                </div>
                                <div class="flex flex-wrap items-end justify-between gap-3">
                                    <label class="text-xs text-[color:var(--text-muted)]">Ảnh hoặc video trải nghiệm (tùy chọn, tối đa 20MB)
                                        <input name="media" type="file" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm" class="mt-1 block max-w-full text-xs" />
                                    </label>
                                    <button type="submit" class="rounded-[var(--radius-ui)] bg-[color:var(--accent)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[color:var(--accent-hover)]">Gửi đánh giá</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endif
            @endforeach
        </section>
    @endif
    <section class="shop-shell mt-6 p-5" aria-labelledby="payment-title">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 id="payment-title" class="font-semibold text-[color:var(--primary)]">Thanh toán</h2>
                <p class="mt-1 text-sm text-[color:var(--text-muted)]">{{ $order->paymentMethodLabel() }}</p>
            </div>
            <span @class([
                'px-3 py-1 text-xs font-semibold',
                'bg-emerald-100 text-emerald-800' => $order->payment_status === 'paid',
                'bg-amber-100 text-amber-900' => $order->payment_status !== 'paid',
            ])>{{ $order->paymentStatusLabel() }}</span>
        </div>
        @if($order->payment_method === 'bank_transfer' && $order->payment_status !== 'paid')
            @if($bankTransferDetails)
                <div class="mt-5 grid gap-6 sm:grid-cols-[300px_1fr] sm:items-center">
                    <img src="{{ $bankTransferDetails['qr_url'] }}" alt="Mã QR chuyển khoản cho đơn {{ $order->order_number }}" class="mx-auto w-full max-w-[300px] border border-[color:var(--line-soft)] bg-white p-3" />
                    <dl class="space-y-2 text-sm">
                        <div class="flex flex-wrap justify-between gap-2"><dt class="text-[color:var(--text-muted)]">Ngân hàng</dt><dd class="font-semibold text-[color:var(--primary)]">{{ $bankTransferDetails['bank_name'] }}</dd></div>
                        <div class="flex flex-wrap justify-between gap-2"><dt class="text-[color:var(--text-muted)]">Số tài khoản</dt><dd class="font-semibold text-[color:var(--primary)]">{{ $bankTransferDetails['account_number'] }}</dd></div>
                        <div class="flex flex-wrap justify-between gap-2"><dt class="text-[color:var(--text-muted)]">Chủ tài khoản</dt><dd class="font-semibold text-[color:var(--primary)]">{{ $bankTransferDetails['account_name'] }}</dd></div>
                        <div class="flex flex-wrap justify-between gap-2"><dt class="text-[color:var(--text-muted)]">Số tiền</dt><dd class="font-bold text-[color:var(--accent)]">{{ number_format($bankTransferDetails['amount'], 0, ',', '.') }} ₫</dd></div>
                        <div class="flex flex-wrap justify-between gap-2"><dt class="text-[color:var(--text-muted)]">Nội dung chuyển khoản</dt><dd class="font-mono font-semibold text-[color:var(--primary)]">{{ $bankTransferDetails['transfer_content'] }}</dd></div>
                    </dl>
                </div>
                <p class="mt-5 text-xs leading-relaxed text-[color:var(--text-muted)]">Vui lòng không thay đổi số tiền hoặc nội dung chuyển khoản. Cửa hàng sẽ kiểm tra giao dịch trước khi chuẩn bị đơn.</p>
                <div class="mt-5 rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-white p-4">
                    <h3 class="font-semibold text-[color:var(--primary)]">Gửi ảnh xác nhận chuyển khoản</h3>
                    <p class="mt-1 text-sm text-[color:var(--text-muted)]">Sau khi chuyển khoản, tải ảnh biên lai hoặc màn hình giao dịch thành công để cửa hàng đối soát. Ảnh JPG, PNG hoặc WEBP, tối đa 8MB.</p>
                    @if($order->payment_proof_path)
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <a href="{{ $order->paymentProofUrl() }}" target="_blank" rel="noopener">
                                <img src="{{ $order->paymentProofUrl() }}" alt="Ảnh xác nhận chuyển khoản" class="h-24 w-32 rounded-lg border border-[color:var(--line-soft)] object-cover" />
                            </a>
                            <span class="text-xs text-amber-700">Đã gửi lúc {{ $order->payment_proof_submitted_at?->format('d/m/Y H:i') }} — đang chờ cửa hàng xác minh.</span>
                        </div>
                    @endif
                    <form method="post" action="{{ route('orders.payment-proof', $order) }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-end gap-3">
                        @csrf
                        <label class="text-sm font-medium text-[color:var(--primary)]">{{ $order->payment_proof_path ? 'Tải ảnh mới' : 'Chọn ảnh giao dịch' }}
                            <input name="payment_proof" type="file" accept="image/jpeg,image/png,image/webp" required class="mt-1 block max-w-full text-xs" />
                        </label>
                        <button type="submit" class="rounded-[var(--radius-ui)] bg-[color:var(--accent)] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[color:var(--accent-hover)]">Gửi ảnh xác nhận</button>
                    </form>
                </div>
            @else
                <p class="mt-3 text-sm text-[color:var(--text-muted)]">Thông tin chuyển khoản đang được cửa hàng cập nhật. Vui lòng liên hệ cửa hàng để được hỗ trợ.</p>
            @endif
        @elseif($order->payment_method === 'bank_transfer')
            <p class="mt-3 text-sm text-emerald-700">Cửa hàng đã xác nhận thanh toán lúc {{ $order->payment_confirmed_at?->format('d/m/Y H:i') }}.</p>
        @endif
    </section>
    @if($order->status === 'pending' || $order->status === 'processing')
        <form method="post" action="{{ route('orders.cancel', $order) }}" class="mt-6">@csrf
            <button class="text-sm font-semibold text-red-600 hover:underline" type="submit" onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">Hủy đơn hàng</button>
        </form>
    @endif
@endsection
