@extends('layouts.public')

@section('title', 'Check-in '.\Illuminate\Support\Str::title($mode))

@section('content')
    <div class="mb-5 text-center">
        <a href="{{ route('public.events.check-in', ['event' => $event, 'slug' => $event->slug]) }}"
           class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">
            &larr; Ganti metode check-in
        </a>
    </div>

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
                                ·
                                {{ \App\Enums\AttendanceMethod::from($lastResult['attendance']['method'])->label() }}
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

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($mode === 'qr')
            <h2 class="mb-1 text-lg font-semibold text-slate-900">Scan QR Code</h2>
            <p class="mb-5 text-sm text-slate-500">Pindai QR Code dari undangan / kartu peserta Anda, atau tempel hasil scan ke kolom dibawah.</p>

            <div class="mb-5 flex flex-wrap gap-2" id="pub-qr-scanner-controls">
                <button type="button" id="pub-btn-start-scanner"
                        class="rounded-md bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                    📷 Pakai Kamera untuk Scan
                </button>
                <button type="button" id="pub-btn-stop-scanner" hidden
                        class="rounded-md border border-red-200 bg-white px-3 py-2 text-xs font-medium text-red-700 hover:bg-red-50">
                    ⏹️ Hentikan Kamera
                </button>
                <span id="pub-scanner-status" class="self-center text-xs text-slate-500">Siap — pilih kamera atau input manual</span>
            </div>
            <div id="pub-qr-reader-container"
                 class="mb-5 hidden overflow-hidden rounded-lg border border-indigo-200 bg-indigo-50 p-2">
                <div id="pub-qr-reader" class="mx-auto max-w-md"></div>
            </div>

            <form method="POST" id="pub-qr-form" action="{{ route('public.events.check-in.submit-qr', ['event' => $event, 'slug' => $event->slug]) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="qr_token" class="mb-1 block text-sm font-medium text-slate-700">QR Token (manual / paste)</label>
                        <input id="qr_token" name="qr_token" type="text" required autofocus autocomplete="off"
                               placeholder="Hasil scan akan otomatis terisi disini..."
                               class="w-full rounded-md border border-slate-300 px-3 py-3 font-mono text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <button type="submit"
                            class="w-full rounded-md bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700">
                        Konfirmasi Hadir
                    </button>
                </div>
            </form>

            <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"
                    onerror="window.__html5qrLoadFailed=true"></script>
            <script>
                (function () {
                    var inp = document.getElementById('qr_token');
                    var form = document.getElementById('pub-qr-form');
                    function submitIfLooksValid() {
                        if (!inp || !form) return;
                        if ((inp.value || '').trim().length >= 8) form.submit();
                    }
                    if (inp && form) {
                        inp.addEventListener('paste', function () { setTimeout(submitIfLooksValid, 30); });
                        inp.addEventListener('keydown', function (e) {
                            if (e.key === 'Enter' && (inp.value || '').trim().length >= 6) {
                                e.preventDefault();
                                submitIfLooksValid();
                            }
                        });
                        var typingTimer;
                        inp.addEventListener('input', function () {
                            clearTimeout(typingTimer);
                            if ((inp.value || '').trim().length >= 20) {
                                typingTimer = setTimeout(submitIfLooksValid, 220);
                            }
                        });
                    }

                    var scanner = null, active = false, last = 0;
                    var btnStart = document.getElementById('pub-btn-start-scanner');
                    var btnStop = document.getElementById('pub-btn-stop-scanner');
                    var status = document.getElementById('pub-scanner-status');
                    var reader = document.getElementById('pub-qr-reader');
                    var readerWrap = document.getElementById('pub-qr-reader-container');
                    function setStatus(msg, cls) {
                        if (status) {
                            status.textContent = msg;
                            status.className = 'self-center text-xs ' + (cls || 'text-slate-500');
                        }
                    }
                    function onDecoded(txt) {
                        var now = Date.now();
                        if (now - last < 3500) return;
                        last = now;
                        if (inp) inp.value = (txt || '').trim();
                        if (navigator && navigator.vibrate) { try { navigator.vibrate(120); } catch (_) {} }
                        setStatus('✓ Terdeteksi! Mengonfirmasi...', 'text-emerald-700 font-medium');
                        submitIfLooksValid();
                    }
                    if (btnStart && reader) {
                        btnStart.addEventListener('click', async function () {
                            if (window.__html5qrLoadFailed || typeof Html5Qrcode === 'undefined') {
                                setStatus('Gagal memuat library scanner (jaringan offline). Pakai paste token manual.', 'text-red-600');
                                return;
                            }
                            setStatus('Meminta izin kamera...', 'text-indigo-600');
                            try {
                                scanner = scanner || new Html5Qrcode(reader.id, { formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE], verbose: false });
                                await scanner.start(
                                    { facingMode: 'environment' },
                                    { fps: 10, qrbox: function (v) { return Math.min(v, 300); } },
                                    onDecoded,
                                    function () {},
                                );
                                active = true;
                                readerWrap && readerWrap.classList.remove('hidden');
                                btnStart.hidden = true;
                                btnStop && (btnStop.hidden = false);
                                setStatus('Scanner aktif — arahkan ke QR code kartu peserta.', 'text-emerald-700 font-medium');
                            } catch (err) {
                                setStatus('Gagal mulai kamera: ' + (err && err.message ? err.message : err) + '. Pakai paste token manual.', 'text-red-600');
                                try { scanner && scanner.clear(); } catch (_) {}
                                scanner = null;
                            }
                        });
                        btnStop && btnStop.addEventListener('click', async function () {
                            if (!scanner || !active) return;
                            try { await scanner.stop(); await scanner.clear(); } catch (_) {}
                            active = false;
                            readerWrap && readerWrap.classList.add('hidden');
                            btnStart.hidden = false;
                            btnStop.hidden = true;
                            setStatus('Scanner dihentikan.', 'text-slate-500');
                        });
                    }
                })();
            </script>
        @elseif($mode === 'nik')
            <h2 class="mb-1 text-lg font-semibold text-slate-900">Check-in dengan NIK</h2>
            <p class="mb-6 text-sm text-slate-500">Masukkan NIK KTP Anda (16 digit) yang sudah didaftarkan panitia.</p>

            <form method="POST" action="{{ route('public.events.check-in.submit-nik', ['event' => $event, 'slug' => $event->slug]) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="nik" class="mb-1 block text-sm font-medium text-slate-700">Nomor Induk Kependudukan (NIK)</label>
                        <input id="nik" name="nik" type="text" inputmode="numeric" required autofocus
                               placeholder="Contoh: 3201010101900001"
                               value="{{ old('nik') }}"
                               class="w-full rounded-md border border-slate-300 px-3 py-3 font-mono text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <button type="submit"
                            class="w-full rounded-md bg-sky-600 px-4 py-3 text-sm font-semibold text-white hover:bg-sky-700">
                        Konfirmasi Hadir
                    </button>
                </div>
            </form>
        @else
            <h2 class="mb-1 text-lg font-semibold text-slate-900">Cari nama Anda</h2>
            <p class="mb-6 text-sm text-slate-500">Ketik nama lengkap / organisasi Anda, lalu pilih dari daftar yang muncul.</p>

            <form method="GET" class="mb-5">
                <label for="q" class="mb-1 block text-sm font-medium text-slate-700">Ketik nama / NIM / organisasi</label>
                <div class="flex gap-2">
                    <input id="q" name="q" type="search" value="{{ e($search ?? '') }}" autofocus
                           placeholder="Contoh: Budi Santoso"
                           class="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <button type="submit" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">
                        Cari
                    </button>
                </div>
            </form>

            @if(($search ?? '') !== '' && $candidates->isEmpty())
                <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    Tidak menemukan peserta dengan kata "<strong>{{ e($search) }}</strong>". Coba kata kunci lain, atau hubungi panitia jika Anda merasa sudah terdaftar.
                </div>
            @elseif(($search ?? '') !== '')
                <form method="POST" action="{{ route('public.events.check-in.submit-name', ['event' => $event, 'slug' => $event->slug]) }}">
                    @csrf
                    <div class="space-y-4">
                        <div class="max-h-72 overflow-y-auto rounded-md border border-slate-200 p-1">
                            @foreach($candidates as $c)
                                <label class="flex cursor-pointer items-start gap-3 rounded-md px-3 py-2 hover:bg-indigo-50 has-[:checked]:bg-indigo-50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-300">
                                    <input type="radio" name="participant_id" value="{{ $c->id }}"
                                           class="mt-1 h-4 w-4 flex-none rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                           @checked(old('participant_id') == $c->id) required>
                                    <div class="min-w-0 text-sm">
                                        <div class="font-medium text-slate-900">{{ $c->name }}</div>
                                        <div class="truncate text-xs text-slate-500">
                                            @if($c->nik) NIK {{ $c->nik }} @endif
                                            @if($c->nik && $c->organization) · @endif
                                            @if($c->organization) {{ $c->organization }} @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <label class="flex items-start gap-2 rounded-md border border-indigo-100 bg-indigo-50/40 p-3 text-xs text-indigo-900">
                            <input type="checkbox" name="confirmation" required
                                   class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span>Data di atas adalah <strong>saya</strong> dan saya menyatakan hadir di acara ini.</span>
                        </label>
                        @error('confirmation')
                            <div class="text-xs text-red-600">{{ $message }}</div>
                        @enderror

                        <button type="submit"
                                class="w-full rounded-md bg-amber-600 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-700">
                            Konfirmasi Hadir
                        </button>
                    </div>
                </form>
            @else
                <div class="rounded-md border border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                    Silakan ketik nama Anda di kolom pencarian di atas.
                </div>
            @endif
        @endif
    </div>
@endsection
