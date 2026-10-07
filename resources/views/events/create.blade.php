@extends('layouts.app')

@section('title', 'Buat Event Baru')

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
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Buat Event Baru</h1>
        <p class="mt-1 text-sm text-slate-500">Lengkapi formulir di bawah ini untuk membuka acara dan sesi presensi.</p>
    </div>

    <form method="POST" action="{{ route('events.store') }}"
          class="max-w-3xl rounded-xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-xs">
        @include('events._form', ['cancelUrl' => route('events.index')])
    </form>
@endsection
