@extends('layouts.public')

@section('content')
    @if(! $canCheckIn)
        @include('public._unavailable')
    @else
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-300/30 sm:p-8">
            <div class="text-center">
                <h2 class="text-xl font-bold text-enc-950 sm:text-2xl">Pilih Cara Check-in Anda</h2>
                <p class="mt-2 text-sm text-slate-700 sm:text-base">Silakan pilih salah satu metode check-in di bawah ini untuk memulai.</p>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 sm:gap-5">
                <a href="{{ route('public.events.check-in.mode', ['event' => $event, 'slug' => $event->slug, 'mode' => 'name']) }}"
                   class="flex flex-col items-center rounded-2xl border border-slate-300 bg-white px-5 py-8 text-center transition hover:border-enc-600 hover:bg-slate-50 hover:shadow-lg hover:shadow-slate-300/50 focus-visible:border-enc-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-enc-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-enc-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="4" y="2.5" width="15" height="19" rx="2.5"/>
                        <path d="M19 7h2M19 12h2M19 17h2"/>
                        <circle cx="11.5" cy="9.5" r="2.5"/>
                        <path d="M7.5 17c.8-2 2.4-3 4-3s3.2 1 4 3"/>
                    </svg>
                    <span class="mt-5 text-xl font-bold text-enc-950">Cari Nama Anda</span>
                    <span class="mt-2 text-sm text-slate-700">Temukan nama Anda langsung dari daftar peserta acara.</span>
                </a>

                <a href="{{ route('public.events.check-in.mode', ['event' => $event, 'slug' => $event->slug, 'mode' => 'nik']) }}"
                   class="flex flex-col items-center rounded-2xl border border-slate-300 bg-white px-5 py-8 text-center transition hover:border-enc-600 hover:bg-slate-50 hover:shadow-lg hover:shadow-slate-300/50 focus-visible:border-enc-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-enc-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-enc-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="15.5" rx="2.5"/>
                        <rect x="9.5" y="2.5" width="5" height="3.5" rx="1"/>
                        <circle cx="8" cy="11.5" r="1.8"/>
                        <path d="M5 17.5c.5-1.6 1.7-2.4 3-2.4s2.5.8 3 2.4"/>
                        <path d="M13.5 10.5H18M13.5 13.5H18M13.5 16.5H18"/>
                    </svg>
                    <span class="mt-5 text-xl font-bold text-enc-950">Input NIK</span>
                    <span class="mt-2 text-sm text-slate-700">Masukkan Nomor Induk Karyawan Anda yang terdaftar.</span>
                </a>
            </div>
        </div>
    @endif
@endsection
