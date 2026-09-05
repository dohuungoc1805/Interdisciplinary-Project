<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center border border-[color:var(--line-soft)] bg-transparent px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.16em] text-[color:var(--brand)] transition hover:bg-[#ebe6db] focus:outline-none focus:ring-0 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
