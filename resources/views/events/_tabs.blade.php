<nav class="mb-6 flex flex-wrap items-center gap-1 border-b border-slate-200 text-sm">
    @php
        $current = request()->route()->getName();
    @endphp
    <a href="{{ route('events.show', $event) }}"
       class="px-3 py-2 -mb-px border-b-2 {{ $current === 'events.show' ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
        Detail
    </a>
    <a href="{{ route('events.participants.index', $event) }}"
       class="px-3 py-2 -mb-px border-b-2 {{ str_starts_with($current, 'events.participants.') ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
        Peserta <span class="ml-1 text-slate-400">({{ $event->participants_count ?? $event->participants()->count() }})</span>
    </a>
    <a href="{{ route('events.attendances.index', $event) }}"
       class="px-3 py-2 -mb-px border-b-2 {{ str_starts_with($current, 'events.attendances.') ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
        Daftar Kehadiran
    </a>
</nav>
