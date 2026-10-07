@php($closed = $event->effectiveStatus() === \App\Enums\EventStatus::Closed)
<div class="rounded-2xl border border-amber-300/80 bg-white p-8 text-center shadow-lg shadow-amber-500/5">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
        @if($closed)
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        @endif
    </div>

    <h2 class="mt-4 text-lg font-bold text-slate-900">
        @if($closed)
            Sesi Presensi Telah Ditutup
        @else
            Sesi Presensi Belum Dibuka
        @endif
    </h2>

    <p class="mt-1.5 text-xs text-slate-600 sm:text-sm max-w-md mx-auto leading-relaxed">
        @if($closed)
            Waktu check-in untuk acara ini telah berakhir atau ditutup oleh panitia penyelenggara.
        @else
            Pendaftaran absensi mandiri untuk acara ini belum diaktifkan oleh panitia acara.
        @endif
    </p>

    <div class="mt-6 rounded-xl border border-slate-100 bg-slate-50/70 p-4 text-xs text-slate-600 max-w-sm mx-auto">
        Silakan hubungi <strong>Panitia Acara</strong> di meja registrasi depan ruangan untuk bantuan presensi manual.
    </div>
</div>
