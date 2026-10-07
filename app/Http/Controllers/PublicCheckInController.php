<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInByNameRequest;
use App\Http\Requests\CheckInByNikRequest;
use App\Http\Requests\ParticipantPublicSearchRequest;
use App\Models\Event;
use App\Models\Participant;
use App\Services\CheckInResult;
use App\Services\CheckInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Halaman check-in untuk peserta (tanpa login). Satu-satunya tempat check-in.
 * Metode: input NIK, atau cari nama lalu pilih.
 */
class PublicCheckInController extends Controller
{
    private const MIN_NAME_SEARCH_LENGTH = 3;

    public function welcome(Event $event, string $slug): View
    {
        $this->ensureSlug($event, $slug);

        return view('public.check_in_welcome', [
            'event' => $event,
            'canCheckIn' => $event->status->allowsCheckIn(),
        ]);
    }

    public function form(Event $event, string $slug, string $mode, ParticipantPublicSearchRequest $request): View
    {
        $this->ensureSlug($event, $slug);
        abort_unless(in_array($mode, ['nik', 'name'], true), 404);

        $search = '';
        $candidates = collect();

        if ($mode === 'name' && $event->status->allowsCheckIn()) {
            $search = trim((string) $request->validated('q'));

            if ($this->searchLength($search) >= self::MIN_NAME_SEARCH_LENGTH) {
                // Hanya cocokkan NAMA. NIK ditampilkan dalam bentuk tersamar, tidak pernah lengkap.
                $candidates = $event->participants()
                    ->nameMatches($search)
                    ->doesntHave('attendance')
                    ->orderByRaw('LOWER(name) ASC')
                    ->limit(10)
                    ->get(['id', 'event_id', 'name', 'nik', 'organization'])
                    ->map(fn (Participant $p): array => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'nik_masked' => $p->maskedNik(),
                        'organization' => $p->organization,
                    ]);
            }
        }

        return view('public.check_in', [
            'event' => $event,
            'mode' => $mode,
            'search' => $search,
            'searchTooShort' => $search !== '' && $this->searchLength($search) < self::MIN_NAME_SEARCH_LENGTH,
            'candidates' => $candidates,
            'lastResult' => session('public_check_in_result'),
            'canCheckIn' => $event->status->allowsCheckIn(),
        ]);
    }

    public function submitNik(CheckInByNikRequest $request, Event $event, string $slug, CheckInService $service): RedirectResponse
    {
        $this->ensureSlug($event, $slug);

        $result = $service->checkInByNik($event, $request->string('nik')->value());

        return $this->backWithResult($event, 'nik', $result);
    }

    public function submitName(CheckInByNameRequest $request, Event $event, string $slug, CheckInService $service): RedirectResponse
    {
        $this->ensureSlug($event, $slug);

        $result = $service->checkInByName($event, (int) $request->validated('participant_id'));

        return $this->backWithResult($event, 'name', $result);
    }

    private function ensureSlug(Event $event, string $slug): void
    {
        abort_unless($event->slug === $slug, 404);
    }

    private function searchLength(string $term): int
    {
        return mb_strlen(trim(str_replace(['%', '_', '\\'], '', $term)));
    }

    private function backWithResult(Event $event, string $mode, CheckInResult $result): RedirectResponse
    {
        return redirect()
            ->route('public.events.check-in.mode', ['event' => $event, 'slug' => $event->slug, 'mode' => $mode])
            ->with('public_check_in_result', $result->toFlash());
    }
}
