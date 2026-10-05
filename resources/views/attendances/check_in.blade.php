@extends('layouts.app')

@section('title', 'Check-in — '.$event->name)

@section('content')
    <div class="mb-3">
        <a href="{{ route('events.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Semua event</a>
    </div>
    <div class="mb-2 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">{{ $event->name }}</h1>
            <div class="mt-1 flex items-center gap-2 text-sm">
                @include('events._status', ['status' => $event->status])
                @if(!$event->status->allowsCheckIn())
                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700">
                        Check-in hanya bisa saat event Dibuka
                    </span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('events.attendances.index', $event) }}"
               class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">
                Lihat daftar kehadiran
            </a>
        </div>
    </div>

    @include('events._tabs', ['event' => $event])

    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="text-sm text-slate-500">Total peserta</div>
            <div class="mt-1 text-2xl font-semibold">{{ $event->participants_count }}</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="text-sm text-slate-500">Sudah hadir</div>
            <div class="mt-1 text-2xl font-semibold text-emerald-700">{{ $event->attendances_count }}</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="text-sm text-slate-500">Persentase</div>
            <div class="mt-1 text-2xl font-semibold">
                {{ $event->participants_count > 0 ? round($event->attendances_count / $event->participants_count * 100) : 0 }}%
            </div>
        </div>
    </div>

    @if($lastResult)
        @if($lastResult['success'])
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm">
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 inline-flex h-5 w-5 flex-none items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                    <div class="flex-1">
                        <div class="font-semibold text-emerald-900">{{ $lastResult['participant_name'] }}</div>
                        @if($lastResult['participant_organization'])
                            <div class="text-emerald-700">{{ $lastResult['participant_organization'] }}</div>
                        @endif
                        @if(!empty($lastResult['attendance']))
                            <div class="mt-1 text-emerald-700">
                                Check-in berhasil:
                                {{ \Illuminate\Support\Carbon::parse($lastResult['attendance']['checked_in_at'])->translatedFormat('d M Y, H:i:s') }}
                                ·
                                {{ \App\Enums\AttendanceMethod::from($lastResult['attendance']['method'])->label() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm">
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 inline-flex h-5 w-5 flex-none items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">✕</span>
                    <div class="flex-1">
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

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="space-y-6 lg:col-span-3">
            @php($activeTab = request()->old('tab') ?: 'qr')
            <div class="rounded-lg border border-slate-200 bg-white">
                <div class="flex border-b border-slate-200 text-sm">
                    <button type="button" data-tab="qr"
                            class="tab-btn flex-1 border-b-2 px-4 py-3 font-medium {{ $activeTab === 'qr' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                        Scan QR
                    </button>
                    <button type="button" data-tab="nik"
                            class="tab-btn flex-1 border-b-2 px-4 py-3 font-medium {{ $activeTab === 'nik' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                        Input NIK
                    </button>
                    <button type="button" data-tab="manual"
                            class="tab-btn flex-1 border-b-2 px-4 py-3 font-medium {{ $activeTab === 'manual' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                        Manual
                    </button>
                </div>

                <section data-tab-panel="qr" class="tab-panel p-6 {{ $activeTab !== 'qr' ? 'hidden' : '' }}">
                    <h2 class="mb-4 text-lg font-semibold">Scan QR Code Peserta</h2>
                    <div class="mb-4 flex flex-wrap gap-2" id="qr-scanner-controls">
                        <button type="button" id="btn-start-scanner"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                            📷 Mulai Scanner Kamera
                        </button>
                        <button type="button" id="btn-stop-scanner" hidden
                                class="rounded-md border border-red-200 bg-white px-4 py-2 text-xs font-medium text-red-700 hover:bg-red-50">
                            ⏹️ Hentikan Scanner
                        </button>
                        <span id="scanner-status" class="self-center text-xs text-slate-500">Scanner belum dimulai</span>
                    </div>
                    <div id="qr-reader-container"
                         class="mb-4 hidden overflow-hidden rounded-lg border border-indigo-200 bg-indigo-50 p-2">
                        <div id="qr-reader" class="mx-auto max-w-md"></div>
                    </div>

                    <form method="POST" action="{{ route('events.attendances.check-in.qr', $event) }}" id="qr-form">
                        @csrf
                        <input type="hidden" name="tab" value="qr">
                        <label class="mb-1 block text-sm font-medium text-slate-700">QR Token (manual / paste)</label>
                        <div class="flex gap-2">
                            <input
                                id="qr_token"
                                type="text"
                                name="qr_token"
                                placeholder="Hasil scan akan otomatis terisi disini..."
                                class="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm font-mono focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                required autofocus autocomplete="off">
                            <button type="submit"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Check-in
                            </button>
                        </div>
                        @error('qr_token')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </form>
                </section>

                <section data-tab-panel="nik" class="tab-panel p-6 {{ $activeTab !== 'nik' ? 'hidden' : '' }}">
                    <h2 class="mb-4 text-lg font-semibold">Cari Peserta dengan NIK</h2>
                    <form method="POST" action="{{ route('events.attendances.check-in.nik', $event) }}">
                        @csrf
                        <input type="hidden" name="tab" value="nik">
                        <div class="space-y-4">
                            <div>
                                <label for="nik" class="mb-1 block text-sm font-medium text-slate-700">NIK</label>
                                <input
                                    id="nik"
                                    type="text"
                                    name="nik"
                                    placeholder="Contoh: 3201010101900001"
                                    value="{{ old('nik') }}"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                    required>
                                @error('nik')
                                    <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="nik_notes" class="mb-1 block text-sm font-medium text-slate-700">Catatan (opsional)</label>
                                <input
                                    id="nik_notes"
                                    type="text"
                                    name="notes"
                                    placeholder="Contoh: baru datang, izin terlambat..."
                                    value="{{ old('notes') }}"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <button type="submit"
                                    class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Check-in via NIK
                            </button>
                        </div>
                    </form>
                </section>

                <section data-tab-panel="manual" class="tab-panel p-6 {{ $activeTab !== 'manual' ? 'hidden' : '' }}">
                    <h2 class="mb-4 text-lg font-semibold">Check-in Manual (Pilih Peserta)</h2>
                    <form method="POST" action="{{ route('events.attendances.check-in.manual', $event) }}">
                        @csrf
                        <input type="hidden" name="tab" value="manual">
                        <div class="space-y-4">
                            <div>
                                <label for="participant_search" class="mb-1 block text-sm font-medium text-slate-700">
                                    Cari peserta
                                </label>
                                <input
                                    id="participant_search"
                                    type="text"
                                    placeholder="Ketik nama / NIK / organisasi..."
                                    class="mb-2 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <select
                                    id="participant_id"
                                    name="participant_id"
                                    required
                                    size="8"
                                    class="w-full rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    <option value="" disabled>-- Pilih peserta --</option>
                                    @foreach($event->participants()->doesntHave('attendance')->orderByRaw('LOWER(name) ASC')->get(['id','name','nik','organization']) as $p)
                                        <option value="{{ $p->id }}"
                                                data-search="{{ mb_strtolower($p->name.' '.$p->nik.' '.$p->organization) }}"
                                                class="py-1">
                                            {{ $p->name }}
                                            @if($p->nik)
                                                <span class="text-slate-500">· NIK {{ $p->nik }}</span>
                                            @endif
                                            @if($p->organization)
                                                <span class="text-slate-500">· {{ $p->organization }}</span>
                                            @endif
                                        </option>
                                    @endforeach
                                    @php($done = $event->participants()->has('attendance')->orderByRaw('LOWER(name) ASC')->get(['id','name','nik','organization']))
                                    @if($done->isNotEmpty())
                                        <optgroup label="— Sudah hadir (pilih untuk lihat detail / cek duplikat) —">
                                            @foreach($done as $p)
                                                <option value="{{ $p->id }}"
                                                        data-search="{{ mb_strtolower($p->name.' '.$p->nik.' '.$p->organization) }}"
                                                        class="py-1 text-slate-500">
                                                    ✓ {{ $p->name }}
                                                    @if($p->nik)
                                                        <span>· NIK {{ $p->nik }}</span>
                                                    @endif
                                                    @if($p->organization)
                                                        <span>· {{ $p->organization }}</span>
                                                    @endif
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                @error('participant_id')
                                    <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label for="manual_notes" class="mb-1 block text-sm font-medium text-slate-700">Catatan (opsional)</label>
                                <input
                                    id="manual_notes"
                                    type="text"
                                    name="notes"
                                    placeholder="Contoh: dikenali panitia, data ditambahkan manual..."
                                    value="{{ old('notes') }}"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <button type="submit"
                                    class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Tandai Hadir
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="rounded-lg border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h2 class="font-semibold text-slate-900">Check-in terbaru</h2>
                </div>
                @if($recent->isEmpty())
                    <div class="p-6 text-center text-sm text-slate-500">
                        Belum ada check-in.
                    </div>
                @else
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach($recent as $a)
                            <li class="flex items-start gap-3 px-4 py-3">
                                <span class="mt-0.5 inline-flex h-7 w-7 flex-none items-center justify-center rounded-full bg-emerald-100 text-xs font-semibold text-emerald-700">
                                    {{ strtoupper(substr($a->participant?->name ?? '?', 0, 1)) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate font-medium text-slate-900">
                                        {{ $a->participant?->name ?? '(data dihapus)' }}
                                    </div>
                                    <div class="mt-0.5 truncate text-xs text-slate-500">
                                        {{ \Illuminate\Support\Carbon::parse($a->checked_in_at)->translatedFormat('H:i:s') }}
                                        · {{ $a->method->label() }}
                                        · oleh {{ $a->checkedInBy?->name ?? '-' }}
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"
            onerror="window.__html5qrLoadFailed=true"></script>
    <script>
        (function () {
            document.querySelectorAll('.tab-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var target = btn.getAttribute('data-tab');
                    document.querySelectorAll('.tab-btn').forEach(function (b) {
                        var active = b === btn;
                        b.classList.toggle('border-indigo-600', active);
                        b.classList.toggle('text-indigo-600', active);
                        b.classList.toggle('border-transparent', !active);
                        b.classList.toggle('text-slate-600', !active);
                    });
                    document.querySelectorAll('.tab-panel').forEach(function (panel) {
                        panel.classList.toggle('hidden', panel.getAttribute('data-tab-panel') !== target);
                    });
                    // Focus ke input utama tiap tab
                    var focusMap = { qr: '#qr_token', nik: '#nik', manual: '#participant_search' };
                    var el = document.querySelector(focusMap[target] || '');
                    if (el) { el.focus(); }
                });
            });

            var qrInput = document.getElementById('qr_token');
            var qrForm = document.getElementById('qr_form') || document.getElementById('qr-form');
            function submitIfLooksValid() {
                if (!qrInput || !qrForm) return;
                var val = (qrInput.value || '').trim();
                if (val.length >= 8) qrForm.submit();
            }
            if (qrInput && qrForm) {
                // Auto-submit ketika user paste token panjang (dari scanner QR external).
                qrInput.addEventListener('paste', function () { setTimeout(submitIfLooksValid, 30); });
                qrInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' && (qrInput.value || '').trim().length >= 6) {
                        e.preventDefault();
                        qrForm.submit();
                    }
                });
                // Input event: untuk scanner eksternal yang mensimulasikan ketikan
                var typingTimer;
                qrInput.addEventListener('input', function () {
                    clearTimeout(typingTimer);
                    var val = (qrInput.value || '').trim();
                    if (val.length >= 20) {
                        typingTimer = setTimeout(submitIfLooksValid, 180);
                    }
                });
            }

            // --- HTML5 QR Code Scanner Kamera ---
            var scanner = null;
            var scanActive = false;
            var lastScanAt = 0;
            var btnStart = document.getElementById('btn-start-scanner');
            var btnStop = document.getElementById('btn-stop-scanner');
            var status = document.getElementById('scanner-status');
            var readerEl = document.getElementById('qr-reader');
            var readerContainer = document.getElementById('qr-reader-container');

            function setStatus(msg, cls) {
                if (status) {
                    status.textContent = msg;
                    status.className = 'self-center text-xs ' + (cls || 'text-slate-500');
                }
            }

            function onQrScanSuccess(decodedText) {
                // Throttle: jangan sampai 2x submit dalam 3 detik
                var now = Date.now();
                if (now - lastScanAt < 3000) return;
                lastScanAt = now;

                if (qrInput) {
                    qrInput.value = (decodedText || '').trim();
                }
                if (typeof navigator !== 'undefined' && navigator.vibrate) {
                    try { navigator.vibrate(100); } catch (_) {}
                }
                setStatus('✓ Terdeteksi! Mengirim data...', 'text-emerald-700 font-medium');
                submitIfLooksValid();
            }
            function onQrScanFailure() {} // normal saat scanner bekerja

            if (btnStart && readerEl) {
                btnStart.addEventListener('click', async function () {
                    if (window.__html5qrLoadFailed || typeof Html5Qrcode === 'undefined') {
                        setStatus('Gagal memuat library scanner (jaringan offline). Pakai paste token manual.', 'text-red-600');
                        return;
                    }
                    setStatus('Meminta izin kamera & memulai scanner...', 'text-indigo-600');
                    try {
                        scanner = scanner || new Html5Qrcode(readerEl.id, { formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE], verbose: false });
                        await scanner.start(
                            { facingMode: 'environment' },
                            { fps: 10, qrbox: function (vw) { return Math.min(vw, 320); } },
                            onQrScanSuccess,
                            onQrScanFailure,
                        );
                        scanActive = true;
                        readerContainer && readerContainer.classList.remove('hidden');
                        btnStart.hidden = true;
                        btnStop && (btnStop.hidden = false);
                        setStatus('Scanner aktif — arahkan ke QR code peserta.', 'text-emerald-700 font-medium');
                    } catch (err) {
                        setStatus('Gagal mulai kamera: ' + (err && err.message ? err.message : err) + '. Pakai paste token manual.', 'text-red-600');
                        try { scanner && scanner.clear(); } catch (_) {}
                        scanner = null;
                    }
                });
                btnStop && btnStop.addEventListener('click', async function () {
                    if (!scanner || !scanActive) return;
                    try { await scanner.stop(); await scanner.clear(); } catch (_) {}
                    scanActive = false;
                    readerContainer && readerContainer.classList.add('hidden');
                    btnStart.hidden = false;
                    btnStop.hidden = true;
                    setStatus('Scanner dihentikan.', 'text-slate-500');
                });
            }

            var s = document.getElementById('participant_search');
            var sel = document.getElementById('participant_id');
            if (s && sel) {
                s.addEventListener('input', function () {
                    var term = (s.value || '').toLowerCase();
                    sel.querySelectorAll('option').forEach(function (opt) {
                        if (opt.getAttribute('value') === '' && opt.disabled) {
                            opt.style.display = '';
                            return;
                        }
                        var hay = (opt.getAttribute('data-search') || opt.textContent || '').toLowerCase();
                        var show = !term || hay.indexOf(term) !== -1;
                        opt.style.display = show ? '' : 'none';
                    });
                    var optgroups = sel.querySelectorAll('optgroup');
                    optgroups.forEach(function (og) {
                        var visible = Array.from(og.querySelectorAll('option')).some(function (opt) { return opt.style.display !== 'none'; });
                        og.style.display = visible ? '' : 'none';
                    });
                });
            }
        })();
    </script>
@endsection
