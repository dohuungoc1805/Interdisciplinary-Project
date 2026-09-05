<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center border border-red-700 bg-red-700 px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.16em] text-white transition hover:bg-red-800 focus:outline-none focus:ring-0']) }}>
    {{ $slot }}
</button>
