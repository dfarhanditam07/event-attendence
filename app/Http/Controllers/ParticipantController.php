<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParticipantImportRequest;
use App\Http\Requests\ParticipantStoreRequest;
use App\Models\Event;
use App\Models\Participant;
use App\Services\ParticipantImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    public function index(Request $request, Event $event): View
    {
        Gate::authorize('view', $event);

        $query = $event->participants()
            ->with('attendance');

        $search = $request->string('q')->trim()->value();
        $status = $request->string('status')->trim()->value();

        if ($search !== '') {
            $query->search($search);
        }

        if ($status === 'present') {
            $query->whereHas('attendance');
        } elseif ($status === 'absent') {
            $query->whereDoesntHave('attendance');
        }

        $participants = $query
            ->orderByRaw('LOWER(name) ASC')
            ->paginate(20)
            ->withQueryString();

        return view('participants.index', [
            'event' => $event,
            'participants' => $participants,
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function create(Event $event): View
    {
        Gate::authorize('update', $event);

        return view('participants.create', [
            'event' => $event,
            'participant' => new Participant,
        ]);
    }

    public function store(ParticipantStoreRequest $request, Event $event): RedirectResponse
    {
        Gate::authorize('update', $event);

        $participant = $event->participants()->create($request->validated());

        return redirect()
            ->route('events.participants.index', $event)
            ->with('success', 'Peserta "'.$participant->name.'" berhasil ditambahkan.');
    }

    public function importCreate(Event $event): View
    {
        Gate::authorize('update', $event);

        return view('participants.import', [
            'event' => $event,
            'result' => session('import_result'),
        ]);
    }

    public function importStore(ParticipantImportRequest $request, Event $event, ParticipantImportService $service): RedirectResponse
    {
        Gate::authorize('update', $event);

        $result = $service->import($event, $request->file('file'));

        if ($result->success > 0 && $result->failed->isEmpty()) {
            $msg = "Berhasil mengimpor {$result->success} peserta.";
        } elseif ($result->success > 0) {
            $msg = "Berhasil mengimpor {$result->success} peserta. Gagal: {$result->failed->count()} baris.";
        } else {
            $msg = "Tidak ada peserta yang berhasil diimpor. Gagal: {$result->failed->count()} baris.";
        }

        return back()
            ->with('success', $msg)
            ->with('import_result', [
                'success' => $result->success,
                'failed' => $result->failed->all(),
            ]);
    }
}
