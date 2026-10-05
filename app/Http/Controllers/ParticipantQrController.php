<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ParticipantQrController extends Controller
{
    /**
     * Menampilkan 1 kartu QR peserta (untuk di-print / di-share).
     */
    public function show(Event $event, Participant $participant)
    {
        Gate::authorize('view', $event);

        abort_unless($participant->event_id === $event->id, 404);

        $qrSvg = QrCode::format('svg')
            ->size(320)
            ->margin(2)
            ->errorCorrection('M')
            ->generate($participant->qr_token);

        return view('participants.qr_card', [
            'event' => $event,
            'participant' => $participant,
            'qrSvg' => $qrSvg,
        ]);
    }

    /**
     * Download gambar QR code peserta (PNG).
     */
    public function download(Event $event, Participant $participant)
    {
        Gate::authorize('view', $event);

        abort_unless($participant->event_id === $event->id, 404);

        // Simple QrCode mengembalikan binary PNG ketika format('png').
        $png = QrCode::format('png')
            ->size(600)
            ->margin(3)
            ->errorCorrection('H')
            ->generate($participant->qr_token);

        $filename = 'QR-'.Str::slug($participant->name).'-'.$participant->id.'.png';

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Halaman print bulk semua QR peserta event.
     */
    public function index(Event $event)
    {
        Gate::authorize('view', $event);

        $participants = $event->participants()
            ->orderByRaw('LOWER(name) ASC')
            ->get(['id', 'event_id', 'name', 'nik', 'organization', 'qr_token']);

        $qrs = $participants->map(function (Participant $p): array {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'nik' => $p->nik,
                'organization' => $p->organization,
                'qr' => QrCode::format('svg')
                    ->size(220)
                    ->margin(1)
                    ->errorCorrection('M')
                    ->generate($p->qr_token),
            ];
        });

        return view('participants.qr_bulk', [
            'event' => $event,
            'qrs' => $qrs,
        ]);
    }
}
