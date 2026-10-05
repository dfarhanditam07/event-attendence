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
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
