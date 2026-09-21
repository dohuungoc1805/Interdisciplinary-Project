@props(['status'])

@php
    $steps = [
        ['status' => 'pending', 'label' => 'Tiếp nhận'],
        ['status' => 'processing', 'label' => 'Chuẩn bị hàng'],
        ['status' => 'shipping', 'label' => 'Đang giao'],
        ['status' => 'completed', 'label' => 'Hoàn thành'],
    ];
    $currentStep = array_search($status, array_column($steps, 'status'), true);
@endphp

@if($status === 'cancelled')
    <div class="mt-6 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        Đơn hàng này đã được hủy. Nếu cần hỗ trợ thêm, vui lòng liên hệ cửa hàng.
    </div>
@else
    <ol class="mt-6 grid grid-cols-4 gap-1" aria-label="Tiến trình đơn hàng">
        @foreach($steps as $index => $step)
            @php($isReached = $currentStep !== false && $index <= $currentStep)
            <li class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span @class([
                        'flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                        'bg-[color:var(--accent)] text-white' => $isReached,
                        'bg-[#e5e7eb] text-[#6b7280]' => ! $isReached,
                    ])>{{ $index + 1 }}</span>
                    @if(! $loop->last)
                        <span @class([
                            'h-0.5 w-full',
                            'bg-[color:var(--accent)]' => $currentStep !== false && $index < $currentStep,
                            'bg-[#e5e7eb]' => $currentStep === false || $index >= $currentStep,
                        ])></span>
                    @endif
                </div>
                <p @class([
                    'mt-2 pr-1 text-xs leading-tight',
                    'font-semibold text-[color:var(--primary)]' => $isReached,
                    'text-[color:var(--text-muted)]' => ! $isReached,
                ])>{{ $step['label'] }}</p>
            </li>
        @endforeach
    </ol>
@endif
