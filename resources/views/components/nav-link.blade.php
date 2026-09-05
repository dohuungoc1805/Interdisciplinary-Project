@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b border-[color:var(--brand)] px-1 pt-1 text-sm font-medium leading-5 text-[color:var(--brand)] focus:outline-none transition'
            : 'inline-flex items-center border-b border-transparent px-1 pt-1 text-sm font-medium leading-5 text-[color:var(--text-muted)] hover:text-[color:var(--brand)] hover:border-[color:var(--line-soft)] focus:outline-none transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
