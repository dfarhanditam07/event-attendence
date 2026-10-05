@php
    $classes = match ($status) {
        \App\Enums\EventStatus::Open => 'bg-emerald-100 text-emerald-700',
        \App\Enums\EventStatus::Closed => 'bg-slate-200 text-slate-600',
        default => 'bg-amber-100 text-amber-700',
    };
@endphp
<span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $classes }}">{{ $status->label() }}</span>
