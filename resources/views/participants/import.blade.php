@extends('layouts.app')

@section('title', 'Import Peserta — '.$event->name)

@section('content')
    <div class="mb-3">
        <a href="{{ route('events.participants.index', $event) }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Daftar peserta</a>
    </div>
    <div class="mb-2 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">{{ $event->name }}</h1>
            <div class="mt-1 text-sm text-slate-500">Import peserta dari CSV</div>
        </div>
    </div>

    @include('events._tabs', ['event' => $event])

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Unggah file</h2>

            <form method="POST" enctype="multipart/form-data" action="{{ route('events.participants.import.store', $event) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="file" class="mb-1 block text-sm font-medium text-slate-700">File CSV</label>
                        <input
                            id="file"
                            type="file"
                            name="file"
                            accept=".csv,text/csv"
                            class="block w-full rounded-md border border-slate-300 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 file:mr-4 file:rounded-md file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200"
                            required>
                        @error('file')
                            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="rounded-md border border-indigo-100 bg-indigo-50 p-4 text-xs text-indigo-800">
                        <p class="font-medium">Format kolom (baris pertama adalah header):</p>
                        <ul class="mt-2 list-inside list-disc space-y-0.5">
                            <li><code class="rounded bg-white px-1 py-0.5">name</code> / <code class="rounded bg-white px-1 py-0.5">nama</code> <span class="font-medium">(wajib)</span></li>
                            <li><code class="rounded bg-white px-1 py-0.5">nik</code> (opsional, unik per event)</li>
                            <li><code class="rounded bg-white px-1 py-0.5">email</code> (opsional)</li>
                            <li><code class="rounded bg-white px-1 py-0.5">phone</code> / <code class="rounded bg-white px-1 py-0.5">no_hp</code> (opsional)</li>
                            <li><code class="rounded bg-white px-1 py-0.5">organization</code> / <code class="rounded bg-white px-1 py-0.5">instansi</code> (opsional)</li>
                        </ul>
                        <p class="mt-3">Pemisah koma maupun titik koma (Excel Indonesia) dikenali otomatis. Maksimal 5.000 baris per file.</p>
                        <p class="mt-1 font-medium text-amber-800">Penting: di Excel, format kolom <code class="rounded bg-white px-1 py-0.5">nik</code> dan <code class="rounded bg-white px-1 py-0.5">phone</code> sebagai <em>Text</em> sebelum mengisi, agar angka panjang tidak berubah menjadi 3,2E+15 dan angka 0 di depan tidak hilang.</p>
                    </div>

                    <button type="submit"
                            class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Import Peserta
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Contoh isi file</h2>
            <div class="overflow-x-auto rounded-md border border-slate-200 text-xs">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-left">
                        <tr>
                            <th class="px-3 py-2 font-medium text-slate-600">name</th>
                            <th class="px-3 py-2 font-medium text-slate-600">nik</th>
                            <th class="px-3 py-2 font-medium text-slate-600">email</th>
                            <th class="px-3 py-2 font-medium text-slate-600">phone</th>
                            <th class="px-3 py-2 font-medium text-slate-600">organization</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <tr>
                            <td class="px-3 py-2">Budi Santoso</td>
                            <td class="px-3 py-2">3201010101900001</td>
                            <td class="px-3 py-2">budi@example.com</td>
                            <td class="px-3 py-2">081234567890</td>
                            <td class="px-3 py-2">Kementerian XYZ</td>
                        </tr>
                        <tr>
                            <td class="px-3 py-2">Siti Rahayu</td>
                            <td class="px-3 py-2">3201010202900002</td>
                            <td class="px-3 py-2">siti@example.com</td>
                            <td class="px-3 py-2">081398765432</td>
                            <td class="px-3 py-2">Dinas ABC</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($result && !empty($result['failed']))
        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-6">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-amber-900">
                    Baris gagal diimpor ({{ count($result['failed']) }})
                </h2>
                @if(!empty($result['success']))
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-800">
                        Berhasil: {{ $result['success'] }}
                    </span>
                @endif
            </div>
            <div class="overflow-x-auto rounded-md border border-amber-200 bg-white">
                <table class="min-w-full divide-y divide-amber-100 text-xs">
                    <thead class="bg-amber-50 text-left text-amber-800">
                        <tr>
                            <th class="px-3 py-2 font-medium">Baris</th>
                            <th class="px-3 py-2 font-medium">Data</th>
                            <th class="px-3 py-2 font-medium">Penyebab</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-50 text-slate-700">
                        @foreach($result['failed'] as $fail)
                            <tr class="align-top">
                                <td class="px-3 py-2 font-mono text-amber-800">{{ $fail['row'] }}</td>
                                <td class="px-3 py-2">
                                    <div class="space-y-0.5">
                                        @foreach(['name','nik','email','phone','organization'] as $k)
                                            @if(filled($fail['data'][$k] ?? null))
                                                <div>
                                                    <span class="font-medium text-slate-500">{{ $k }}:</span>
                                                    {{ $fail['data'][$k] }}
                                                </div>
                                            @endif
                                        @endforeach
                                        @empty($fail['data'])
                                            <span class="text-slate-400 italic">(kosong)</span>
                                        @endempty
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <ul class="list-inside list-disc space-y-0.5 text-red-700">
                                        @foreach(($fail['errors'] ?? []) as $field => $messages)
                                            @foreach($messages as $m)
                                                <li>
                                                    @if(!str_starts_with($field, '_'))
                                                        <span class="font-medium">{{ $field }}:</span>
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
