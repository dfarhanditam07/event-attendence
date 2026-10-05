<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ParticipantImportService
{
    public const COLUMNS = ['name', 'nik', 'email', 'phone', 'organization'];

    /**
     * Hasil import.
     *
     * @param  array{success: int, failed: Collection<int, array{row: int, data: array<string, mixed>, errors: array<string, list<string>>}>}  $summary
     */
    public function __construct(public int $success = 0, public Collection $failed = new Collection)
    {
        $this->failed = new Collection;
    }

    /**
     * Import CSV/Excel (hanya CSV yang didukung; format Excel-friendly).
     *
     * - Baris 1 dianggap header (nama kolom, case-insensitive).
     * - Kolom yang dikenali: name, nama, nik, email, phone, no_hp, nomor_hp, organization, organisasi, instansi.
     * - NIK boleh kosong, tapi jika diisi harus unik dalam event.
     */
    public function import(Event $event, UploadedFile $file): self
    {
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            $this->failed->push([
                'row' => 0,
                'data' => [],
                'errors' => ['_file' => ['Gagal membuka file.']],
            ]);

            return $this;
        }

        $header = $this->readCsvRow($handle);

        if ($header === null) {
            fclose($handle);
            $this->failed->push([
                'row' => 0,
                'data' => [],
                'errors' => ['_file' => ['File kosong atau format tidak didukung.']],
            ]);

            return $this;
        }

        $columnMap = $this->mapColumns($header);

        $rowNumber = 1;
        while (($raw = $this->readCsvRow($handle)) !== null) {
            $rowNumber++;

            if ($this->isEmptyRow($raw)) {
                continue;
            }

            $data = $this->extractRowData($raw, $columnMap);
            $normalized = $this->normalizeData($data);

            $validator = Validator::make($normalized, [
                'name' => ['required', 'string', 'max:255'],
                'nik' => ['nullable', 'string', 'max:32'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:32'],
                'organization' => ['nullable', 'string', 'max:255'],
            ], [
                'required' => ':attribute wajib diisi.',
                'max' => ':attribute maksimal :max karakter.',
                'email' => ':attribute bukan email yang valid.',
            ], [
                'name' => 'nama',
                'nik' => 'NIK',
                'email' => 'email',
                'phone' => 'no. HP',
                'organization' => 'organisasi',
            ]);

            if ($validator->fails()) {
                $this->failed->push([
                    'row' => $rowNumber,
                    'data' => $normalized,
                    'errors' => $validator->errors()->toArray(),
                ]);

                continue;
            }

            $validated = $validator->validated();

            if (filled($validated['nik'])) {
                $nikExists = Participant::where('event_id', $event->id)
                    ->where('nik', $validated['nik'])
                    ->exists();

                if ($nikExists) {
                    $this->failed->push([
                        'row' => $rowNumber,
                        'data' => $normalized,
                        'errors' => ['nik' => ['NIK sudah terdaftar di event ini.']],
                    ]);

                    continue;
                }
            }

            try {
                Participant::create([
                    'event_id' => $event->id,
                    ...$validated,
                    'qr_token' => Str::random(40),
                ]);
                $this->success++;
            } catch (\Throwable $e) {
                $this->failed->push([
                    'row' => $rowNumber,
                    'data' => $normalized,
                    'errors' => ['_system' => ['Gagal menyimpan: '.$e->getMessage()]],
                ]);
            }
        }

        fclose($handle);

        return $this;
    }

    /**
     * @return array<int, string>|null
     */
    private function readCsvRow($handle): ?array
    {
        $row = fgetcsv($handle, 4096, ',', '"', '\\');

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

        $value = preg_replace('/^[\xEF\xBB\xBF]/u', '', $value) ?? $value;
        $value = trim($value);

        return $value;
    }

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
            'name' => ['name', 'nama', 'nama lengkap', 'fullname', 'full_name', 'peserta'],
            'nik' => ['nik', 'ktp', 'no_ktp', 'nomor_ktp'],
            'email' => ['email', 'e-mail', 'mail'],
            'phone' => ['phone', 'no_hp', 'nomor_hp', 'no_telp', 'telp', 'hp', 'whatsapp', 'wa'],
            'organization' => ['organization', 'organisasi', 'instansi', 'lembaga', 'perusahaan', 'company'],
        ];

        $map = [];
        foreach ($header as $index => $rawName) {
            $name = mb_strtolower(trim((string) $rawName));

            foreach ($aliases as $target => $variants) {
                if (in_array($name, $variants, true)) {
                    $map[$target] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * Ekstrak data baris berdasarkan mapping kolom.
     *
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
        $nik = $data['nik'];
        if ($nik !== '') {
            $cleanedNik = preg_replace('/\D/', '', $nik);
            $nik = filled($cleanedNik) ? $cleanedNik : $nik;
        }

        return [
            'name' => $data['name'],
            'nik' => $nik,
            'email' => $data['email'] === '' ? '' : mb_strtolower($data['email']),
            'phone' => $data['phone'],
            'organization' => $data['organization'],
        ];
    }
}
