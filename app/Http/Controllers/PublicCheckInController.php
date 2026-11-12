<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInByNameRequest;
use App\Http\Requests\ParticipantPublicSearchRequest;
use App\Models\Event;
use App\Models\Participant;
use App\Services\CheckInResult;
use App\Services\CheckInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Halaman check-in untuk peserta (tanpa login). Satu-satunya tempat check-in.
 * Peserta mencari namanya sendiri lalu memilih datanya dari hasil pencarian.
 */
class PublicCheckInController extends Controller
{
    private const MIN_NAME_SEARCH_LENGTH = 3;

    public function form(Event $event, string $slug, ParticipantPublicSearchRequest $request): View
    {
        $this->ensureSlug($event, $slug);

        $search = '';
        $candidates = collect();

        if ($event->allowsCheckIn()) {
            $search = trim((string) $request->validated('q'));

            if ($this->searchLength($search) >= self::MIN_NAME_SEARCH_LENGTH) {
                // Hanya cocokkan NAMA. Bisnis unit & jabatan ditampilkan untuk membantu peserta memilih.
                $candidates = $event->participants()
                    ->nameMatches($search)
                    ->doesntHave('attendance')
                    ->orderByRaw('LOWER(name) ASC')
                    ->limit(10)
                    ->get(['id', 'event_id', 'name', 'bisnis_unit', 'jabatan'])
                    ->map(fn (Participant $p): array => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'bisnis_unit' => $p->bisnis_unit,
                        'jabatan' => $p->jabatan,
                    ]);
            }
        }

        return view('public.check_in', [
            'event' => $event,
            'search' => $search,
            'searchTooShort' => $search !== '' && $this->searchLength($search) < self::MIN_NAME_SEARCH_LENGTH,
            'candidates' => $candidates,
            'lastResult' => session('public_check_in_result'),
            'canCheckIn' => $event->allowsCheckIn(),
        ]);
    }

    public function submitName(CheckInByNameRequest $request, Event $event, string $slug, CheckInService $service): RedirectResponse
    {
        $this->ensureSlug($event, $slug);

        $result = $service->checkInByName($event, (int) $request->validated('participant_id'));

        return $this->backWithResult($event, $result);
    }

    private function ensureSlug(Event $event, string $slug): void
    {
        abort_unless($event->slug === $slug, 404);
    }

    private function searchLength(string $term): int
    {
        return mb_strlen(trim(str_replace(['%', '_', '\\'], '', $term)));
    }

    private function backWithResult(Event $event, CheckInResult $result): RedirectResponse
    {
        return redirect()
            ->route('public.events.check-in', ['event' => $event, 'slug' => $event->slug])
            ->with('public_check_in_result', $result->toFlash());
    }
}
