@php
    $isLg = ($size ?? 'sm') === 'lg';

    $config = match ($status) {
        \App\Enums\EventStatus::Open => [
            'badge' => 'border-emerald-200/90 bg-emerald-50 text-emerald-800 ring-1 ring-emerald-500/10',
            'dot' => 'bg-emerald-500',
            'pulse' => true,
        ],
        \App\Enums\EventStatus::Closed => [
            'badge' => 'border-slate-200 bg-slate-100 text-slate-600 ring-1 ring-slate-500/5',
            'dot' => 'bg-slate-400',
            'pulse' => false,
        ],
        default => [
            'badge' => 'border-amber-200/90 bg-amber-50 text-amber-800 ring-1 ring-amber-500/10',
            'dot' => 'bg-amber-500',
            'pulse' => false,
        ],
    };
@endphp
<span class="inline-flex items-center gap-1.5 rounded-full border font-semibold tracking-wide {{ $config['badge'] }} {{ $isLg ? 'px-3.5 py-1 text-sm' : 'px-2.5 py-0.5 text-xs' }}">
    <span class="relative flex {{ $isLg ? 'h-2 w-2' : 'h-1.5 w-1.5' }}">
        @if($config['pulse'])
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full {{ $config['dot'] }} opacity-75"></span>
        @endif
        <span class="relative inline-flex h-full w-full rounded-full {{ $config['dot'] }}"></span>
    </span>
    <span>{{ $status->label() }}</span>
</span>
