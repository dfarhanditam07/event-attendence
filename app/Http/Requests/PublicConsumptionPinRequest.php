<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Input PIN untuk membuka kiosk konsumsi publik.
 * Kecocokan PIN diverifikasi di controller lewat Event::verifyConsumptionPin().
 */
class PublicConsumptionPinRequest extends FormRequest
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
            'pin' => ['required', 'string', 'max:32'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'pin' => 'PIN kiosk',
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
        ];
    }
}
