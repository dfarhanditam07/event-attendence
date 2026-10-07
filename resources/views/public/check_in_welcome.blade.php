@extends('layouts.public')

@section('content')
    @if(! $canCheckIn)
        @include('public._unavailable')
    @else
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/40">
            <div class="text-center">
                <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-enc-50 text-enc-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <polyline points="17 11 19 13 23 9"/>
                    </svg>
                </div>
                <h2 class="mt-3 text-xl font-bold text-slate-900 sm:text-2xl">Pilih Metode Check-in</h2>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm">Silakan pilih cara verifikasi kehadiran Anda yang paling mudah.</p>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 sm:gap-5">
                <!-- Opsi 1: Cari Nama -->
                <a href="{{ route('public.events.check-in.mode', ['event' => $event, 'slug' => $event->slug, 'mode' => 'name']) }}"
                   class="group relative flex flex-col items-center justify-between rounded-xl border border-slate-200 bg-white p-6 text-center transition-all hover:border-enc-600 hover:bg-slate-50/50 hover:shadow-md hover:ring-2 hover:ring-enc-600/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-enc-600">
                    <div class="flex flex-col items-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-enc-50 text-enc-800 transition group-hover:bg-enc-800 group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                <path d="M11 8v6"/>
                                <path d="M8 11h6"/>
                            </svg>
                        </span>
                        <span class="mt-4 text-base font-bold text-slate-900 group-hover:text-enc-950">
                            Cari Nama Anda
                        </span>
                        <span class="mt-1.5 text-xs text-slate-500 leading-relaxed">
                            Cari nama Anda secara langsung dari daftar undangan peserta acara.
                        </span>
                    </div>

                    <span class="mt-5 inline-flex items-center gap-1 text-xs font-semibold text-enc-700 transition group-hover:gap-1.5 group-hover:text-enc-900">
                        <span>Mulai Check-in</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </span>
                </a>

                <!-- Opsi 2: Input NIK -->
                <a href="{{ route('public.events.check-in.mode', ['event' => $event, 'slug' => $event->slug, 'mode' => 'nik']) }}"
                   class="group relative flex flex-col items-center justify-between rounded-xl border border-slate-200 bg-white p-6 text-center transition-all hover:border-enc-600 hover:bg-slate-50/50 hover:shadow-md hover:ring-2 hover:ring-enc-600/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-enc-600">
                    <div class="flex flex-col items-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-enc-50 text-enc-800 transition group-hover:bg-enc-800 group-hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <line x1="7" y1="8" x2="17" y2="8"/>
                                <line x1="7" y1="12" x2="11" y2="12"/>
                                <circle cx="15" cy="12" r="1"/>
                            </svg>
                        </span>
                        <span class="mt-4 text-base font-bold text-slate-900 group-hover:text-enc-950">
                            Input NIK
                        </span>
                        <span class="mt-1.5 text-xs text-slate-500 leading-relaxed">
                            Masukkan Nomor Induk Karyawan resmi yang terdaftar untuk verifikasi instan.
                        </span>
                    </div>

                    <span class="mt-5 inline-flex items-center gap-1 text-xs font-semibold text-enc-700 transition group-hover:gap-1.5 group-hover:text-enc-900">
                        <span>Mulai Check-in</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    @endif
@endsection
