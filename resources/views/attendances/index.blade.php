@extends('layouts.app')

@section('title', 'Daftar Kehadiran — '.$event->name)

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

    <!-- Header Section -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $event->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Laporan pemantauan dan rekap kehadiran peserta secara langsung.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('events.attendances.export', $event) }}"
               class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-700 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export CSV Kehadiran
            </a>
        </div>
    </div>

    @include('events._tabs', ['event' => $event])

    @php
        $rate = $event->participants_count > 0 ? round(($event->attendances_count / $event->participants_count) * 100) : 0;
    @endphp

    <!-- Metrics Grid -->
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Peserta</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $event->participants_count }}</div>
            <div class="mt-1 text-xs text-slate-500">Peserta terdaftar</div>
        </div>

        <div class="rounded-xl border border-emerald-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Sudah Hadir</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-emerald-700">{{ $event->attendances_count }}</div>
            <div class="mt-1 text-xs text-emerald-600">Telah check-in</div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-700">Belum Hadir</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-amber-700">{{ $absentCount }}</div>
            <div class="mt-1 text-xs text-amber-600">Belum presensi</div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tingkat Kehadiran</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-enc-50 text-enc-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.21 15.89A10 10 0 1 1 8 2.83"/>
                        <path d="M22 12A10 10 0 0 0 12 2v10z"/>
                    </svg>
                </span>
            </div>
            <div class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $rate }}%</div>
            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $rate }}%"></div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
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
                    placeholder="Cari nama, NIK, atau organisasi..."
                    class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">
            </div>

            <div class="w-full sm:w-auto">
                <select name="method"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">
                    <option value="">Semua metode check-in</option>
                    @foreach($methods as $m)
                        <option value="{{ $m->value }}" {{ optional($filters['method'])->value === $m->value ? 'selected' : '' }}>
                            {{ $m->label() }}
                        </option>
                    @endforeach
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

                @if($filters['q'] || $filters['method'])
                    <a href="{{ route('events.attendances.index', $event) }}"
                       class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    @if($attendances->isEmpty())
        <div class="rounded-xl border border-slate-200/80 bg-white p-12 text-center shadow-xs">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <h3 class="mt-3 text-sm font-semibold text-slate-900">Belum Ada Data Kehadiran</h3>
            <p class="mt-1 text-xs text-slate-500">
                @if($filters['q'] || $filters['method'])
                    Tidak ditemukan data kehadiran yang sesuai filter.
                @else
                    Peserta melakukan check-in mandiri melalui link publik acara.
                @endif
            </p>
            <div class="mt-5">
                <a href="{{ route('events.show', $event) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                    Buka Link Publik di Detail Event
                </a>
            </div>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Waktu Check-in</th>
                            <th class="px-5 py-3.5">Peserta</th>
                            <th class="px-5 py-3.5">NIK & Instansi</th>
                            <th class="px-5 py-3.5">Metode Check-in</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($attendances as $a)
                            @php
                                $name = $a->participant?->name ?? '(Peserta telah dihapus)';
                                $initials = collect(explode(' ', $name))
                                    ->filter()
                                    ->take(2)
                                    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                                    ->implode('');

                                $badge = match($a->method) {
                                    \App\Enums\AttendanceMethod::Nik => [
                                        'class' => 'bg-sky-50 text-sky-800 border-sky-200/90',
                                        'label' => 'Input NIK',
                                    ],
                                    \App\Enums\AttendanceMethod::Name => [
                                        'class' => 'bg-amber-50 text-amber-800 border-amber-200/90',
                                        'label' => 'Cari Nama',
                                    ],
                                    default => [
                                        'class' => 'bg-slate-100 text-slate-700 border-slate-200',
                                        'label' => $a->method->label(),
                                    ],
                                };
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50/80">
                                <td class="whitespace-nowrap px-5 py-3.5 text-slate-600">
                                    <div class="font-semibold text-slate-900">{{ $a->checked_in_at->translatedFormat('d M Y') }}</div>
                                    <div class="font-mono text-xs text-slate-500">{{ $a->checked_in_at->translatedFormat('H:i:s') }} WIB</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">
                                            {{ $initials ?: '?' }}
                                        </span>
                                        <div>
                                            <div class="font-semibold text-slate-900">{{ $name }}</div>
                                            @if($a->participant?->email)
                                                <div class="text-[11px] text-slate-400">{{ $a->participant->email }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600">
                                    @if($a->participant?->nik)
                                        <div class="font-mono text-xs font-medium text-slate-800">{{ $a->participant->nik }}</div>
                                    @endif
                                    @if($a->participant?->organization)
                                        <div class="text-xs text-slate-500">{{ $a->participant->organization }}</div>
                                    @endif
                                    @if(!$a->participant?->nik && !$a->participant?->organization)
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $badge['class'] }}">
                                        @if($a->method === \App\Enums\AttendanceMethod::Nik)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                                <line x1="7" y1="8" x2="17" y2="8"/>
                                                <line x1="7" y1="12" x2="11" y2="12"/>
                                            </svg>
                                        @elseif($a->method === \App\Enums\AttendanceMethod::Name)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="11" cy="11" r="8"/>
                                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                            </svg>
                                        @endif
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($attendances->hasPages())
                <div class="border-t border-slate-200 px-5 py-3.5">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection
