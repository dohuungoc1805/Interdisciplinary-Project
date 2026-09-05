@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-semibold uppercase tracking-[0.14em] text-[color:var(--text-muted)]']) }}>
    {{ $value ?? $slot }}
</label>
