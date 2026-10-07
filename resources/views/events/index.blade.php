@extends('layouts.app')

@section('title', 'Daftar Event')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Daftar Event</h1>
            <p class="mt-1 text-sm text-slate-500">Kelola jadwal acara perusahaan, peserta undangan, dan pemantauan absensi secara real-time.</p>
        </div>

        @can('create', App\Models\Event::class)
            <a href="{{ route('events.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-enc-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Buat Event Baru
            </a>
        @endcan
    </div>

    <!-- Filter & Pencarian -->
    <div class="mb-6 rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs">
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[240px] flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama event atau lokasi..."
                       class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">
            </div>

            <div class="w-full sm:w-auto">
                <select name="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-xs transition hover:bg-slate-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Filter
                </button>

                @if (request()->hasAny(['q', 'status']))
                    <a href="{{ route('events.index') }}"
                       class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Event</th>
                        <th class="px-5 py-3.5">Jadwal & Waktu</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Peserta</th>
                        <th class="px-5 py-3.5 text-right">Hadir</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($events as $event)
                        @php
                            $rate = $event->participants_count > 0 ? round(($event->attendances_count / $event->participants_count) * 100) : 0;
                        @endphp
                        <tr class="transition-colors hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <a href="{{ route('events.show', $event) }}" class="font-semibold text-slate-900 hover:text-enc-700">
                                    {{ $event->name }}
                                </a>
                                @if ($event->location)
                                    <div class="mt-1 flex items-center gap-1.5 text-xs text-slate-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-none text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                            <circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        <span>{{ $event->location }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                <div class="font-medium text-slate-900">{{ $event->starts_at->translatedFormat('d M Y') }}</div>
                                <div class="text-xs text-slate-500">{{ $event->starts_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-5 py-4">
                                @include('events._status', ['status' => $event->effectiveStatus()])
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <span class="font-semibold text-slate-900">{{ $event->participants_count }}</span>
                                <span class="text-xs text-slate-500">orang</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <div class="font-semibold text-emerald-700">{{ $event->attendances_count }}</div>
                                <div class="text-xs text-slate-500">{{ $rate }}% hadir</div>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-xs">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('events.show', $event) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 font-medium text-slate-700 shadow-xs hover:border-slate-300 hover:bg-slate-50">
                                        Detail
                                    </a>
                                    @can('update', $event)
                                        <a href="{{ route('events.edit', $event) }}"
                                           class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 font-medium text-slate-700 shadow-xs hover:border-slate-300 hover:bg-slate-50">
                                            Edit
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                </div>
                                <h3 class="mt-3 text-sm font-semibold text-slate-900">Belum Ada Event</h3>
                                <p class="mt-1 text-xs text-slate-500">Mulai dengan membuat event baru untuk mendata peserta dan memantau kehadiran.</p>
                                @can('create', App\Models\Event::class)
                                    <div class="mt-4">
                                        <a href="{{ route('events.create') }}"
                                           class="inline-flex items-center gap-2 rounded-lg bg-enc-800 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-enc-900">
                                            + Buat Event Pertama
                                        </a>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($events->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">
                {{ $events->links() }}
            </div>
        @endif
    </div>
@endsection
