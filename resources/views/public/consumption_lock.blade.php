@extends('layouts.public')

@section('title', 'Buka Kiosk Konsumsi')
@section('subtitle', 'Kiosk Konsumsi Publik')
@section('footer_hint', 'Butuh bantuan? Silakan hubungi Panitia Acara di meja pembagian konsumsi.')

@section('content')
    @include('public._consumption_result', ['lastResult' => $lastResult])

    <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/40">
        <div class="text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-enc-50 text-enc-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <h2 class="mt-3 text-xl font-bold text-slate-900 sm:text-2xl">Kiosk Konsumsi</h2>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Masukkan PIN kiosk untuk mulai mencatat serah-terima konsumsi divisi.
            </p>
        </div>

        <form method="POST"
              action="{{ route('public.events.consumption.unlock', ['event' => $event, 'slug' => $event->slug]) }}"
              class="mt-6 space-y-5">
            @csrf
            <div>
                <label for="pin" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    PIN Kiosk
                </label>
                <input id="pin" name="pin" type="password" inputmode="numeric" required autofocus autocomplete="off"
                       placeholder="Masukkan PIN dari panitia"
                       class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-center font-mono text-lg tracking-[0.35em] text-slate-900 placeholder:text-sm placeholder:tracking-normal placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">
            </div>

            <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-enc-800 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 9.9-1"/>
                </svg>
                Buka Kiosk
            </button>
        </form>
    </div>
@endsection
