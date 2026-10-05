@extends('layouts.app')

@section('title', 'Peserta — '.$event->name)

@section('content')
    <div class="mb-3">
        <a href="{{ route('events.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Semua event</a>
    </div>
    <div class="mb-2 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">{{ $event->name }}</h1>
            <div class="mt-1 text-sm text-slate-500">Daftar peserta</div>
        </div>
        <div class="flex items-center gap-2">
            @can('update', $event)
                <a href="{{ route('events.participants.create', $event) }}"
                   class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-100">
                    + Tambah Peserta
                </a>
                <a href="{{ route('events.participants.import', $event) }}"
                   class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Import CSV
                </a>
            @endcan
        </div>
    </div>

    @include('events._tabs', ['event' => $event])

    <form method="GET" class="mb-4 flex flex-wrap gap-2 text-sm">
        <input
            type="search"
            name="q"
            value="{{ $filters['q'] }}"
            placeholder="Cari nama, NIK, email, organisasi..."
            class="flex-1 min-w-[200px] rounded-md border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        <select name="status" class="rounded-md border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            <option value="">Semua status</option>
            <option value="present" {{ $filters['status'] === 'present' ? 'selected' : '' }}>Sudah Hadir</option>
            <option value="absent" {{ $filters['status'] === 'absent' ? 'selected' : '' }}>Belum Hadir</option>
        </select>
        <button type="submit" class="rounded-md border border-slate-300 bg-white px-4 py-2 hover:bg-slate-100">
            Cari
        </button>
        <a href="{{ route('events.participants.index', $event) }}"
           class="rounded-md border border-slate-300 bg-white px-4 py-2 hover:bg-slate-100">
            Reset
        </a>
    </form>

    @if($participants->isEmpty())
        <div class="rounded-lg border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
            Belum ada peserta.
            @can('update', $event)
                <a href="{{ route('events.participants.import', $event) }}" class="text-indigo-600 hover:underline">Import peserta sekarang</a>.
            @endcan
        </div>
    @else
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium text-slate-600">Nama</th>
                            <th class="px-4 py-3 font-medium text-slate-600">NIK</th>
                            <th class="px-4 py-3 font-medium text-slate-600">Kontak</th>
                            <th class="px-4 py-3 font-medium text-slate-600">Organisasi</th>
                            <th class="px-4 py-3 font-medium text-slate-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($participants as $p)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900">{{ $p->name }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $p->nik ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    @if($p->email)
                                        <div>{{ $p->email }}</div>
                                    @endif
                                    @if($p->phone)
                                        <div class="text-xs text-slate-500">{{ $p->phone }}</div>
                                    @endif
                                    @if(!$p->email && !$p->phone)
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ $p->organization ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    @if($p->attendance)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                            ✓ Hadir
                                            <span class="text-emerald-600">{{ $p->attendance->checked_in_at->translatedFormat('H:i') }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
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
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $participants->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection
