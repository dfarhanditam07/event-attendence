@extends('layouts.app')

@section('title', 'Laporan Konsumsi — '.$event->name)

@section('content')
    <div class="mb-4">
        <a href="{{ route('events.index') }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 transition hover:text-slate-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
            Kembali ke Daftar Event
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $event->name }}</h1>
        <p class="mt-1 text-sm text-slate-500">
            Laporan audit trail penyerahan konsumsi: siapa perwakilan pengambil, berapa porsi, dan panitia yang menyerahkan.
        </p>
    </div>

    @include('events._tabs', ['event' => $event])
    @include('consumptions._nav', ['event' => $event])

    @if($pickups->isEmpty())
        <div class="rounded-xl border border-slate-200/80 bg-white p-12 text-center shadow-xs">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
            </div>
            <h3 class="mt-3 text-sm font-semibold text-slate-900">Belum Ada Transaksi Konsumsi</h3>
            <p class="mt-1 text-xs text-slate-500">Riwayat penyerahan konsumsi akan tampil di sini.</p>
            <div class="mt-5">
                <a href="{{ route('events.consumptions.index', $event) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50">
                    Buka Rekap Divisi
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach($pickups as $pickup)
                <article class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-xs">
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 flex-none items-center justify-center rounded-lg bg-enc-50 text-enc-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                                </svg>
                            </span>
                            <div>
                                <div class="font-bold text-slate-900">{{ $pickup->division }}</div>
                                <div class="font-mono text-[11px] text-slate-500">
                                    {{ $pickup->picked_up_at->translatedFormat('d M Y, H:i') }} WIB
                                </div>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 self-start rounded-full border border-emerald-200/90 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 sm:self-auto">
                            {{ $pickup->portions_count }} porsi
                        </span>
                    </div>

                    <dl class="grid gap-4 px-5 py-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Perwakilan Pengambil</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pickup->representative_name }}</dd>
                            @if($pickup->representative_contact)
                                <dd class="text-xs text-slate-500">{{ $pickup->representative_contact }}</dd>
                            @endif
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Diserahkan Oleh (Panitia)</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $pickup->handedOverBy?->name ?? '(akun telah dihapus)' }}</dd>
                        </div>
                    </dl>

                    @if($pickup->notes)
                        <div class="border-t border-slate-100 px-5 py-3">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Catatan: </span>
                            <span class="text-xs text-slate-700">{{ $pickup->notes }}</span>
                        </div>
                    @endif

                    <details class="group border-t border-slate-100">
                        <summary class="flex cursor-pointer list-none items-center gap-1.5 px-5 py-3 text-xs font-semibold text-enc-800 transition hover:bg-enc-50/60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition group-open:rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                            Lihat {{ $pickup->claims_count }} penerima konsumsi
                        </summary>
                        <ul class="divide-y divide-slate-100 border-t border-slate-100 bg-slate-50/40">
                            @foreach($pickup->claims as $claim)
                                <li class="flex items-center justify-between px-5 py-2.5 text-sm">
                                    <span class="font-medium text-slate-800">{{ $claim->participant?->name ?? '(peserta telah dihapus)' }}</span>
                                    <span class="text-xs text-slate-500">{{ $claim->participant?->organization ?: '—' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                </article>
            @endforeach
        </div>

        @if($pickups->hasPages())
            <div class="mt-5">
                {{ $pickups->links() }}
            </div>
        @endif
    @endif
@endsection
