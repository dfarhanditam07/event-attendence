@extends('layouts.app')

@section('title', $event->name)

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

    <!-- Event Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $event->name }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2.5">
                @include('events._status', ['status' => $event->effectiveStatus()])
                @if($event->status === \App\Enums\EventStatus::Open && $event->ends_at)
                    <span class="inline-flex items-center gap-1 text-xs text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        {{ $event->ends_at->isPast() ? 'Ditutup otomatis sejak' : 'Otomatis ditutup' }}
                        {{ $event->ends_at->translatedFormat('d M Y, H:i') }} WIB
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $event)
                <a href="{{ route('events.edit', $event) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs transition hover:border-slate-300 hover:bg-slate-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Edit Event
                </a>
            @endcan
            @can('delete', $event)
                <form method="POST" action="{{ route('events.destroy', $event) }}"
                      onsubmit="return confirm('Hapus event ini? Data peserta dan absensinya tetap tersimpan, tetapi event tidak akan tampil lagi.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-white px-3.5 py-2 text-xs font-semibold text-rose-600 shadow-xs transition hover:bg-rose-50 hover:text-rose-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        Hapus
                    </button>
                </form>
            @endcan
        </div>
    </div>

    @include('events._tabs', ['event' => $event])

    @php
        $publicUrl = route('public.events.check-in', ['event' => $event, 'slug' => $event->slug]);
        $absentCount = max(0, $event->participants_count - $event->attendances_count);
        $rate = $event->participants_count > 0 ? round(($event->attendances_count / $event->participants_count) * 100) : 0;
    @endphp

    <!-- Stat Cards Metric Grid -->
    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
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
            <div class="mt-1 text-xs text-amber-600">Menunggu kehadiran</div>
        </div>

        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Persentase</span>
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

    <!-- Detail & Link Check-in Grid -->
    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Informasi Event -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-enc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <h2 class="font-bold text-slate-900">Informasi Pelaksanaan Acara</h2>
            </div>

            <dl class="mt-5 space-y-4 text-sm">
                <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1 border-b border-slate-50 pb-3">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Waktu Mulai</dt>
                    <dd class="font-medium text-slate-900">{{ $event->starts_at->translatedFormat('l, d F Y • H:i') }} WIB</dd>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1 border-b border-slate-50 pb-3">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Waktu Selesai</dt>
                    <dd class="font-medium text-slate-900">
                        {{ $event->ends_at ? $event->ends_at->translatedFormat('l, d F Y • H:i').' WIB' : 'Tidak ditentukan (manual)' }}
                    </dd>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-1 border-b border-slate-50 pb-3">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Lokasi</dt>
                    <dd class="font-medium text-slate-900">{{ $event->location ?: '—' }}</dd>
                </div>
                <div class="pt-1">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500">Deskripsi Acara</dt>
                    <dd class="mt-1.5 whitespace-pre-line text-slate-700 leading-relaxed">{{ $event->description ?: 'Tidak ada deskripsi.' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Portal Check-in Publik Link -->
        <div class="flex flex-col rounded-xl border border-enc-200/90 bg-gradient-to-br from-enc-50/70 via-white to-enc-50/40 p-6 shadow-xs">
            <div class="flex items-center justify-between border-b border-enc-100 pb-4">
                <div class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-enc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    </svg>
                    <h2 class="font-bold text-enc-950">Portal Check-in Mandiri Peserta</h2>
                </div>
                <span class="rounded-full bg-enc-100 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-enc-800">
                    Publik
                </span>
            </div>

            <p class="mt-4 text-xs leading-relaxed text-slate-600">
                Bagikan link berikut kepada peserta pada meja registrasi atau layar proyektor. Peserta dapat langsung melakukan check-in mandiri tanpa perlu login akun panitia.
            </p>

            <div class="mt-4 rounded-xl border border-slate-200 bg-white p-3 shadow-2xs">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Tautan Akses:</div>
                <div class="mt-1 break-all font-mono text-xs text-enc-900 select-all">
                    {{ $publicUrl }}
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-2.5">
                <button type="button"
                        onclick="navigator.clipboard.writeText('{{ $publicUrl }}').then(function(){ var el=document.getElementById('copy-ok'); if(el){ el.classList.remove('hidden'); setTimeout(function(){el.classList.add('hidden');},2500);} })"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-enc-800 px-4 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    Salin Link
                </button>

                <a href="{{ $publicUrl }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                    Buka Halaman Publik
                </a>

                <span id="copy-ok" class="hidden inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Tersalin!
                </span>
            </div>

            <div class="mt-auto pt-6 text-[11px] text-slate-500 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-enc-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                Tautan publik aman digunakan di tablet, smartphone, maupun laptop registrasi.
            </div>
        </div>
    </div>
@endsection
