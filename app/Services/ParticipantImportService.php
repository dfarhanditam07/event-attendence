<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class ParticipantImportService
{
    public const COLUMNS = ['name', 'bisnis_unit', 'jabatan'];

    public const MAX_ROWS = 5000;

    public int $success = 0;

    /**
     * Baris yang gagal beserta penyebabnya.
     *
     * @var Collection<int, array{row: int, data: array<string, mixed>, errors: array<string, list<string>>}>
     */
    public Collection $failed;

    private string $delimiter = ',';

    public function __construct()
    {
        $this->failed = new Collection;
    }

    /**
     * Import peserta dari CSV.
     *
     * - Baris 1 adalah header (nama kolom, tidak peka huruf besar/kecil).
     * - Pemisah kolom dideteksi otomatis: koma, titik koma (default Excel Indonesia), atau tab.
     * - Nama wajib diisi; bisnis unit dan jabatan bersifat opsional.
     * - Baris yang gagal tidak menggagalkan baris lain; semuanya dilaporkan di $failed.
     */
    public function import(Event $event, UploadedFile $file): self
    {
        $handle = @fopen($file->getPathname(), 'rb');

        if ($handle === false) {
            return $this->fileError('Gagal membuka file.');
        }

        $this->delimiter = $this->detectDelimiter($handle);

        $header = $this->readCsvRow($handle);

        if ($header === null) {
            fclose($handle);

            return $this->fileError('File kosong atau format tidak didukung.');
        }

        $columnMap = $this->mapColumns($header);

        if (! isset($columnMap['name'])) {
            fclose($handle);

            return $this->fileError('Kolom "nama" (atau "name") tidak ditemukan di baris pertama. Pastikan baris pertama berisi header kolom.');
        }

        $rowNumber = 1;
        $processed = 0;

        while (($raw = $this->readCsvRow($handle)) !== null) {
            $rowNumber++;

            if ($this->isEmptyRow($raw)) {
                continue;
            }

            if (++$processed > self::MAX_ROWS) {
                $this->fileError('Maksimal '.self::MAX_ROWS.' baris per file. Baris setelah baris '.($rowNumber - 1).' tidak diproses; bagi file menjadi beberapa bagian.');
                break;
            }

            $data = $this->normalizeData($this->extractRowData($raw, $columnMap));

            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'bisnis_unit' => ['nullable', 'string', 'max:255'],
                'jabatan' => ['nullable', 'string', 'max:255'],
            ], [
                'required' => ':attribute wajib diisi.',
                'max' => ':attribute maksimal :max karakter.',
            ], [
                'name' => 'nama',
                'bisnis_unit' => 'bisnis unit',
                'jabatan' => 'jabatan',
            ]);

            if ($validator->fails()) {
                $this->fail($rowNumber, $data, $validator->errors()->toArray());

                continue;
            }

            // Kolom kosong disimpan sebagai NULL (bukan '').
            $values = array_map(fn ($v) => $v === '' ? null : $v, $validator->validated());

            try {
                Participant::create(['event_id' => $event->id, ...$values]);
                $this->success++;
            } catch (\Throwable $e) {
                report($e);

                $this->fail($rowNumber, $data, [
                    '_system' => ['Gagal menyimpan baris ini. Coba lagi, atau hubungi admin jika terus berulang.'],
                ]);
            }
        }

        fclose($handle);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, list<string>>  $errors
     */
    private function fail(int $row, array $data, array $errors): void
    {
        $this->failed->push(['row' => $row, 'data' => $data, 'errors' => $errors]);
    }

    private function fileError(string $message): self
    {
        $this->fail(0, [], ['_file' => [$message]]);

        return $this;
    }

    /**
     * Excel Indonesia menyimpan CSV dengan titik koma; sumber lain memakai koma atau tab.
     *
     * @param  resource  $handle
     */
    private function detectDelimiter($handle): string
    {
        $this->rewindPastBom($handle);
        $line = fgets($handle) ?: '';
        $this->rewindPastBom($handle);

        $counts = [
            ',' => substr_count($line, ','),
            ';' => substr_count($line, ';'),
            "\t" => substr_count($line, "\t"),
        ];

        arsort($counts);
        $best = array_key_first($counts);

        return $counts[$best] > 0 ? $best : ',';
    }

    /**
     * @param  resource  $handle
     */
    private function rewindPastBom($handle): void
    {
        rewind($handle);

        if (fread($handle, 3) !== "\xEF\xBB\xBF") {
            rewind($handle);
        }
    }

    /**
     * @param  resource  $handle
     * @return array<int, string>|null
     */
    private function readCsvRow($handle): ?array
    {
        $row = fgetcsv($handle, 0, $this->delimiter, '"', '');

        if ($row === false) {
            return null;
        }

        return array_map(fn ($cell) => $this->cleanCell($cell), $row);
    }

    private function cleanCell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = (string) $value;

        // CSV dari Excel versi Windows kadang bukan UTF-8.
        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
        }

        return trim($value);
    }

    /**
     * @param  array<int, string>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (filled($cell)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Petakan nama kolom di file CSV ke kolom yang dikenali.
     *
     * @param  array<int, string>  $header
     * @return array<string, int> map kolom => index
     */
    private function mapColumns(array $header): array
    {
        $aliases = [
            'name' => ['name', 'nama', 'nama lengkap', 'full name', 'peserta'],
            'bisnis_unit' => ['bisnis unit', 'business unit', 'unit bisnis', 'unit', 'divisi', 'division', 'departemen', 'department', 'organisasi', 'organization', 'instansi', 'lembaga', 'perusahaan', 'company'],
            'jabatan' => ['jabatan', 'posisi', 'position', 'jabatan posisi', 'role', 'title'],
        ];

        $map = [];

        foreach ($header as $index => $rawName) {
            $key = $this->normalizeKey((string) $rawName);

            foreach ($aliases as $target => $variants) {
                if (! isset($map[$target]) && in_array($key, array_map($this->normalizeKey(...), $variants), true)) {
                    $map[$target] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * "No. HP", "no_hp", dan "NO HP" dianggap sama.
     */
    private function normalizeKey(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', mb_strtolower(trim($value))) ?? '';
    }

    /**
     * @param  array<int, string>  $raw
     * @param  array<string, int>  $columnMap
     * @return array<string, string>
     */
    private function extractRowData(array $raw, array $columnMap): array
    {
        $data = [];

        foreach (self::COLUMNS as $col) {
            $data[$col] = isset($columnMap[$col]) ? ($raw[$columnMap[$col]] ?? '') : '';
        }

        return $data;
    }

    /**
     * @param  array<string, string>  $data
     * @return array<string, string>
     */
    private function normalizeData(array $data): array
    {
        $collapse = fn (string $value): string => preg_replace('/\s+/u', ' ', $value) ?? $value;

        return [
            'name' => $collapse($data['name']),
            'bisnis_unit' => $collapse($data['bisnis_unit']),
            'jabatan' => $collapse($data['jabatan']),
        ];
    }
}
