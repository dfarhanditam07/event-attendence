@php
    $current = request()->route()->getName();
    $participantCount = $event->participants_count ?? $event->participants()->count();
@endphp

<div class="mb-6 border-b border-slate-200">
    <nav class="-mb-px flex flex-wrap gap-2 sm:gap-6 text-sm" aria-label="Tabs">
        <a href="{{ route('events.show', $event) }}"
           class="inline-flex items-center gap-2 border-b-2 px-3 py-3 font-semibold transition {{ $current === 'events.show' ? 'border-enc-800 text-enc-950' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $current === 'events.show' ? 'text-enc-700' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path d="M3 9h18M9 21V9"/>
            </svg>
            <span>Detail Event</span>
        </a>

        <a href="{{ route('events.participants.index', $event) }}"
           class="inline-flex items-center gap-2 border-b-2 px-3 py-3 font-semibold transition {{ str_starts_with($current, 'events.participants.') ? 'border-enc-800 text-enc-950' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ str_starts_with($current, 'events.participants.') ? 'text-enc-700' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>Peserta</span>
            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ str_starts_with($current, 'events.participants.') ? 'bg-enc-100 text-enc-800' : 'bg-slate-100 text-slate-600' }}">
                {{ $participantCount }}
            </span>
        </a>

        <a href="{{ route('events.attendances.index', $event) }}"
           class="inline-flex items-center gap-2 border-b-2 px-3 py-3 font-semibold transition {{ str_starts_with($current, 'events.attendances.') ? 'border-enc-800 text-enc-950' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-800' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ str_starts_with($current, 'events.attendances.') ? 'text-enc-700' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 11l3 3L22 4"/>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
            <span>Daftar Kehadiran</span>
        </a>
    </nav>
</div>
