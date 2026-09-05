<x-guest-layout>
    <form method="POST" action="{{ route('login') }}" class="w-full max-w-[500px] rounded-3xl border border-white/30 bg-[linear-gradient(152deg,rgba(255,255,255,0.13),rgba(255,255,255,0.04))] p-8 sm:p-10 shadow-[0_28px_70px_rgba(0,0,0,0.42),inset_0_1px_0_rgba(255,255,255,0.2)] backdrop-blur-[18px]" id="login-form">
        @csrf

        <div class="mb-7">
            <p class="mb-3 text-[0.78rem] font-extrabold uppercase text-[#2fc8bb]">Tài khoản thành viên</p>
            <h2 class="text-[clamp(2.05rem,5vw,2.7rem)] leading-none tracking-normal text-white">Đăng nhập</h2>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <label class="mb-4 block">
            <span class="mb-2 block text-[0.92rem] font-[650] text-white/75">Email</span>
            <x-text-input id="email" class="block min-h-[54px] w-full rounded-lg border border-white/25 bg-white/10 px-4 text-[color:var(--text-main)] placeholder:text-white/50 focus:border-[#2fc8bb] focus:bg-white/15 focus:ring-4 focus:ring-[#2fc8bb]/20" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="ten@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </label>

        <label class="relative mb-4 block">
            <span class="mb-2 block text-[0.92rem] font-[650] text-white/75">Mật khẩu</span>
            <x-text-input id="password" class="block min-h-[54px] w-full rounded-lg border border-white/25 bg-white/10 px-4 pr-[74px] text-[color:var(--text-main)] placeholder:text-white/50 focus:border-[#2fc8bb] focus:bg-white/15 focus:ring-4 focus:ring-[#2fc8bb]/20" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <button class="absolute bottom-2 right-2 min-h-[38px] min-w-[58px] rounded-lg bg-white/15 px-3 text-[0.86rem] font-[750] text-white hover:bg-white/25 focus:outline-none" type="button" id="togglePassword" aria-controls="password" aria-label="Hiện mật khẩu">Hiện</button>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </label>

        <div class="mb-6 mt-1 flex items-center justify-between gap-4 text-[0.92rem] text-white/75">
            <label for="remember_me" class="inline-flex items-center gap-[9px]">
                <input id="remember_me" type="checkbox" class="h-[17px] w-[17px] accent-[#d96a5e]" name="remember">
                <span>{{ __('Ghi nhớ tôi') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="font-[750] text-white hover:text-[#2fc8bb]" href="{{ route('password.request') }}">
                    {{ __('Quên mật khẩu?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="inline-flex min-h-[56px] w-full items-center justify-center rounded-lg border-0 bg-gradient-to-br from-[#d96a5e] to-[#b64b45] text-sm font-extrabold text-white shadow-[0_18px_34px_rgba(169,73,67,0.36)] transition hover:-translate-y-px hover:brightness-105">
            {{ __('Đăng nhập') }}
        </x-primary-button>

        @if (Route::has('register'))
            <p class="mt-[22px] text-center text-sm leading-6 text-white/75">
                {{ __('Chưa có tài khoản?') }}
                <a href="{{ route('register') }}" class="font-[750] text-white hover:text-[#2fc8bb]">
                    {{ __('Tạo tài khoản') }}
                </a>
            </p>
        @endif
    </form>

    <script>
        (() => {
            const passwordInput = document.querySelector('#password');
            const togglePassword = document.querySelector('#togglePassword');
            if (!passwordInput || !togglePassword) return;
            togglePassword.addEventListener('click', () => {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                togglePassword.textContent = isHidden ? 'Ẩn' : 'Hiện';
                togglePassword.setAttribute('aria-label', isHidden ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
            });
        })();
    </script>
</x-guest-layout>
