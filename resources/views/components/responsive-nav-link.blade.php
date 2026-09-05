@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-2 border-[color:var(--brand)] bg-[#ebe6db] ps-3 pe-4 py-2 text-start text-base font-medium text-[color:var(--brand)] focus:outline-none transition'
            : 'block w-full border-l-2 border-transparent ps-3 pe-4 py-2 text-start text-base font-medium text-[color:var(--text-muted)] hover:bg-[#f5f2ea] hover:text-[color:var(--brand)] hover:border-[color:var(--line-soft)] focus:outline-none transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
