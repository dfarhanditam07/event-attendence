@extends('layouts.app')

@section('title', 'Serah-terima Konsumsi — '.$division)

@section('content')
    <div class="mb-4">
        <a href="{{ route('events.consumptions.index', $event) }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-slate-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Kembali ke Rekap Divisi
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $event->name }}</h1>
        <p class="mt-1 text-sm text-slate-500">Serah-terima konsumsi untuk divisi <span class="font-semibold text-slate-800">{{ $division }}</span>.</p>
    </div>

    @include('events._tabs', ['event' => $event])
    @include('consumptions._nav', ['event' => $event])

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Daftar penerima -->
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-enc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <h2 class="font-bold text-slate-900">Anggota Penerima Konsumsi</h2>
                    </div>
                    <span class="rounded-full bg-enc-100 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-enc-800">
                        {{ $participants->count() }} porsi
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5">Nama</th>
                                <th class="px-5 py-3.5">NIK</th>
                                <th class="px-5 py-3.5">Waktu Check-in</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($participants as $participant)
                                <tr class="transition-colors hover:bg-slate-50/80">
                                    <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $participant->name }}</td>
                                    <td class="px-5 py-3.5 font-mono text-xs text-slate-600">{{ $participant->nik ?: '—' }}</td>
                                    <td class="px-5 py-3.5 text-xs text-slate-600">
                                        {{ $participant->attendance?->checked_in_at?->translatedFormat('d M Y, H:i') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-3 flex items-start gap-1.5 text-xs text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-3.5 w-3.5 flex-none text-enc-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                Semua anggota di atas sudah check-in dan belum pernah diambil porsinya. Setiap peserta hanya bisa diambil konsumsinya satu kali.
            </p>
        </div>

        <!-- Form serah-terima -->
        <div>
            <form method="POST" action="{{ route('events.consumptions.store', $event) }}"
                  class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-xs">
                @csrf
                <input type="hidden" name="division" value="{{ $division }}">

                <h2 class="border-b border-slate-100 pb-4 font-bold text-slate-900">Data Perwakilan Pengambil</h2>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="representative_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Nama Perwakilan / PJ <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="representative_name" name="representative_name"
                               value="{{ old('representative_name') }}"
                               placeholder="Nama penanggung jawab divisi"
                               class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10 @error('representative_name') border-rose-400 @enderror">
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
                               class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10 @error('representative_contact') border-rose-400 @enderror">
                        @error('representative_contact')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Catatan
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  placeholder="Opsional, mis. jumlah kursi/meja"
                                  class="mt-1.5 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10 @error('notes') border-rose-400 @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 rounded-lg border border-enc-100 bg-enc-50/70 p-3 text-xs text-enc-900">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold">Total porsi diserahkan</span>
                        <span class="text-base font-bold">{{ $participants->count() }}</span>
                    </div>
                </div>

                <button type="submit"
                        class="mt-5 inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-enc-800 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Konfirmasi Serah-terima
                </button>
            </form>
        </div>
    </div>
@endsection
