<section class="shop-shell border-red-100 bg-red-50/40 p-5 sm:p-6">
    <header class="mb-4">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-red-800">Xóa tài khoản</h2>
        <p class="mt-2 text-sm text-[color:var(--text-muted)]">Khi xóa, mọi dữ liệu tài khoản sẽ bị gỡ vĩnh viễn. Hãy sao lưu thông tin cần thiết trước khi thực hiện.</p>
    </header>

    <button
        type="button"
        class="rounded-[var(--radius-ui)] border border-red-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-800 shadow-sm transition hover:bg-red-50"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        Xóa tài khoản
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h2 class="shop-heading text-xl font-bold text-[color:var(--primary)]">Xác nhận xóa tài khoản?</h2>
            <p class="mt-2 text-sm text-[color:var(--text-muted)]">Nhập mật khẩu hiện tại để xác nhận. Thao tác này không thể hoàn tác.</p>

            <div class="mt-6">
                <label for="delete-account-password" class="sr-only">Mật khẩu</label>
                <input
                    id="delete-account-password"
                    name="password"
                    type="password"
                    class="field-control max-w-md"
                    placeholder="Mật khẩu hiện tại"
                    autocomplete="current-password"
                />
                @if ($errors->userDeletion->has('password'))
                    <p class="mt-2 text-sm text-red-600">{{ $errors->userDeletion->first('password') }}</p>
                @endif
            </div>

            <div class="mt-8 flex flex-wrap justify-end gap-3">
                <button
                    type="button"
                    class="rounded-[var(--radius-ui)] border-2 border-[color:var(--line-soft)] px-4 py-2.5 text-sm font-semibold text-[color:var(--primary)] transition hover:bg-[#f7f7f9]"
                    x-on:click="$dispatch('close')"
                >
                    Hủy
                </button>
                <button type="submit" class="rounded-[var(--radius-ui)] bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
                    Xóa vĩnh viễn
                </button>
            </div>
        </form>
    </x-modal>
</section>
