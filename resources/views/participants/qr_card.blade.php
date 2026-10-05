@extends('layouts.app')

@section('title', 'QR Code — '.$participant->name)

@section('content')
    <div class="mb-3">
        <a href="{{ route('events.participants.index', $event) }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Daftar peserta</a>
    </div>
    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Kartu QR — {{ $participant->name }}</h1>
            <div class="mt-1 text-sm text-slate-500">{{ $event->name }}</div>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('events.participants.qr.index', $event) }}"
               class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">
                Semua QR
            </a>
            <a href="{{ route('events.participants.qr.download', ['event' => $event, 'participant' => $participant]) }}"
               class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Download PNG
            </a>
            <button type="button" onclick="window.print()"
                    class="rounded-md border border-indigo-200 bg-white px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50">
                Cetak Kartu
            </button>
        </div>
    </div>

    @include('events._tabs', ['event' => $event])

    <div class="flex flex-col items-center">
        <div id="qr-card"
             class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl print:shadow-none print:rounded-none print:border-slate-400">
            <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 px-6 py-5 text-white">
                <div class="text-xs font-semibold uppercase tracking-wider opacity-90">Kartu Peserta</div>
                <div class="mt-1 text-xl font-bold leading-tight">{{ $event->name }}</div>
                <div class="mt-2 text-xs opacity-90">
                    {{ $event->starts_at->translatedFormat('l, d F Y, H:i') }}
                    @if($event->location) · {{ $event->location }} @endif
                </div>
            </div>
            <div class="flex flex-col items-center gap-4 px-6 py-8">
                <div class="rounded-2xl bg-white p-2 ring-1 ring-slate-200">
                    {!! $qrSvg !!}
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-slate-900">{{ $participant->name }}</div>
                    @if($participant->organization)
                        <div class="mt-0.5 text-sm text-slate-600">{{ $participant->organization }}</div>
                    @endif
                    @if($participant->nik)
                        <div class="mt-1 font-mono text-xs text-slate-500">NIK {{ chunk_split($participant->nik, 4, ' ') }}</div>
                    @endif
                </div>
                <div class="w-full rounded-lg bg-slate-50 px-4 py-3 text-center text-[11px] leading-relaxed text-slate-600">
                    Tunjukkan QR code ini ke panitia saat check-in. Atau scan sendiri via link publik:
                    <div class="mt-1 truncate font-mono text-indigo-600">
                        {{ route('public.events.check-in', ['event' => $event, 'slug' => $event->slug]) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
@media print {
    body > header, body > main > *:not(#qr-card), main > .mb-3, main > .mb-4, main > nav, footer, .no-print { display: none !important; }
    body { background: #fff !important; }
    main { padding: 0; max-width: none; margin: 0; }
}
</style>
@endpush
