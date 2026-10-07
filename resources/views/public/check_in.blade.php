@extends('layouts.public')

@section('title', $mode === 'nik' ? 'Check-in dengan NIK' : 'Check-in dengan Nama')

@section('content')
    <div class="mb-5 text-center">
        <a href="{{ route('public.events.check-in', ['event' => $event, 'slug' => $event->slug]) }}"
           class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">
            &larr; Ganti metode check-in
        </a>
    </div>

    @include('public._result', ['lastResult' => $lastResult])

    @if(! $canCheckIn)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center text-sm text-amber-900">
            Check-in belum dibuka untuk acara ini. Hubungi panitia jika Anda yakin ini sebuah kesalahan.
        </div>
    @else
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @if($mode === 'nik')
            <h2 class="mb-1 text-lg font-semibold text-slate-900">Check-in dengan NIK</h2>
            <p class="mb-6 text-sm text-slate-500">Masukkan NIK Anda yang sudah didaftarkan panitia.</p>

            <form method="POST" action="{{ route('public.events.check-in.submit-nik', ['event' => $event, 'slug' => $event->slug]) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="nik" class="mb-1 block text-sm font-medium text-slate-700">Nomor Induk Kependudukan (NIK)</label>
                        <input id="nik" name="nik" type="text" inputmode="numeric" required autofocus autocomplete="off"
                               value="{{ old('nik') }}"
                               placeholder="Contoh: 3201010101900001"
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
            <p class="mb-6 text-sm text-slate-500">Ketik nama Anda (minimal 3 huruf), lalu pilih dari daftar yang muncul.</p>

            <form method="GET" class="mb-5">
                <label for="q" class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                <div class="flex gap-2">
                    <input id="q" name="q" type="search" value="{{ $search }}" autofocus autocomplete="off"
                           placeholder="Contoh: Budi Santoso"
                           class="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <button type="submit" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">
                        Cari
                    </button>
                </div>
            </form>

            @if($searchTooShort)
                <div class="rounded-md border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    Ketik minimal 3 huruf nama Anda.
                </div>
            @elseif($search !== '' && $candidates->isEmpty())
                <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    Tidak menemukan nama "<strong>{{ $search }}</strong>" yang belum check-in. Coba tulisan nama lain, atau hubungi panitia.
                    Jika Anda sudah check-in sebelumnya, Anda tidak perlu mengulang.
                </div>
            @elseif($search !== '')
                <form method="POST" action="{{ route('public.events.check-in.submit-name', ['event' => $event, 'slug' => $event->slug]) }}">
                    @csrf
                    <div class="space-y-4">
                        <div class="max-h-72 overflow-y-auto rounded-md border border-slate-200 p-1">
                            @foreach($candidates as $c)
                                <label class="flex cursor-pointer items-start gap-3 rounded-md px-3 py-2 hover:bg-indigo-50 has-[:checked]:bg-indigo-50 has-[:checked]:ring-1 has-[:checked]:ring-indigo-300">
                                    <input type="radio" name="participant_id" value="{{ $c['id'] }}"
                                           class="mt-1 h-4 w-4 flex-none border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                           @checked(old('participant_id') == $c['id']) required>
                                    <div class="min-w-0 text-sm">
                                        <div class="font-medium text-slate-900">{{ $c['name'] }}</div>
                                        <div class="truncate text-xs text-slate-500">
                                            @if($c['nik_masked']) NIK {{ $c['nik_masked'] }} @endif
                                            @if($c['nik_masked'] && $c['organization']) · @endif
                                            @if($c['organization']) {{ $c['organization'] }} @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <label class="flex items-start gap-2 rounded-md border border-indigo-100 bg-indigo-50/40 p-3 text-xs text-indigo-900">
                            <input type="checkbox" name="confirmation" value="1" required
                                   class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span>Data di atas adalah <strong>saya</strong> dan saya menyatakan hadir di acara ini.</span>
                        </label>

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
    @endif
@endsection
