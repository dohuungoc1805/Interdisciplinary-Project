@props([
    'variant' => 'header',
])

<a
    {{ $attributes->merge(['href' => route('home')])->class([
        'nd-brand-logo group shrink-0',
        $variant === 'footer' ? 'nd-brand-logo--footer' : false,
    ]) }}
>
    <span class="nd-logo-mark" aria-hidden="true">
        <img src="{{ asset('images/brand/qudena-qn.png') }}" alt="" />
    </span>
    <span class="nd-logo-text">
        <span class="nd-logo-line1">QUDENA</span>
        <span class="nd-logo-line2">THỜI TRANG</span>
    </span>
</a>
