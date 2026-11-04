@extends('layouts.app')

@section('title', 'Konsumsi Kolektif — '.$event->name)

@section('content')
    <div class="mb-4">
        <a href="{{ route('events.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-slate-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Kembali ke Daftar Event
        </a>
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $event->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                Pengambilan konsumsi kolektif per divisi. Porsi hanya dihitung untuk anggota yang sudah hadir (check-in).
            </p>
        </div>

        @if($event->hasConsumptionKiosk())
            <a href="{{ route('public.events.consumption.index', ['event' => $event, 'slug' => $event->slug]) }}"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex flex-none items-center gap-1.5 rounded-xl border border-enc-200 bg-white px-3.5 py-2 text-xs font-semibold text-enc-800 shadow-xs transition hover:border-enc-300 hover:bg-enc-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                    <polyline points="15 3 21 3 21 9"/>
                    <line x1="10" y1="14" x2="21" y2="3"/>
                </svg>
                Buka Kiosk Publik
            </a>
        @endif
    </div>

    @include('events._tabs', ['event' => $event])
    @include('consumptions._nav', ['event' => $event])

    <!-- Metrics Grid -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-emerald-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Anggota Hadir</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-emerald-700">{{ $totals['present'] }}</div>
            <div class="mt-1 text-xs text-emerald-600">Berhak atas konsumsi</div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sudah Diambil</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $totals['claimed'] }}</div>
            <div class="mt-1 text-xs text-slate-500">Porsi terserahkan</div>
        </div>

        <div class="rounded-xl border border-amber-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-700">Sisa Porsi</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-amber-700">{{ $totals['remaining'] }}</div>
            <div class="mt-1 text-xs text-amber-600">Belum diambil</div>
        </div>
    </div>

    <!-- Division Table -->
    @if($divisions->isEmpty())
        <div class="rounded-xl border border-slate-200/80 bg-white p-12 text-center shadow-xs">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                </svg>
            </div>
            <h3 class="mt-3 text-sm font-semibold text-slate-900">Belum Ada Anggota Hadir</h3>
            <p class="mt-1 text-xs text-slate-500">
                Konsumsi bisa diserahkan setelah ada peserta yang melakukan check-in.
            </p>
            <div class="mt-5">
                <a href="{{ route('events.attendances.index', $event) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                    Lihat Daftar Kehadiran
                </a>
            </div>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Divisi</th>
                            <th class="px-5 py-3.5 text-center">Anggota Hadir</th>
                            <th class="px-5 py-3.5 text-center">Sudah Diambil</th>
                            <th class="px-5 py-3.5 text-center">Sisa</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($divisions as $division)
                            <tr class="transition-colors hover:bg-slate-50/80">
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-slate-900">{{ $division['division'] }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-center font-medium text-slate-700">{{ $division['present'] }}</td>
                                <td class="px-5 py-3.5 text-center font-medium text-slate-700">{{ $division['claimed'] }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    @if($division['remaining'] > 0)
                                        <span class="inline-flex items-center rounded-full border border-amber-200/90 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                            {{ $division['remaining'] }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200/90 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            Selesai
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    @if($division['remaining'] > 0)
                                        <a href="{{ route('events.consumptions.create', ['event' => $event, 'division' => $division['division']]) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg bg-enc-800 px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-enc-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 12 20 22 4 22 4 12"/>
                                                <rect x="2" y="7" width="20" height="5"/>
                                                <line x1="12" y1="22" x2="12" y2="7"/>
                                                <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                                                <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                                            </svg>
                                            Serahkan Konsumsi
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">Semua porsi sudah diambil</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
