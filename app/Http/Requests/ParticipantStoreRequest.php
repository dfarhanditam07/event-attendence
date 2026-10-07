<?php

namespace App\Http\Requests;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParticipantStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('nik')) {
            $this->merge([
                'nik' => Participant::normalizeNik($this->input('nik')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $event = $this->route('event');
        $eventId = $event instanceof Event ? $event->id : (int) $event;

        return [
            'name' => ['required', 'string', 'max:255'],
            'nik' => [
                'nullable',
                'string',
                'max:32',
                Rule::unique('participants', 'nik')->where(fn ($q) => $q->where('event_id', $eventId)),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'organization' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'nik' => 'NIK',
            'email' => 'email',
            'phone' => 'no. HP',
            'organization' => 'organisasi',
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
            'email' => ':attribute bukan format email yang valid.',
            'nik.unique' => 'NIK ini sudah terdaftar di event ini.',
        ];
    }
}
