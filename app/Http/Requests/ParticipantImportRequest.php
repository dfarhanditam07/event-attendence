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
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'mimetypes:text/csv,text/plain,application/csv,text/comma-separated-values,text/anytext,application/octet-stream',
                'max:5120',
            ],
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
            'mimes' => ':attribute harus berformat CSV (.csv).',
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
