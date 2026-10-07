<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckInByNameRequest extends FormRequest
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
            'participant_id' => ['required', 'integer', 'min:1'],
            'confirmation' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'participant_id' => 'nama peserta',
            'confirmation' => 'konfirmasi',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib dipilih.',
            'integer' => ':attribute tidak valid.',
            'min' => ':attribute tidak valid.',
            'accepted' => 'Anda harus mengonfirmasi bahwa ini adalah data Anda.',
        ];
    }
}
