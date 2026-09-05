<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="w-full max-w-[500px] rounded-3xl border border-white/30 bg-[linear-gradient(152deg,rgba(255,255,255,0.13),rgba(255,255,255,0.04))] p-8 sm:p-10 shadow-[0_28px_70px_rgba(0,0,0,0.42),inset_0_1px_0_rgba(255,255,255,0.2)] backdrop-blur-[18px]" id="register-form">
        @csrf

        <div class="mb-7">
            <p class="mb-3 text-[0.78rem] font-extrabold uppercase text-[#57d8ce]">Tài khoản thành viên</p>
            <h1 class="text-[clamp(2.05rem,5vw,2.7rem)] leading-none tracking-normal text-white">Đăng ký tài khoản</h1>
            <p class="mt-3 text-sm text-white/75">Lưu địa chỉ, theo dõi đơn hàng và quản lý yêu thích.</p>
        </div>

        <label class="mb-4 block">
            <span class="mb-2 block text-[0.92rem] font-[650] text-white/75">Họ tên</span>
            <x-text-input id="name" class="block min-h-[54px] w-full rounded-lg border border-white/25 bg-white/10 px-4 text-[color:var(--text-main)] placeholder:text-white/50 focus:border-[#57d8ce] focus:bg-white/15 focus:ring-4 focus:ring-[#57d8ce]/20" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nhập họ tên của bạn" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </label>

        <label class="mb-4 block">
            <span class="mb-2 block text-[0.92rem] font-[650] text-white/75">Email</span>
            <x-text-input id="email" class="block min-h-[54px] w-full rounded-lg border border-white/25 bg-white/10 px-4 text-[color:var(--text-main)] placeholder:text-white/50 focus:border-[#57d8ce] focus:bg-white/15 focus:ring-4 focus:ring-[#57d8ce]/20" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="ten@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </label>

        <label class="mb-4 block">
            <span class="mb-2 block text-[0.92rem] font-[650] text-white/75">Mật khẩu</span>
            <x-text-input id="password" class="block min-h-[54px] w-full rounded-lg border border-white/25 bg-white/10 px-4 text-[color:var(--text-main)] placeholder:text-white/50 focus:border-[#57d8ce] focus:bg-white/15 focus:ring-4 focus:ring-[#57d8ce]/20" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </label>

        <label class="mb-6 block">
            <span class="mb-2 block text-[0.92rem] font-[650] text-white/75">Xác nhận mật khẩu</span>
            <x-text-input id="password_confirmation" class="block min-h-[54px] w-full rounded-lg border border-white/25 bg-white/10 px-4 text-[color:var(--text-main)] placeholder:text-white/50 focus:border-[#57d8ce] focus:bg-white/15 focus:ring-4 focus:ring-[#57d8ce]/20" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </label>

        <x-primary-button class="inline-flex min-h-[56px] w-full items-center justify-center rounded-lg border-0 bg-gradient-to-br from-[#d55368] to-[#cf4f63] text-sm font-extrabold uppercase tracking-[0.2em] text-white shadow-[0_18px_34px_rgba(177,73,95,0.34)] transition hover:-translate-y-px hover:brightness-105">
            {{ __('Đăng ký') }}
        </x-primary-button>

        <div class="mt-8 border-t border-white/30 pt-7 text-center">
            <p class="text-sm text-white/70">
                {{ __('Đã có tài khoản?') }}
                <a href="{{ route('login') }}" class="font-extrabold text-[#ff6a86] underline underline-offset-4 hover:text-[#ff89a0]">
                    {{ __('Đăng nhập ngay') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
