@extends('layouts.public')

@section('content')
    @if(! $canCheckIn)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center text-sm text-amber-900">
            Check-in belum dibuka untuk acara ini. Hubungi panitia jika Anda yakin ini sebuah kesalahan.
        </div>
    @else
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-center text-lg font-semibold text-slate-900">Pilih cara check-in Anda</h2>
            <p class="mt-1 text-center text-sm text-slate-500">Pilih salah satu metode paling mudah untuk Anda</p>
        </div>
        <div class="grid divide-slate-200 sm:grid-cols-2 sm:divide-x">
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
            <a href="{{ route('public.events.check-in.mode', ['event' => $event, 'slug' => $event->slug, 'mode' => 'nik']) }}"
               class="flex flex-col items-center gap-2 p-6 text-center transition hover:bg-sky-50/60">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-5m-4 0V5a2 2 0 1 1 4 0v1m-4 0a2 2 0 1 0 4 0m-6 5h8"/>
                    </svg>
                </span>
                <span class="font-semibold text-slate-900">Input NIK</span>
                <span class="text-xs text-slate-500">Masukkan NIK yang didaftarkan panitia</span>
            </a>
        </div>
    </div>
    @endif
@endsection
