@php
    $classes = match ($status) {
        \App\Enums\EventStatus::Open => 'bg-emerald-100 text-emerald-700',
        \App\Enums\EventStatus::Closed => 'bg-slate-200 text-slate-600',
        default => 'bg-amber-100 text-amber-700',
    };

    $sizeClasses = ($size ?? 'sm') === 'lg'
        ? 'px-4 py-1.5 text-base'
        : 'px-2.5 py-0.5 text-xs';
@endphp
<span class="inline-block rounded-full font-medium {{ $sizeClasses }} {{ $classes }}">{{ $status->label() }}</span>
