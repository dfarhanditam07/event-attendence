<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParticipantImportRequest extends FormRequest
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
            // Cek ekstensi (bukan MIME): CSV dari Excel sering terbaca sebagai
            // application/vnd.ms-excel sehingga ditolak oleh aturan "mimes".
            'file' => ['required', 'file', 'extensions:csv,txt', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib dipilih.',
            'file' => ':attribute harus berupa file.',
            'extensions' => ':attribute harus berformat CSV (.csv).',
            'max' => ':attribute maksimal :max KB.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'file' => 'file CSV',
        ];
    }
}
