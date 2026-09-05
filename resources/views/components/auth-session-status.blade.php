@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'border border-[color:var(--line-soft)] bg-[#f5f2ea] px-3 py-2 text-sm text-[color:var(--brand)]']) }}>
        {{ $status }}
    </div>
@endif
