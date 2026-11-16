@php($sessions = \App\Enums\MealSession::cases())

{{-- Pemilih sesi makan: rekap dihitung terpisah untuk tiap sesi. --}}
<div class="mb-6 inline-flex items-center gap-1 rounded-xl border border-slate-200/80 bg-white p-1 shadow-xs">
    @foreach($sessions as $session)
        <a href="{{ route('events.consumptions.index', ['event' => $event, 'meal_session' => $session->value]) }}"
           class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold transition {{ $mealSession === $session ? 'bg-enc-800 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            @if($session === \App\Enums\MealSession::Lunch)
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                </svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            @endif
            {{ $session->label() }}
        </a>
    @endforeach
</div>
