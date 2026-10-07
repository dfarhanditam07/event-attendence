@extends('layouts.public')

@section('title', $mode === 'nik' ? 'Check-in dengan NIK' : 'Check-in dengan Nama')

@section('content')
    <div class="mb-5 text-center">
        <a href="{{ route('public.events.check-in', ['event' => $event, 'slug' => $event->slug]) }}"
           class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white/90 px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs backdrop-blur-xs transition hover:border-slate-300 hover:bg-slate-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Ganti Metode Check-in
        </a>
    </div>

    @include('public._result', ['lastResult' => $lastResult])

    @if(! $canCheckIn)
        @include('public._unavailable')
    @else
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/40">
            @if($mode === 'nik')
                <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-enc-50 text-enc-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="16" rx="2"/>
                            <line x1="7" y1="8" x2="17" y2="8"/>
                            <line x1="7" y1="12" x2="11" y2="12"/>
                            <circle cx="15" cy="12" r="1"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Check-in Menggunakan NIK</h2>
                        <p class="text-xs text-slate-500">Ketikkan Nomor Induk Karyawan Anda persis sesuai data undangan.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('public.events.check-in.submit-nik', ['event' => $event, 'slug' => $event->slug]) }}">
                    @csrf
                    <div class="space-y-5">
                        <div>
                            <label for="nik" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Nomor Induk Karyawan (NIK)
                            </label>
                            <input id="nik" name="nik" type="text" inputmode="numeric" required autofocus autocomplete="off"
                                   value="{{ old('nik') }}"
                                   placeholder="Contoh: 3201010101900001"
                                   class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 font-mono text-base tracking-wider text-slate-900 placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">
                            <p class="mt-1.5 text-[11px] text-slate-400">
                                Pastikan tidak ada spasi di awal atau akhir nomor NIK Anda.
                            </p>
                        </div>

                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-enc-800 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            Konfirmasi Kehadiran Sekarang
                        </button>
                    </div>
                </form>
            @else
                <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-enc-50 text-enc-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Cari Nama Peserta</h2>
                        <p class="text-xs text-slate-500">Ketikkan minimal 3 karakter nama Anda untuk mencari dalam daftar.</p>
                    </div>
                </div>

                <form method="GET" class="mb-6">
                    <label for="q" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">Nama Lengkap</label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"/>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                            </div>
                            <input id="q" name="q" type="search" value="{{ $search }}" autofocus autocomplete="off"
                                   placeholder="Contoh: Budi Santoso"
                                   class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">
                        </div>
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-enc-800 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-enc-900">
                            Cari
                        </button>
                    </div>
                </form>

                @if($searchTooShort)
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-5 text-center text-xs text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-6 w-6 text-slate-400 mb-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        Silakan ketikkan minimal 3 huruf dari nama Anda untuk mulai mencari.
                    </div>
                @elseif($search !== '' && $candidates->isEmpty())
                    <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-5 text-xs text-amber-900">
                        <div class="font-bold flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            Nama "{{ $search }}" tidak ditemukan atau sudah melakukan check-in.
                        </div>
                        <p class="mt-2 text-amber-800 leading-relaxed">
                            Pastikan ejaan nama Anda tepat. Jika Anda sudah berhasil check-in sebelumnya, Anda tidak perlu mengisi ulang. Jika data Anda belum terdaftar, silakan menuju ke meja panitia di lokasi acara.
                        </p>
                    </div>
                @elseif($search !== '')
                    <form method="POST" action="{{ route('public.events.check-in.submit-name', ['event' => $event, 'slug' => $event->slug]) }}">
                        @csrf
                        <div class="space-y-4">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Pilih Data Anda:
                            </div>

                            <div class="max-h-72 space-y-2 overflow-y-auto rounded-xl border border-slate-200/90 p-2">
                                @foreach($candidates as $c)
                                    @php
                                        $initials = collect(explode(' ', $c['name']))
                                            ->filter()
                                            ->take(2)
                                            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                                            ->implode('');
                                    @endphp
                                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200/60 bg-white p-3 transition hover:bg-slate-50 has-[:checked]:border-enc-600 has-[:checked]:bg-enc-50/70 has-[:checked]:ring-1 has-[:checked]:ring-enc-600">
                                        <input type="radio" name="participant_id" value="{{ $c['id'] }}"
                                               class="h-4 w-4 border-slate-300 text-enc-800 focus:ring-enc-600"
                                               @checked(old('participant_id') == $c['id']) required>
                                        <span class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">
                                            {{ $initials ?: '?' }}
                                        </span>
                                        <div class="min-w-0 flex-1 text-xs">
                                            <div class="font-bold text-slate-900">{{ $c['name'] }}</div>
                                            <div class="mt-0.5 truncate text-[11px] text-slate-500">
                                                @if($c['nik_masked'])
                                                    <span class="font-mono">NIK: {{ $c['nik_masked'] }}</span>
                                                @endif
                                                @if($c['nik_masked'] && $c['organization']) • @endif
                                                @if($c['organization'])
                                                    <span>{{ $c['organization'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <label class="flex items-start gap-2.5 rounded-xl border border-slate-200 bg-slate-50/80 p-3.5 text-xs text-slate-700 cursor-pointer select-none">
                                <input type="checkbox" name="confirmation" value="1" required
                                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-enc-800 focus:ring-enc-600">
                                <span class="leading-relaxed">
                                    Saya menyatakan bahwa data yang dipilih di atas adalah <strong>benar diri saya</strong> dan saya menghadiri acara ini secara langsung.
                                </span>
                            </label>

                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-enc-800 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Konfirmasi Kehadiran Saya
                            </button>
                        </div>
                    </form>
                @else
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-8 text-center text-xs text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-8 w-8 text-slate-400 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Ketikkan nama Anda pada kolom pencarian di atas untuk memverifikasi kehadiran.
                    </div>
                @endif
            @endif
        </div>
    @endif
@endsection
