<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Serah-terima konsumsi lewat kiosk publik (tanpa login).
 * Foto bukti wajib karena penyerahan tidak melekat pada akun panitia (lihat ConsumptionPickupService::pickUp()).
 */
class PublicConsumptionPickupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'division' => ['required', 'string', 'max:255'],
            'representative_name' => ['required', 'string', 'max:255'],
            'representative_contact' => ['nullable', 'string', 'max:32'],
            'handed_over_by_name' => ['required', 'string', 'max:255'],
            'photo' => ['required', 'image', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'division' => 'divisi',
            'representative_name' => 'nama perwakilan',
            'representative_contact' => 'no. HP perwakilan',
            'handed_over_by_name' => 'nama panitia penyerah',
            'photo' => 'foto bukti',
            'notes' => 'catatan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
            'photo.image' => 'foto bukti harus berupa gambar.',
        ];
    }
}
