@extends('layouts.public')

@section('title', 'Serah-terima Konsumsi — '.$division)
@section('subtitle', 'Kiosk Konsumsi Publik')
@section('footer_hint', 'Butuh bantuan? Silakan hubungi Panitia Acara di meja pembagian konsumsi.')

@section('content')
    <div class="mb-5 text-center">
        <a href="{{ route('public.events.consumption.index', ['event' => $event, 'slug' => $event->slug]) }}"
           class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white/90 px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs backdrop-blur-xs transition hover:border-slate-300 hover:bg-slate-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Pilih Divisi Lain
        </a>
    </div>

    <div class="space-y-6">
        <!-- Daftar penerima -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xl shadow-slate-200/40">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="font-bold text-slate-900">Anggota Penerima — {{ $division }}</h2>
                    <p class="text-xs text-slate-500">Semua anggota di bawah sudah check-in dan belum diambil porsinya.</p>
                </div>
                <span class="rounded-full bg-enc-100 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-enc-800">
                    {{ $participants->count() }} porsi
                </span>
            </div>

            <ul class="max-h-72 divide-y divide-slate-100 overflow-y-auto">
                @foreach($participants as $participant)
                    <li class="flex items-center justify-between gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-slate-900">{{ $participant->name }}</div>
                            <div class="mt-0.5 font-mono text-[11px] text-slate-500">{{ $participant->nik ?: '—' }}</div>
                        </div>
                        <span class="flex-none text-[11px] text-slate-400">
                            {{ $participant->attendance?->checked_in_at?->translatedFormat('H:i') ?? '' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Form serah-terima -->
        <form method="POST" enctype="multipart/form-data"
              action="{{ route('public.events.consumption.store', ['event' => $event, 'slug' => $event->slug]) }}"
              class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xl shadow-slate-200/40">
            @csrf
            <input type="hidden" name="division" value="{{ $division }}">

            <h2 class="border-b border-slate-100 pb-4 font-bold text-slate-900">Data Serah-terima</h2>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="representative_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Nama Perwakilan / PJ <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="representative_name" name="representative_name"
                           value="{{ old('representative_name') }}"
                           placeholder="Nama penanggung jawab divisi"
                           autocomplete="name" autocapitalize="words" spellcheck="false" enterkeyhint="next"
                           class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-base placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10 @error('representative_name') border-rose-400 @enderror">
                    @error('representative_name')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="representative_contact" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                        No. HP Perwakilan
                    </label>
                    <input type="text" id="representative_contact" name="representative_contact"
                           value="{{ old('representative_contact') }}"
                           placeholder="Opsional"
                           inputmode="tel" autocomplete="tel" enterkeyhint="next"
                           class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-base placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">
                </div>

                <div>
                    <label for="handed_over_by_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Nama Panitia Penyerah <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="handed_over_by_name" name="handed_over_by_name"
                           value="{{ old('handed_over_by_name') }}"
                           placeholder="Nama panitia di meja pembagian"
                           autocomplete="name" autocapitalize="words" spellcheck="false" enterkeyhint="done"
                           class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-base placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10 @error('handed_over_by_name') border-rose-400 @enderror">
                    @error('handed_over_by_name')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div data-camera-capture>
                    <label for="photo" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Foto Bukti Penyerahan <span class="text-rose-500">*</span>
                    </label>

                    <div class="mt-1.5 overflow-hidden rounded-xl border border-slate-300 bg-white @error('photo') border-rose-400 @enderror">
                        {{-- Pratinjau live dari kamera perangkat (getUserMedia). --}}
                        <video data-camera-video hidden playsinline autoplay muted
                               class="aspect-[4/3] w-full bg-slate-900 object-cover"></video>

                        {{-- Pratinjau hasil bidikan sebelum dikirim. --}}
                        <img data-camera-preview hidden alt="Pratinjau foto bukti"
                             class="aspect-[4/3] w-full bg-slate-900 object-cover">

                        {{-- Kanvas kerja tersembunyi: menangkap frame dari video. --}}
                        <canvas data-camera-canvas class="hidden"></canvas>

                        {{-- Tampilan awal sebelum kamera dinyalakan. --}}
                        <div data-camera-placeholder
                             class="flex aspect-[4/3] w-full flex-col items-center justify-center gap-2 bg-slate-100 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                            <span class="text-xs font-medium">Kamera belum aktif</span>
                        </div>

                        <div class="flex flex-wrap gap-2 border-t border-slate-100 bg-slate-50 p-3">
                            <button type="button" data-camera-start
                                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-enc-800 px-4 py-3 text-sm font-semibold text-white shadow-xs transition hover:bg-enc-900 disabled:cursor-not-allowed disabled:opacity-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                    <circle cx="12" cy="13" r="4"/>
                                </svg>
                                Nyalakan Kamera
                            </button>

                            <button type="button" data-camera-shoot hidden
                                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-enc-800 px-4 py-3 text-sm font-semibold text-white shadow-xs transition hover:bg-enc-900">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="13" r="4"/>
                                </svg>
                                Ambil Foto
                            </button>

                            <button type="button" data-camera-retake hidden
                                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="1 4 1 10 7 10"/>
                                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                                </svg>
                                Ulangi
                            </button>
                        </div>
                    </div>

                    <p data-camera-status role="status" aria-live="polite"
                       class="mt-1.5 text-[11px] font-medium text-slate-500">
                        Tekan "Nyalakan Kamera" untuk mulai.
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400">
                        Foto diambil langsung dari kamera perangkat (bukan unggah dari galeri) dan langsung terkirim ke sistem saat serah-terima dikonfirmasi.
                    </p>

                    {{-- Foto hasil bidikan dikirim sebagai berkas oleh form multipart ini. --}}
                    <input type="file" data-camera-input id="photo" name="photo" accept="image/*" class="hidden">

                    @error('photo')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Catatan
                    </label>
                    <textarea id="notes" name="notes" rows="2"
                              placeholder="Opsional"
                              class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-base placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-enc-100 bg-enc-50/70 p-3.5 text-xs text-enc-900">
                <div class="flex items-center justify-between">
                    <span class="font-semibold">Total porsi diserahkan</span>
                    <span class="text-base font-bold">{{ $participants->count() }}</span>
                </div>
            </div>

            <button type="submit" data-camera-submit
                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-enc-800 px-5 py-3.5 text-base font-semibold text-white shadow-sm transition hover:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/30 disabled:cursor-not-allowed disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Konfirmasi Serah-terima
            </button>
        </form>
    </div>
@endsection
