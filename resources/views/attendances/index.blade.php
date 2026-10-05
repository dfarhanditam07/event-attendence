@extends('layouts.app')

@section('title', 'Daftar Kehadiran — '.$event->name)

@section('content')
    <div class="mb-3">
        <a href="{{ route('events.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Semua event</a>
    </div>
    <div class="mb-2 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">{{ $event->name }}</h1>
            <div class="mt-1 text-sm text-slate-500">Daftar kehadiran</div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('events.attendances.check-in', $event) }}"
               class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">
                Halaman Check-in
            </a>
            <a href="{{ route('events.attendances.export', $event) }}"
               class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Export CSV
            </a>
        </div>
    </div>

    @include('events._tabs', ['event' => $event])

    <div class="mb-6 grid gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="text-sm text-slate-500">Total peserta</div>
            <div class="mt-1 text-2xl font-semibold">{{ $event->participants_count }}</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="text-sm text-slate-500">Sudah hadir</div>
            <div class="mt-1 text-2xl font-semibold text-emerald-700">{{ $event->attendances_count }}</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="text-sm text-slate-500">Belum hadir</div>
            <div class="mt-1 text-2xl font-semibold text-amber-700">{{ $absentCount }}</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="text-sm text-slate-500">Persentase</div>
            <div class="mt-1 text-2xl font-semibold">
                {{ $event->participants_count > 0 ? round($event->attendances_count / $event->participants_count * 100) : 0 }}%
            </div>
        </div>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2 text-sm">
        <input
            type="search"
            name="q"
            value="{{ $filters['q'] }}"
            placeholder="Cari nama / NIK / organisasi..."
            class="flex-1 min-w-[200px] rounded-md border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        <select name="method" class="rounded-md border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            <option value="">Semua metode</option>
            @foreach($methods as $m)
                <option value="{{ $m->value }}" {{ optional($filters['method'])->value === $m->value ? 'selected' : '' }}>
                    {{ $m->label() }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md border border-slate-300 bg-white px-4 py-2 hover:bg-slate-100">
            Cari
        </button>
        <a href="{{ route('events.attendances.index', $event) }}"
           class="rounded-md border border-slate-300 bg-white px-4 py-2 hover:bg-slate-100">
            Reset
        </a>
    </form>

    @if($attendances->isEmpty())
        <div class="rounded-lg border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
            Belum ada data kehadiran untuk event ini.
            <a href="{{ route('events.attendances.check-in', $event) }}" class="text-indigo-600 hover:underline">Buka halaman check-in</a>.
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium text-slate-600">Waktu</th>
                            <th class="px-4 py-3 font-medium text-slate-600">Nama</th>
                            <th class="px-4 py-3 font-medium text-slate-600">NIK / Organisasi</th>
                            <th class="px-4 py-3 font-medium text-slate-600">Metode</th>
                            <th class="px-4 py-3 font-medium text-slate-600">Oleh</th>
                            <th class="px-4 py-3 font-medium text-slate-600">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($attendances as $a)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    <div class="font-medium text-slate-900">{{ $a->checked_in_at->translatedFormat('d M Y') }}</div>
                                    <div class="text-xs">{{ $a->checked_in_at->translatedFormat('H:i:s') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900">
                                        {{ $a->participant?->name ?? '(data dihapus)' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    @if($a->participant?->nik)
                                        <div class="font-mono text-xs">{{ $a->participant->nik }}</div>
                                    @endif
                                    @if($a->participant?->organization)
                                        <div class="text-xs">{{ $a->participant->organization }}</div>
                                    @endif
                                    @if(!$a->participant?->nik && !$a->participant?->organization)
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @php($badgeClass = match($a->method) {
                                        \App\Enums\AttendanceMethod::Qr => 'bg-indigo-50 text-indigo-700',
                                        \App\Enums\AttendanceMethod::Nik => 'bg-sky-50 text-sky-700',
                                        \App\Enums\AttendanceMethod::Manual => 'bg-amber-50 text-amber-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    })
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $badgeClass }}">
                                        {{ $a->method->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-xs">
                                    {{ $a->checkedInBy?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-xs">
                                    {{ $a->notes ?: '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($attendances->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection
