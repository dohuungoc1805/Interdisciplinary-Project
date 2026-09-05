<section class="shop-shell p-5 sm:p-6">
    <header class="mb-6">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-[color:var(--text-muted)]">Thông tin hồ sơ</h2>
        <p class="mt-2 text-sm text-[color:var(--text-muted)]">Cập nhật họ tên, số điện thoại và email đăng nhập.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">@csrf @method('patch')
        <div>
            <label for="name" class="mb-1 block text-sm font-semibold text-[color:var(--primary)]">Họ và tên</label>
            <input id="name" name="name" type="text" class="field-control" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="phone" class="mb-1 block text-sm font-semibold text-[color:var(--primary)]">Điện thoại</label>
            <input id="phone" name="phone" type="text" class="field-control" value="{{ old('phone', $user->phone) }}" autocomplete="tel" />
            @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold text-[color:var(--primary)]">Email</label>
            <input id="email" name="email" type="email" class="field-control" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-[var(--radius-ui)] border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                    <p>Email của bạn chưa được xác minh.</p>
                    <button form="send-verification" type="submit" class="mt-1 font-semibold text-[color:var(--accent)] underline hover:no-underline">Gửi lại email xác minh</button>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-[color:var(--primary)]">Liên kết xác minh mới đã được gửi tới email của bạn.</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit" class="primary-cta">Lưu thông tin</button>
        </div>
    </form>
</section>
