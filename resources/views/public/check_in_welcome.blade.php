@extends('layouts.public')

@section('content')
    @if($lastResult)
        @if($lastResult['success'])
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                    <div>
                        <div class="font-semibold text-emerald-900">Check-in berhasil, {{ $lastResult['participant_name'] }}!</div>
                        @if(!empty($lastResult['participant_organization']))
                            <div class="text-emerald-700">{{ $lastResult['participant_organization'] }}</div>
                        @endif
                        @if(!empty($lastResult['attendance']))
                            <div class="mt-1 text-emerald-700">
                                {{ \Illuminate\Support\Carbon::parse($lastResult['attendance']['checked_in_at'])->translatedFormat('d M Y, H:i:s') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">✕</span>
                    <div>
                        @if(!empty($lastResult['participant_name']))
                            <div class="font-semibold text-red-900">{{ $lastResult['participant_name'] }}</div>
                        @endif
                        <ul class="mt-1 list-inside list-disc space-y-0.5 text-red-700">
                            @foreach(($lastResult['errors'] ?? []) as $field => $messages)
                                @foreach($messages as $m)
                                    <li>{{ $m }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-center text-lg font-semibold text-slate-900">Pilih cara check-in Anda</h2>
            <p class="mt-1 text-center text-sm text-slate-500">Pilih salah satu metode paling mudah untuk Anda</p>
        </div>
        <div class="grid divide-slate-200 sm:grid-cols-3 sm:divide-x">
            <a href="{{ route('public.events.check-in.mode', ['event' => $event, 'slug' => $event->slug, 'mode' => 'qr']) }}"
               class="flex flex-col items-center gap-2 p-6 text-center transition hover:bg-indigo-50/60">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h6v6H3zM15 3h6v6h-6zM3 15h6v6H3zM13 13h3v3h-3zM17 13h3v3h-3zM17 17h3v3h-3zM20 13h1v1h-1zM13 20h4v1h-4z"/>
                    </svg>
                </span>
                <span class="font-semibold text-slate-900">Scan QR</span>
                <span class="text-xs text-slate-500">Pindai QR dari panitia / undangan</span>
            </a>
            <a href="{{ route('public.events.check-in.mode', ['event' => $event, 'slug' => $event->slug, 'mode' => 'nik']) }}"
               class="flex flex-col items-center gap-2 p-6 text-center transition hover:bg-sky-50/60">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-5m-4 0V5a2 2 0 1 1 4 0v1m-4 0a2 2 0 1 0 4 0m-6 5h8"/>
                    </svg>
                </span>
                <span class="font-semibold text-slate-900">Input NIK</span>
                <span class="text-xs text-slate-500">Masukkan 16 digit NIK KTP Anda</span>
            </a>
            <a href="{{ route('public.events.check-in.mode', ['event' => $event, 'slug' => $event->slug, 'mode' => 'name']) }}"
               class="flex flex-col items-center gap-2 p-6 text-center transition hover:bg-amber-50/60">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/>
                    </svg>
                </span>
                <span class="font-semibold text-slate-900">Cari Nama</span>
                <span class="text-xs text-slate-500">Cari nama Anda dari daftar peserta</span>
            </a>
        </div>
    </div>
@endsection
