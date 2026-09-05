@props(['href', 'active' => false])
@php
    $base = 'flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition';
    $state = $active
        ? 'bg-slate-800 text-white shadow-inner ring-1 ring-orange-500/40'
        : 'text-slate-300 hover:bg-slate-800/90 hover:text-white';
@endphp
<a href="{{ $href }}" {{ $attributes->merge(['class' => $base.' '.$state]) }}>
    {{ $slot }}
</a>
