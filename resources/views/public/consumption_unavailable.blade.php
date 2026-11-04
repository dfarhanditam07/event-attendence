@extends('layouts.public')

@section('title', 'Kiosk Konsumsi Tidak Aktif')
@section('subtitle', 'Kiosk Konsumsi Publik')
@section('footer_hint', 'Butuh bantuan? Silakan hubungi Panitia Acara di meja pembagian konsumsi.')

@section('content')
    <div class="rounded-2xl border border-amber-300/80 bg-white p-8 text-center shadow-lg shadow-amber-500/5">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                <line x1="6" y1="2" x2="6" y2="4"/>
                <line x1="10" y1="2" x2="10" y2="4"/>
            </svg>
        </div>

        <h2 class="mt-4 text-lg font-bold text-slate-900">Kiosk Konsumsi Belum Diaktifkan</h2>

        <p class="mx-auto mt-1.5 max-w-md text-xs leading-relaxed text-slate-600 sm:text-sm">
            Panitia belum menetapkan PIN kiosk untuk acara ini. Kiosk pengambilan konsumsi hanya dapat dibuka setelah PIN diatur.
        </p>

        <div class="mx-auto mt-6 max-w-sm rounded-xl border border-slate-100 bg-slate-50/70 p-4 text-xs text-slate-600">
            Silakan hubungi <strong>Panitia Acara</strong> untuk mengaktifkan kiosk konsumsi.
        </div>
    </div>
@endsection
