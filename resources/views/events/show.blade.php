@extends('layouts.app')

@section('title', $event->name)

@section('content')
    <div class="mb-3">
        <a href="{{ route('events.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; Semua event</a>
    </div>
    <div class="mb-2 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">{{ $event->name }}</h1>
            <div class="mt-1 flex items-center gap-2">
                @include('events._status', ['status' => $event->status])
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $event)
                <a href="{{ route('events.edit', $event) }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">Edit</a>
            @endcan
            <a href="{{ route('events.attendances.check-in', $event) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Check-in</a>
            @can('delete', $event)
                <form method="POST" action="{{ route('events.destroy', $event) }}"
                      onsubmit="return confirm('Hapus event ini? Data peserta dan absensinya tetap tersimpan, tetapi event tidak akan tampil lagi.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-md border border-red-300 bg-white px-4 py-2 text-sm text-red-600 hover:bg-red-50">Hapus</button>
                </form>
            @endcan
        </div>
    </div>

    @include('events._tabs', ['event' => $event])

    @php($publicUrl = route('public.events.check-in', ['event' => $event, 'slug' => $event->slug]))

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="text-sm text-slate-500">Peserta terdaftar</div>
            <div class="mt-1 text-2xl font-semibold">{{ $event->participants_count }}</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="text-sm text-slate-500">Sudah hadir</div>
            <div class="mt-1 text-2xl font-semibold">{{ $event->attendances_count }}</div>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="text-sm text-slate-500">Kehadiran</div>
            <div class="mt-1 text-2xl font-semibold">
                {{ $event->participants_count > 0 ? round($event->attendances_count / $event->participants_count * 100) : 0 }}%
            </div>
        </div>
    </div>

    <dl class="grid max-w-2xl gap-4 rounded-lg border border-slate-200 bg-white p-6 text-sm sm:grid-cols-2">
        <div>
            <dt class="text-slate-500">Waktu mulai</dt>
            <dd class="mt-0.5 font-medium">{{ $event->starts_at->translatedFormat('l, d F Y, H:i') }}</dd>
        </div>
        <div>
            <dt class="text-slate-500">Waktu selesai</dt>
            <dd class="mt-0.5 font-medium">{{ $event->ends_at?->translatedFormat('l, d F Y, H:i') ?? '—' }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-slate-500">Lokasi</dt>
            <dd class="mt-0.5 font-medium">{{ $event->location ?: '—' }}</dd>
        </div>
        <div class="sm:col-span-2">
            <dt class="text-slate-500">Deskripsi</dt>
            <dd class="mt-0.5 whitespace-pre-line">{{ $event->description ?: '—' }}</dd>
        </div>
    </dl>

    <div class="mt-6 rounded-lg border border-indigo-100 bg-indigo-50/50 p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="font-semibold text-indigo-900">Link Check-in Publik</h3>
                <p class="mt-1 text-xs text-indigo-700/90">Bagikan link ini ke peserta. Mereka bisa self check-in tanpa login akun panitia.</p>
                <div class="mt-2 break-all rounded-md border border-indigo-200 bg-white px-3 py-2 font-mono text-xs text-slate-800">
                    <a href="{{ $publicUrl }}" class="hover:underline">{{ $publicUrl }}</a>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button"
                        onclick="navigator.clipboard.writeText(&quot;{{ $publicUrl }}&quot;).then(function(){ var el=document.getElementById('copy-ok'); if(el){ el.classList.remove('hidden'); setTimeout(function(){el.classList.add('hidden');},2500);} })"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                    Salin Link
                </button>
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener noreferrer"
                   class="rounded-md border border-indigo-200 bg-white px-4 py-2 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                    Buka Tab Baru
                </a>
                <span id="copy-ok" class="hidden self-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">
                    ✓ Tersalin!
                </span>
            </div>
        </div>
    </div>
@endsection
