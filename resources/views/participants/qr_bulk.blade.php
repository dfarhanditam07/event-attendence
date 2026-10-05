@extends('layouts.app')

@section('title', 'Semua QR Code — '.$event->name)

@section('content')
    <div class="mb-3">
        <a href="{{ route('events.participants.index', $event) }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Daftar peserta</a>
    </div>
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Kartu QR Semua Peserta</h1>
            <div class="mt-1 text-sm text-slate-500">{{ $event->name }} · {{ $qrs->count() }} peserta</div>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="window.print()"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Cetak Semua (Print)
            </button>
        </div>
    </div>

    @include('events._tabs', ['event' => $event])

    @if($qrs->isEmpty())
        <div class="rounded-lg border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
            Belum ada peserta.
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($qrs as $q)
                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm break-inside-avoid-page">
                    <div class="bg-gradient-to-br from-indigo-600 to-violet-600 px-4 py-3 text-white text-xs">
                        <div class="truncate font-semibold">{{ $event->name }}</div>
                        <div class="opacity-90">{{ $event->starts_at->translatedFormat('d M Y') }}</div>
                    </div>
                    <div class="flex flex-col items-center gap-2 px-4 py-5">
                        <div class="rounded-xl bg-white p-1.5 ring-1 ring-slate-200">
                            {!! $q['qr'] !!}
                        </div>
                        <div class="w-full text-center">
                            <div class="truncate text-sm font-bold text-slate-900" title="{{ $q['name'] }}">{{ $q['name'] }}</div>
                            @if($q['organization'])
                                <div class="truncate text-xs text-slate-600" title="{{ $q['organization'] }}">{{ $q['organization'] }}</div>
                            @endif
                            @if($q['nik'])
                                <div class="mt-1 font-mono text-[10px] text-slate-400">{{ $q['nik'] }}</div>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection

@push('styles')
<style>
@media print {
    header, .mb-3, .mb-4, nav, footer, button { display: none !important; }
    body { background: #fff !important; }
    main { padding: 0; max-width: none; margin: 0; }
    article { box-shadow: none; page-break-inside: avoid; break-inside: avoid; }
}
</style>
@endpush
