<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\CheckInByNikRequest;
use App\Http\Requests\CheckInByQrPublicRequest;
use App\Http\Requests\ParticipantPublicSearchRequest;
use App\Models\Event;
use App\Models\User;
use App\Services\CheckInResult;
use App\Services\CheckInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicCheckInController extends Controller
{
    public function welcome(Event $event, string $slug): View
    {
        abort_unless($event->slug === $slug, 404);

        $event->loadCount(['participants', 'attendances']);
        $lastResult = session('public_check_in_result');

        return view('public.check_in_welcome', [
            'event' => $event,
            'lastResult' => $lastResult,
        ]);
    }

    public function form(Event $event, string $slug, string $mode, ParticipantPublicSearchRequest $request): View
    {
        abort_unless($event->slug === $slug, 404);
        abort_unless(in_array($mode, ['qr', 'nik', 'name'], true), 404);

        $event->loadCount(['participants', 'attendances']);

        $candidates = collect();
        $search = null;
        if ($mode === 'name') {
            $search = trim((string) $request->query('q'));
            if ($search !== '') {
                $candidates = $event->participants()
                    ->search($search)
                    ->doesntHave('attendance')
                    ->orderByRaw('LOWER(name) ASC')
                    ->limit(12)
                    ->get(['id', 'name', 'nik', 'organization']);
            }
        }

        return view('public.check_in', [
            'event' => $event,
            'mode' => $mode,
            'candidates' => $candidates,
            'search' => $search,
            'lastResult' => session('public_check_in_result'),
        ]);
    }

    public function submitQr(CheckInByQrPublicRequest $request, Event $event, string $slug, CheckInService $service): RedirectResponse
    {
        abort_unless($event->slug === $slug, 404);

        $staff = $this->resolveCheckInActor();
        $notes = ($n = trim((string) $request->input('notes'))) !== '' ? $n : null;

        $result = $service->checkInByQrToken(
            $event,
            (string) $request->input('qr_token'),
            $staff,
            $notes,
        );

        return $this->backWithResult($event, 'qr', $result);
    }

    public function submitNik(CheckInByNikRequest $request, Event $event, string $slug, CheckInService $service): RedirectResponse
    {
        abort_unless($event->slug === $slug, 404);

        $staff = $this->resolveCheckInActor();
        $notes = ($n = trim((string) $request->input('notes'))) !== '' ? $n : null;

        $result = $service->checkInByNik(
            $event,
            (string) $request->input('nik'),
            $staff,
            $notes,
        );

        return $this->backWithResult($event, 'nik', $result);
    }

    public function submitName(Request $request, Event $event, string $slug, CheckInService $service): RedirectResponse
    {
        abort_unless($event->slug === $slug, 404);

        $valid = $request->validate([
            'participant_id' => ['required', 'integer', 'min:1'],
            'confirmation' => ['accepted'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'required' => ':attribute wajib dipilih.',
            'integer' => ':attribute tidak valid.',
            'min' => ':attribute tidak valid.',
            'accepted' => 'Anda harus mengonfirmasi bahwa ini adalah data Anda.',
            'max' => ':attribute maksimal :max karakter.',
        ], [
            'participant_id' => 'nama peserta',
            'confirmation' => 'konfirmasi',
            'notes' => 'catatan',
        ]);

        $staff = $this->resolveCheckInActor();
        $notes = ($n = trim((string) ($valid['notes'] ?? ''))) !== '' ? $n : null;

        $result = $service->checkInManual(
            $event,
            (int) $valid['participant_id'],
            $staff,
            $notes,
        );

        return $this->backWithResult($event, 'name', $result);
    }

    /**
     * Untuk halaman publik (tanpa login), data "checked_in_by" mengarah ke user
     * sistem "auto-check-in". Kalau user ini belum ada, dibuat otomatis seeding.
     */
    private function resolveCheckInActor(): User
    {
        return User::firstOrCreate(
            ['email' => 'auto-checkin@system.local'],
            [
                'name' => 'Sistem Check-in Publik',
                'password' => Str::random(32),
                'role' => UserRole::Staff,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
    }

    private function backWithResult(Event $event, string $mode, CheckInResult $result): RedirectResponse
    {
        return redirect()
            ->route('public.events.check-in.mode', ['event' => $event, 'slug' => $event->slug, 'mode' => $mode])
            ->with('public_check_in_result', [
                'success' => $result->success,
                'participant_name' => $result->participant?->name,
                'participant_nik' => $result->participant?->nik,
                'participant_organization' => $result->participant?->organization,
                'attendance' => $result->attendance?->only(['checked_in_at', 'method']),
                'errors' => $result->errors,
            ]);
    }
}
