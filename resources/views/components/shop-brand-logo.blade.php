@props([
    'variant' => 'header',
])

@php
    $brandName = trim((string) config('app.name', 'Shop'));
    $parts = array_values(array_filter(preg_split('/\s+/u', $brandName), fn ($p) => $p !== ''));

    if (count($parts) >= 2) {
        $mono = mb_strtoupper(
            mb_substr($parts[0], 0, 1, 'UTF-8').mb_substr($parts[1], 0, 1, 'UTF-8'),
            'UTF-8'
        );
    } else {
        $len = mb_strlen($brandName, 'UTF-8');
        $mono = mb_strtoupper(mb_substr($brandName, 0, min(2, $len), 'UTF-8'), 'UTF-8');
    }

    $word1 = $brandName;
    $word2 = null;
    $word2IsFashion = false;
    if (count($parts) >= 2 && strcasecmp($parts[count($parts) - 1], 'Fashion') === 0) {
        $word1 = implode(' ', array_slice($parts, 0, -1));
        $word2 = $parts[count($parts) - 1];
        $word2IsFashion = true;
    }
@endphp

<a
    {{ $attributes->merge(['href' => route('home')])->class([
        'nd-brand-logo group shrink-0',
        $variant === 'footer' ? 'nd-brand-logo--footer' : false,
    ]) }}
>
    <span class="nd-logo-mark" aria-hidden="true">{{ $mono }}</span>
    <span class="nd-logo-text">
        <span class="nd-logo-line1">{{ $word1 }}</span>
        @if($word2 !== null)
            <span class="nd-logo-line2 nd-logo-line2--brand">{{ $word2 }}</span>
        @else
            <span class="nd-logo-line2">Thời trang</span>
        @endif
    </span>
</a>
