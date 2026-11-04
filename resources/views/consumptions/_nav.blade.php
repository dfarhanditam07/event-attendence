@php($current = request()->route()->getName())
@php($onRecap = in_array($current, ['events.consumptions.index', 'events.consumptions.create'], true))

<div class="mb-6 flex flex-wrap items-center gap-1.5 rounded-xl border border-slate-200/80 bg-white p-1.5 shadow-xs">
    <a href="{{ route('events.consumptions.index', $event) }}"
       class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold transition {{ $onRecap ? 'bg-enc-800 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="14" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/>
        </svg>
        Rekap Divisi
    </a>
    <a href="{{ route('events.consumptions.report', $event) }}"
       class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold transition {{ $current === 'events.consumptions.report' ? 'bg-enc-800 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="9" y1="15" x2="15" y2="15"/>
        </svg>
        Laporan Audit Trail
    </a>
</div>
