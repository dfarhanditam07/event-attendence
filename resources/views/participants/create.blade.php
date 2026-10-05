@extends('layouts.app')

@section('title', 'Tambah Peserta — '.$event->name)

@section('content')
    <div class="mb-3">
        <a href="{{ route('events.participants.index', $event) }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Daftar peserta</a>
    </div>
    <div class="mb-2 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Tambah Peserta</h1>
            <div class="mt-1 text-sm text-slate-500">{{ $event->name }}</div>
        </div>
    </div>

    @include('events._tabs', ['event' => $event])

    <div class="max-w-2xl rounded-lg border border-slate-200 bg-white p-6">
        <form method="POST" action="{{ route('events.participants.store', $event) }}">
            @csrf
            @php($input = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500')

            <div class="space-y-5">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">
                        Nama lengkap <span class="text-red-500">*</span>
                    </label>
                    <input id="name" name="name" type="text" required value="{{ old('name', $participant->name) }}" class="{{ $input }}">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="nik" class="mb-1 block text-sm font-medium text-slate-700">NIK</label>
                        <input id="nik" name="nik" type="text" inputmode="numeric"
                               value="{{ old('nik', $participant->nik) }}"
                               placeholder="Opsional, tapi jika diisi harus unik"
                               class="{{ $input }}">
                        @error('nik')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">No. HP / WhatsApp</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $participant->phone) }}" class="{{ $input }}">
                        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $participant->email) }}" class="{{ $input }}">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="organization" class="mb-1 block text-sm font-medium text-slate-700">Organisasi / Instansi / Perusahaan</label>
                    <input id="organization" name="organization" type="text"
                           value="{{ old('organization', $participant->organization) }}"
                           class="{{ $input }}">
                    @error('organization')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Simpan Peserta
                </button>
                <a href="{{ route('events.participants.index', $event) }}"
                   class="text-sm text-slate-600 hover:text-slate-900">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
