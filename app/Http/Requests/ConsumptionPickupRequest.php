<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsumptionPickupRequest extends FormRequest
{
    /**
     * Otorisasi ditangani oleh Gate::authorize('manageConsumption') di controller.
     */
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
        ];
    }
}
