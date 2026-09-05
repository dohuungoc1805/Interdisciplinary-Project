<section class="nd-newsletter-block" id="nd-newsletter">
    <div class="nd-container">
        <h2 class="shop-heading mb-2 text-2xl font-bold text-white sm:text-3xl">Đăng ký nhận ưu đãi</h2>
        <p class="mb-6 text-sm opacity-90">Nhận thông tin khuyến mãi và bộ sưu tập mới qua email.</p>
        <form method="post" action="{{ route('newsletter.store') }}" class="nd-newsletter-form max-w-lg">
            @csrf
            <input name="email" type="email" required placeholder="Nhập email của bạn..." autocomplete="email">
            <button type="submit">Đăng ký</button>
        </form>
    </div>
</section>

<footer class="nd-footer mt-auto">
    <div class="nd-footer-grid">
        <div>
            <x-shop-brand-logo variant="footer" class="mb-4" />
            <p class="text-sm leading-relaxed opacity-80">Thời trang nam nữ — phong cách hiện đại, chất liệu được chọn lọc và trải nghiệm mua sắm trực tuyến thuận tiện.</p>
        </div>
        <div class="nd-footer-col">
            <h4>Về chúng tôi</h4>
            <a href="{{ route('home') }}">Trang chủ</a>
            <a href="{{ route('products.index') }}">Sản phẩm</a>
            <a href="{{ route('login') }}">Tài khoản</a>
        </div>
        <div class="nd-footer-col">
            <h4>Hỗ trợ</h4>
            <a href="{{ route('purchases.index') }}">Đơn mua / lịch sử</a>
            <a href="{{ route('cart.index') }}">Giỏ hàng</a>
            <a href="{{ route('addresses.index') }}">Địa chỉ giao hàng</a>
        </div>
        <div class="nd-footer-col">
            <h4>Liên hệ</h4>
            <p class="py-1 text-sm opacity-80"><i class="fas fa-envelope mr-2 text-[color:var(--gold)]"></i> {{ config('mail.from.address') ?: 'hello@example.com' }}</p>
            <p class="py-1 text-sm opacity-80"><i class="fas fa-phone-alt mr-2 text-[color:var(--gold)]"></i> {{ config('app.support_phone', '0123 456 789') }}</p>
        </div>
    </div>
    <div class="nd-footer-bottom">
        <div class="nd-container">
            <p>© {{ date('Y') }} {{ config('app.name') }}. Bảo lưu mọi quyền.</p>
        </div>
    </div>
</footer>
