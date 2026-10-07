<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceExportService
{
    /**
     * Export daftar kehadiran (CSV).
     *
     * - Semua peserta ditampilkan (termasuk yang belum hadir), dengan kolom status hadir.
     * - Diurutkan berdasarkan nama peserta.
     */
    public function exportCsv(Event $event): StreamedResponse
    {
        $filename = 'kehadiran-'.$event->slug.'-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($event): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                throw new \RuntimeException('Gagal membuka output stream.');
            }

            // BOM UTF-8 supaya Excel bisa baca karakter Indonesia benar.
            fprintf($handle, \chr(0xEF).\chr(0xBB).\chr(0xBF));

            fputcsv($handle, [
                'No',
                'Nama',
                'NIK',
                'Email',
                'No. HP',
                'Organisasi',
                'Status Kehadiran',
                'Waktu Check-in',
                'Metode Check-in',
            ], ',', '"', '\\');

            $participants = $event->participants()
                ->with('attendance')
                ->orderByRaw('LOWER(name) ASC')
                ->lazyById(500);

            $i = 0;
            foreach ($participants as $p) {
                /** @var Participant $p */
                $i++;
                $attendance = $p->attendance;

                fputcsv($handle, [
                    $i,
                    $this->safe($p->name),
                    $this->safe($p->nik),
                    $this->safe($p->email),
                    $this->safe($p->phone),
                    $this->safe($p->organization),
                    $attendance ? 'Hadir' : 'Belum Hadir',
                    $attendance?->checked_in_at
                        ? Carbon::parse($attendance->checked_in_at)->translatedFormat('d M Y, H:i')
                        : '',
                    $attendance?->method->label() ?? '',
                ], ',', '"', '\\');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Cegah CSV/formula injection: sel yang diawali = + - @ dianggap rumus oleh Excel.
     */
    private function safe(?string $value): string
    {
        $value ??= '';

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
