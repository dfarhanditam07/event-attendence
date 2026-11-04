@extends('layouts.public')

@section('title', 'Kiosk Konsumsi')
@section('subtitle', 'Kiosk Konsumsi Publik')
@section('footer_hint', 'Butuh bantuan? Silakan hubungi Panitia Acara di meja pembagian konsumsi.')

@section('content')
    @include('public._consumption_result', ['lastResult' => $lastResult])

    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/40">
        <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900 sm:text-xl">Pilih Divisi Pengambil</h2>
                <p class="mt-0.5 text-xs text-slate-500">Porsi dihitung untuk anggota divisi yang sudah check-in dan belum diambil.</p>
            </div>

            <form method="POST" action="{{ route('public.events.consumption.lock', ['event' => $event, 'slug' => $event->slug]) }}">
                @csrf
                <button type="submit"
                        class="inline-flex flex-none items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-xs transition hover:bg-slate-50 hover:text-slate-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Kunci Kiosk
                </button>
            </form>
        </div>

        @if($divisions->isEmpty())
            <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50/70 p-8 text-center text-xs text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-2 h-8 w-8 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                </svg>
                Belum ada anggota yang check-in. Konsumsi bisa diserahkan setelah peserta melakukan check-in.
            </div>
        @else
            <ul class="mt-4 space-y-2.5">
                @foreach($divisions as $division)
                    <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-white p-3.5 transition hover:border-enc-200 hover:bg-enc-50/30">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-bold text-slate-900">{{ $division['division'] }}</div>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                <span>Hadir: <span class="font-semibold text-slate-700">{{ $division['present'] }}</span></span>
                                <span class="text-slate-300">•</span>
                                <span>Sudah diambil: <span class="font-semibold text-slate-700">{{ $division['claimed'] }}</span></span>
                                <span class="text-slate-300">•</span>
                                @if($division['remaining'] > 0)
                                    <span class="inline-flex items-center rounded-full border border-amber-200/90 bg-amber-50 px-2 py-0.5 font-semibold text-amber-800">
                                        Sisa {{ $division['remaining'] }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200/90 bg-emerald-50 px-2 py-0.5 font-semibold text-emerald-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"/>
                                        </svg>
                                        Selesai
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($division['remaining'] > 0)
                            <a href="{{ route('public.events.consumption.create', ['event' => $event, 'slug' => $event->slug, 'division' => $division['division']]) }}"
                               class="inline-flex flex-none items-center gap-1.5 rounded-lg bg-enc-800 px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-enc-900">
                                Serahkan
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                    <polyline points="12 5 19 12 12 19"/>
                                </svg>
                            </a>
                        @else
                            <span class="flex-none text-[11px] font-medium text-slate-400">Semua porsi sudah diambil</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
