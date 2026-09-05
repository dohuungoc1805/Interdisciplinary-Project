<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center border border-[color:var(--brand)] bg-[color:var(--brand)] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.16em] text-[color:var(--surface)] transition hover:bg-[color:var(--brand-deep)] focus:outline-none focus:ring-0']) }}>
    {{ $slot }}
</button>
