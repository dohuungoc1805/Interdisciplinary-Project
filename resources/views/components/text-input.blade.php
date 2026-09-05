@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full border border-[color:var(--line-soft)] bg-[color:var(--surface)] px-3 py-2 text-sm text-[color:var(--text-main)] placeholder:text-stone-400 focus:border-[color:var(--brand)] focus:outline-none focus:ring-0']) }}>
