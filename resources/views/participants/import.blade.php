@extends('layouts.app')

@section('title', 'Import Peserta — '.$event->name)

@section('content')
    <div class="mb-4">
        <a href="{{ route('events.participants.index', $event) }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-slate-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Kembali ke Daftar Peserta
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Import Peserta dari CSV</h1>
        <p class="mt-1 text-sm text-slate-500">Unggah berkas spreadsheet CSV untuk mendaftarkan peserta secara massal ke event: <strong class="text-slate-800">{{ $event->name }}</strong></p>
    </div>

    @include('events._tabs', ['event' => $event])

    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Unggah File Card -->
        <div class="flex flex-col rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-enc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <h2 class="text-base font-bold text-slate-900">Unggah File CSV</h2>
            </div>

            <form method="POST" enctype="multipart/form-data" action="{{ route('events.participants.import.store', $event) }}" class="flex-1 flex flex-col justify-between">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label for="file" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-700">Pilih Berkas CSV</label>
                        <input
                            id="file"
                            type="file"
                            name="file"
                            accept=".csv,text/csv"
                            class="block w-full cursor-pointer rounded-xl border border-slate-300 bg-slate-50/50 text-sm text-slate-700 file:mr-4 file:cursor-pointer file:border-0 file:bg-enc-800 file:px-4 file:py-2.5 file:text-xs file:font-semibold file:text-white hover:file:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/20"
                            required>
                        @error('file')
                            <div class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="rounded-xl border border-enc-100 bg-enc-50/60 p-4 text-xs text-enc-950">
                        <div class="font-bold flex items-center gap-1.5 text-enc-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-enc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="16" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                            </svg>
                            Format Kolom Header (Baris Pertama):
                        </div>
                        <ul class="mt-2.5 space-y-1.5 text-slate-700">
                            <li class="flex items-center gap-2">
                                <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] font-semibold text-enc-800 ring-1 ring-slate-200">name</code> / <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] font-semibold text-enc-800 ring-1 ring-slate-200">nama</code>
                                <span class="font-semibold text-rose-600">(Wajib)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">nik</code>
                                <span class="text-slate-500">(Opsional, unik per event)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">email</code>
                                <span class="text-slate-500">(Opsional)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">phone</code> / <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">no_hp</code>
                                <span class="text-slate-500">(Opsional)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">organization</code> / <code class="rounded bg-white px-1.5 py-0.5 font-mono text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">instansi</code>
                                <span class="text-slate-500">(Opsional)</span>
                            </li>
                        </ul>
                        <div class="mt-3 border-t border-enc-200/60 pt-2 text-[11px] text-slate-600 leading-relaxed">
                            Mendukung pemisah koma (,) maupun titik koma (;) untuk format Excel standar Indonesia. Maksimal 5.000 baris data per unggahan.
                        </div>
                    </div>

                    <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3.5 text-xs text-amber-900 flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-none text-amber-700 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <div class="leading-relaxed">
                            <strong>Penting:</strong> Format kolom <code>nik</code> dan <code>phone</code> sebagai <em>Text</em> di Excel sebelum menyimpan CSV agar angka panjang tidak terpotong menjadi format ilmiah (3.2E+15) dan angka 0 di depan tidak hilang.
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-enc-800 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        Mulai Import Peserta
                    </button>
                </div>
            </form>
        </div>

        <!-- Contoh Isi File Card -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="3" y1="9" x2="21" y2="9"/>
                        <line x1="9" y1="21" x2="9" y2="9"/>
                    </svg>
                    <h2 class="text-base font-bold text-slate-900">Contoh Struktur Data CSV</h2>
                </div>

                <p class="mb-4 text-xs text-slate-500">
                    Pastikan baris teratas file Anda memuat nama kolom yang tepat seperti tabel berikut:
                </p>

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50/80 text-left font-semibold text-slate-600 uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="px-3 py-2.5">name</th>
                                <th class="px-3 py-2.5">nik</th>
                                <th class="px-3 py-2.5">email</th>
                                <th class="px-3 py-2.5">phone</th>
                                <th class="px-3 py-2.5">organization</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2.5 font-medium text-slate-900">Budi Santoso</td>
                                <td class="px-3 py-2.5 font-mono">3201010101900001</td>
                                <td class="px-3 py-2.5">budi@example.com</td>
                                <td class="px-3 py-2.5 font-mono">081234567890</td>
                                <td class="px-3 py-2.5">Kementerian XYZ</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2.5 font-medium text-slate-900">Siti Rahayu</td>
                                <td class="px-3 py-2.5 font-mono">3201010202900002</td>
                                <td class="px-3 py-2.5">siti@example.com</td>
                                <td class="px-3 py-2.5 font-mono">081398765432</td>
                                <td class="px-3 py-2.5">Dinas ABC</td>
                            </tr>
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-2.5 font-medium text-slate-900">Ahmad Fadli</td>
                                <td class="px-3 py-2.5 font-mono">3201010303900003</td>
                                <td class="px-3 py-2.5">ahmad@perusahaan.co.id</td>
                                <td class="px-3 py-2.5 font-mono">081812345678</td>
                                <td class="px-3 py-2.5">PT Mitra Sinergi</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50/60 p-4 text-xs text-slate-500">
                <span class="font-semibold text-slate-700">Tips Import:</span> Sistem secara otomatis mendeteksi dan mengabaikan baris kosong, serta memvalidasi nomor NIK unik di dalam event.
            </div>
        </div>
    </div>

    <!-- Hasil Import: Baris Gagal (Audit Log) -->
    @if($result && !empty($result['failed']))
        <div class="mt-8 rounded-xl border border-amber-300/80 bg-amber-50/60 p-6 shadow-xs">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-200 text-amber-800 font-bold text-xs">!</span>
                    <h2 class="text-base font-bold text-amber-950">
                        Laporan Baris Gagal Diimpor ({{ count($result['failed']) }})
                    </h2>
                </div>
                @if(!empty($result['success']))
                    <span class="rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                        ✓ Berhasil Terimpor: {{ $result['success'] }} peserta
                    </span>
                @endif
            </div>

            <p class="mb-4 text-xs text-amber-800">
                Berikut adalah baris data yang ditolak selama proses impor. Silakan perbaiki data pada baris yang bersangkutan dan unggah kembali.
            </p>

            <div class="overflow-x-auto rounded-lg border border-amber-200 bg-white">
                <table class="min-w-full divide-y divide-amber-100 text-xs text-left">
                    <thead class="bg-amber-50/80 font-semibold text-amber-900">
                        <tr>
                            <th class="px-4 py-3">Baris</th>
                            <th class="px-4 py-3">Data yang Terbaca</th>
                            <th class="px-4 py-3">Penyebab Kegagalan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-50 text-slate-700">
                        @foreach($result['failed'] as $fail)
                            <tr class="align-top hover:bg-amber-50/30">
                                <td class="px-4 py-3 font-mono font-bold text-amber-800">#{{ $fail['row'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="space-y-0.5">
                                        @foreach(['name','nik','email','phone','organization'] as $k)
                                            @if(filled($fail['data'][$k] ?? null))
                                                <div>
                                                    <span class="font-medium text-slate-500">{{ $k }}:</span>
                                                    <span class="text-slate-800">{{ $fail['data'][$k] }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                        @empty($fail['data'])
                                            <span class="italic text-slate-400">(baris kosong)</span>
                                        @endempty
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <ul class="list-inside list-disc space-y-1 text-rose-700">
                                        @foreach(($fail['errors'] ?? []) as $field => $messages)
                                            @foreach($messages as $m)
                                                <li>
                                                    @if(!str_starts_with($field, '_'))
                                                        <span class="font-semibold">{{ $field }}:</span>
                                                    @endif
                                                    {{ $m }}
                                                </li>
                                            @endforeach
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
