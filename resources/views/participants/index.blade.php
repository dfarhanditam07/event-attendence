@extends('layouts.app')

@section('title', 'Peserta — '.$event->name)

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

    <!-- Page Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $event->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Daftar peserta terdaftar dan verifikasi kehadiran acara.</p>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $event)
                <a href="{{ route('events.participants.create', $event) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs transition hover:border-slate-300 hover:bg-slate-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Peserta
                </a>
                <a href="{{ route('events.participants.import', $event) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-enc-800 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Import CSV
                </a>
            @endcan
        </div>
    </div>

    @include('events._tabs', ['event' => $event])

    <!-- Search & Filter Bar -->
    <div class="mb-6 rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[240px] flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] }}"
                    placeholder="Cari nama, NIK, email, organisasi/instansi..."
                    class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">
            </div>

            <div class="w-full sm:w-auto">
                <select name="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">
                    <option value="">Semua status kehadiran</option>
                    <option value="present" {{ $filters['status'] === 'present' ? 'selected' : '' }}>Sudah Hadir</option>
                    <option value="absent" {{ $filters['status'] === 'absent' ? 'selected' : '' }}>Belum Hadir</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-xs transition hover:bg-slate-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Cari
                </button>

                @if($filters['q'] || $filters['status'])
                    <a href="{{ route('events.participants.index', $event) }}"
                       class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table -->
    @if($participants->isEmpty())
        <div class="rounded-xl border border-slate-200/80 bg-white p-12 text-center shadow-xs">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <h3 class="mt-3 text-sm font-semibold text-slate-900">Tidak Ada Data Peserta</h3>
            <p class="mt-1 text-xs text-slate-500">
                @if($filters['q'] || $filters['status'])
                    Tidak ditemukan peserta yang sesuai dengan kriteria filter saat ini.
                @else
                    Belum ada peserta yang didaftarkan ke event ini.
                @endif
            </p>
            @can('update', $event)
                <div class="mt-5 flex items-center justify-center gap-3">
                    <a href="{{ route('events.participants.create', $event) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                        + Tambah Manual
                    </a>
                    <a href="{{ route('events.participants.import', $event) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-enc-800 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-enc-900">
                        Import CSV
                    </a>
                </div>
            @endcan
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Peserta</th>
                            <th class="px-5 py-3.5">NIK</th>
                            <th class="px-5 py-3.5">Kontak</th>
                            <th class="px-5 py-3.5">Instansi / Organisasi</th>
                            <th class="px-5 py-3.5">Status Presensi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($participants as $p)
                            @php
                                $initials = collect(explode(' ', $p->name))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                                    ->implode('');
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50/80">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">
                                            {{ $initials ?: '?' }}
                                        </span>
                                        <div>
                                            <div class="font-semibold text-slate-900">{{ $p->name }}</div>
                                            <div class="text-[11px] text-slate-400">ID #{{ $p->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    @if($p->nik)
                                        <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs font-medium text-slate-700">
                                            {{ $p->nik }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="space-y-0.5 text-xs">
                                        @if($p->email)
                                            <div class="flex items-center gap-1.5 text-slate-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                                    <polyline points="22,6 12,13 2,6"/>
                                                </svg>
                                                <span>{{ $p->email }}</span>
                                            </div>
                                        @endif
                                        @if($p->phone)
                                            <div class="flex items-center gap-1.5 text-slate-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                                </svg>
                                                <span>{{ $p->phone }}</span>
                                            </div>
                                        @endif
                                        @if(!$p->email && !$p->phone)
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600">
                                    {{ $p->organization ?: '—' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    @if($p->attendance)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200/90 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                            Hadir
                                            <span class="text-[11px] font-normal text-emerald-700">({{ $p->attendance->checked_in_at->translatedFormat('H:i') }})</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Belum hadir
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($participants->hasPages())
                <div class="border-t border-slate-200 px-5 py-3.5">
                    {{ $participants->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection
