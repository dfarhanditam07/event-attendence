@php($closed = $event->effectiveStatus() === \App\Enums\EventStatus::Closed)
<div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center text-sm text-amber-900">
    @if($closed)
        Check-in sudah ditutup. Hubungi panitia jika Anda belum sempat check-in.
    @else
        Check-in belum dibuka untuk acara ini. Hubungi panitia jika Anda yakin ini sebuah kesalahan.
    @endif
</div>
