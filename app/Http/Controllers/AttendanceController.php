<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceMethod;
use App\Models\Event;
use App\Services\AttendanceExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Halaman panitia untuk MELIHAT kehadiran. Check-in dilakukan peserta sendiri
 * lewat halaman publik (lihat PublicCheckInController).
 */
class AttendanceController extends Controller
{
    public function index(Request $request, Event $event): View
    {
        Gate::authorize('view', $event);

        $query = $event->attendances()->with('participant');

        $method = $request->enum('method', AttendanceMethod::class);
        if ($method) {
            $query->where('method', $method->value);
        }

        $search = $request->string('q')->trim()->value();
        if ($search !== '') {
            $like = '%'.mb_strtolower($search).'%';
            $query->whereHas('participant', function ($q) use ($like): void {
                $q->where(function ($q) use ($like): void {
                    $q->whereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(nik) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(organization) LIKE ?', [$like]);
                });
            });
        }

        $attendances = $query
            ->orderByDesc('checked_in_at')
            ->paginate(20)
            ->withQueryString();

        $event->loadCount(['participants', 'attendances']);

        return view('attendances.index', [
            'event' => $event,
            'attendances' => $attendances,
            'methods' => AttendanceMethod::active(),
            'filters' => [
                'q' => $search,
                'method' => $method,
            ],
            'absentCount' => max(0, $event->participants_count - $event->attendances_count),
        ]);
    }

    public function export(Event $event, AttendanceExportService $service): StreamedResponse
    {
        Gate::authorize('view', $event);

        return $service->exportCsv($event);
    }
}
