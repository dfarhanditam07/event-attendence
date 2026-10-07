@extends('layouts.app')

@section('title', 'Tambah Peserta — '.$event->name)

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
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Tambah Peserta</h1>
        <p class="mt-1 text-sm text-slate-500">Mendaftarkan peserta secara manual untuk event: <strong class="text-slate-800">{{ $event->name }}</strong></p>
    </div>

    @include('events._tabs', ['event' => $event])

    @php
        $inputClasses = 'w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10 transition-colors';
    @endphp

    <div class="max-w-3xl rounded-xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-xs">
        <form method="POST" action="{{ route('events.participants.store', $event) }}">
            @csrf

            <div class="space-y-6">
                <!-- Section 1: Data Identitas -->
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2 mb-4">
                        Data Identitas
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label for="name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input id="name" name="name" type="text" required
                                   value="{{ old('name', $participant->name) }}"
                                   placeholder="Contoh: Budi Santoso, M.M."
                                   class="{{ $inputClasses }}">
                            @error('name')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nik" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Nomor Induk Karyawan (NIK) <span class="text-xs font-normal normal-case text-slate-400">(Opsional)</span>
                            </label>
                            <input id="nik" name="nik" type="text" inputmode="numeric"
                                   value="{{ old('nik', $participant->nik) }}"
                                   placeholder="Contoh: 3201010101900001 / EN-10492"
                                   class="{{ $inputClasses }}">
                            <p class="mt-1.5 text-xs text-slate-500">
                                Jika diisi, NIK harus unik untuk setiap peserta dalam event ini.
                            </p>
                            @error('nik')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Kontak & Lembaga -->
                <div class="pt-2">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2 mb-4">
                        Kontak & Instansi
                    </h3>

                    <div class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="phone" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                    No. HP / WhatsApp <span class="text-xs font-normal normal-case text-slate-400">(Opsional)</span>
                                </label>
                                <input id="phone" name="phone" type="tel"
                                       value="{{ old('phone', $participant->phone) }}"
                                       placeholder="Contoh: 081234567890"
                                       class="{{ $inputClasses }}">
                                @error('phone')
                                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                    Alamat Email <span class="text-xs font-normal normal-case text-slate-400">(Opsional)</span>
                                </label>
                                <input id="email" name="email" type="email"
                                       value="{{ old('email', $participant->email) }}"
                                       placeholder="Contoh: budi.santoso@perusahaan.com"
                                       class="{{ $inputClasses }}">
                                @error('email')
                                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="organization" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Instansi / Departemen / Perusahaan <span class="text-xs font-normal normal-case text-slate-400">(Opsional)</span>
                            </label>
                            <input id="organization" name="organization" type="text"
                                   value="{{ old('organization', $participant->organization) }}"
                                   placeholder="Contoh: Divisi Keuangan / PT Mitra Sejahtera"
                                   class="{{ $inputClasses }}">
                            @error('organization')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-slate-100 pt-6">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-enc-800 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Simpan Peserta
                </button>
                <a href="{{ route('events.participants.index', $event) }}"
                   class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
