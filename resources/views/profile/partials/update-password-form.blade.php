<section class="shop-shell p-5 sm:p-6">
    <header class="mb-6">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-[color:var(--text-muted)]">Đổi mật khẩu</h2>
        <p class="mt-2 text-sm text-[color:var(--text-muted)]">Dùng mật khẩu dài, khó đoán để bảo vệ tài khoản.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4">@csrf @method('put')
        <div>
            <label for="update_password_current_password" class="mb-1 block text-sm font-semibold text-[color:var(--primary)]">Mật khẩu hiện tại</label>
            <input id="update_password_current_password" name="current_password" type="password" class="field-control" autocomplete="current-password" />
            @if ($errors->updatePassword->has('current_password'))
                <p class="mt-1 text-sm text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password" class="mb-1 block text-sm font-semibold text-[color:var(--primary)]">Mật khẩu mới</label>
            <input id="update_password_password" name="password" type="password" class="field-control" autocomplete="new-password" />
            @if ($errors->updatePassword->has('password'))
                <p class="mt-1 text-sm text-red-600">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="mb-1 block text-sm font-semibold text-[color:var(--primary)]">Xác nhận mật khẩu</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="field-control" autocomplete="new-password" />
            @if ($errors->updatePassword->has('password_confirmation'))
                <p class="mt-1 text-sm text-red-600">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit" class="primary-cta">Cập nhật mật khẩu</button>
        </div>
    </form>
</section>
