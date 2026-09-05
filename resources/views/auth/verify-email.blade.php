<x-guest-layout>
    <div class="mb-6">
        <h1 class="shop-heading text-3xl font-semibold text-[color:var(--brand)]">Xác minh email</h1>
    </div>
    <div class="mb-4 text-sm text-[color:var(--text-muted)]">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 border border-[color:var(--line-soft)] bg-[#f5f2ea] px-3 py-2 text-sm font-medium text-[color:var(--brand)]">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm text-[color:var(--brand)] underline underline-offset-2 hover:no-underline focus:outline-none">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
