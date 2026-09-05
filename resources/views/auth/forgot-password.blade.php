<x-guest-layout>
    <div class="mb-6">
        <h1 class="shop-heading text-3xl font-semibold text-[color:var(--brand)]">Đặt lại mật khẩu</h1>
        <p class="mt-2 text-sm text-[color:var(--text-muted)]">
            {{ __('Enter your email and we will send you a password reset link.') }}
        </p>
    </div>
    <div class="mb-4 text-sm text-[color:var(--text-muted)]">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-4 mt-4">
            <div class="flex-col gap-2">
                <x-primary-button class="w-full">
                    {{ __('Email Password Reset Link') }}
                </x-primary-button>
            </div>

            <div class="flex gap-4 text-sm">
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="text-[color:var(--brand)] underline underline-offset-2 hover:no-underline focus:outline-none">
                        {{ __('Back to Login') }}
                    </a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="text-[color:var(--brand)] underline underline-offset-2 hover:no-underline focus:outline-none">
                        {{ __('Register') }}
                    </a>
                @endif
            </div>
        </div>
    </form>
</x-guest-layout>
