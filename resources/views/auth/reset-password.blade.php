<x-guest-layout>
    <div class="mb-6">
        <h1 class="shop-heading text-3xl font-semibold text-[color:var(--brand)]">Mật khẩu mới</h1>
        <p class="mt-2 text-sm text-[color:var(--text-muted)]">Set a strong password to keep your account secure.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-4 mt-4">
            <x-primary-button class="w-full">
                {{ __('Reset Password') }}
            </x-primary-button>

            @if (Route::has('login'))
                <div class="text-center pt-4 border-t border-[color:var(--line-soft)]">
                    <a href="{{ route('login') }}" class="text-sm text-[color:var(--brand)] underline underline-offset-2 hover:no-underline focus:outline-none">
                        {{ __('Back to Login') }}
                    </a>
                </div>
            @endif
        </div>
    </form>
</x-guest-layout>
