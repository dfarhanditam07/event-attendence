<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceMethod;
use App\Http\Requests\CheckInByNikRequest;
use App\Http\Requests\CheckInByQrRequest;
use App\Http\Requests\CheckInManualRequest;
use App\Models\Event;
use App\Services\AttendanceExportService;
use App\Services\CheckInResult;
use App\Services\CheckInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function checkInForm(Request $request, Event $event): View
    {
        Gate::authorize('view', $event);

        $recent = $event->attendances()
            ->with(['participant', 'checkedInBy'])
            ->orderByDesc('checked_in_at')
            ->limit(10)
            ->get();

        $event->loadCount(['participants', 'attendances']);

        return view('attendances.check_in', [
            'event' => $event,
            'recent' => $recent,
            'lastResult' => session('check_in_result'),
        ]);
    }

    public function checkInByQr(CheckInByQrRequest $request, Event $event, CheckInService $service): RedirectResponse
    {
        Gate::authorize('view', $event);

        $notes = ($n = $request->string('notes')->trim()->value()) !== '' ? $n : null;

        $result = $service->checkInByQrToken(
            $event,
            $request->string('qr_token')->value(),
            $request->user(),
            $notes,
        );

        return $this->redirectWithCheckInResult($event, $result);
    }

    public function checkInByNik(CheckInByNikRequest $request, Event $event, CheckInService $service): RedirectResponse
    {
        Gate::authorize('view', $event);

        $notes = ($n = $request->string('notes')->trim()->value()) !== '' ? $n : null;

        $result = $service->checkInByNik(
            $event,
            $request->string('nik')->value(),
            $request->user(),
            $notes,
        );

        return $this->redirectWithCheckInResult($event, $result);
    }

    public function checkInManual(CheckInManualRequest $request, Event $event, CheckInService $service): RedirectResponse
    {
        Gate::authorize('view', $event);

        $notes = ($n = $request->string('notes')->trim()->value()) !== '' ? $n : null;

        $result = $service->checkInManual(
            $event,
            (int) $request->input('participant_id'),
            $request->user(),
            $notes,
        );

        return $this->redirectWithCheckInResult($event, $result);
    }

    public function index(Request $request, Event $event): View
    {
        Gate::authorize('view', $event);

        $query = $event->attendances()
            ->with(['participant', 'checkedInBy']);

        $method = $request->enum('method', AttendanceMethod::class);
        if ($method) {
            $query->where('method', $method->value);
        }

        $search = $request->string('q')->trim()->value();
        if ($search !== '') {
            $like = '%'.mb_strtolower($search).'%';
            $query->whereHas('participant', function ($q) use ($like): void {
                $q->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(nik) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(organization) LIKE ?', [$like]);
            });
        }

        $attendances = $query
            ->orderByDesc('checked_in_at')
            ->paginate(20)
            ->withQueryString();

        $event->loadCount(['participants', 'attendances']);

        $absentCount = max(0, $event->participants_count - $event->attendances_count);

        return view('attendances.index', [
            'event' => $event,
            'attendances' => $attendances,
            'methods' => AttendanceMethod::cases(),
            'filters' => [
                'q' => $search,
                'method' => $method,
            ],
            'absentCount' => $absentCount,
        ]);
    }

    public function export(Event $event, AttendanceExportService $service)
    {
        Gate::authorize('view', $event);

        return $service->exportCsv($event);
    }

    private function redirectWithCheckInResult(Event $event, CheckInResult $result): RedirectResponse
    {
        return redirect()
            ->route('events.attendances.check-in', $event)
            ->with('check_in_result', [
                'success' => $result->success,
                'participant_name' => $result->participant?->name,
                'participant_nik' => $result->participant?->nik,
                'participant_organization' => $result->participant?->organization,
                'attendance' => $result->attendance?->only(['checked_in_at', 'method']),
                'errors' => $result->errors,
            ]);
    }
}
