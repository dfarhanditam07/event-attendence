<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsumptionPickupRequest;
use App\Models\ConsumptionPickup;
use App\Models\Event;
use App\Services\ConsumptionPickupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Panel panitia untuk konsumsi kolektif per divisi.
 *
 * Alur: halaman utama (rekap per divisi) -> halaman serah-terima -> laporan audit trail.
 * Porsi hanya dihitung untuk anggota divisi yang sudah HADIR (check-in).
 * Penyerahan juga bisa dicatat lewat kiosk publik (lihat PublicConsumptionController).
 */
class ConsumptionController extends Controller
{
    public function index(Event $event, ConsumptionPickupService $service): View
    {
        Gate::authorize('view', $event);

        $divisions = $service->divisionSummaries($event);

        return view('consumptions.index', [
            'event' => $event,
            'divisions' => $divisions,
            'totals' => [
                'present' => (int) $divisions->sum('present'),
                'claimed' => (int) $divisions->sum('claimed'),
                'remaining' => (int) $divisions->sum('remaining'),
            ],
        ]);
    }

    public function create(Request $request, Event $event, ConsumptionPickupService $service): View|RedirectResponse
    {
        Gate::authorize('manageConsumption', $event);

        $division = $request->string('division')->trim()->value();

        if ($division === '') {
            return redirect()
                ->route('events.consumptions.index', $event)
                ->with('error', 'Pilih divisi terlebih dahulu dari daftar konsumsi.');
        }

        $participants = $service->eligibleParticipants($event, $division);

        if ($participants->isEmpty()) {
            return redirect()
                ->route('events.consumptions.index', $event)
                ->with('error', 'Tidak ada anggota divisi "'.$division.'" yang hadir dan belum diambil konsumsinya.');
        }

        return view('consumptions.create', [
            'event' => $event,
            'division' => $division,
            'participants' => $participants,
        ]);
    }

    public function store(ConsumptionPickupRequest $request, Event $event, ConsumptionPickupService $service): RedirectResponse
    {
        Gate::authorize('manageConsumption', $event);

        $result = $service->pickUp($event, $request->user(), $request->validated());

        if (! $result->success) {
            return back()
                ->withInput()
                ->withErrors($result->errors)
                ->with('error', (string) collect($result->errors)->flatten()->first());
        }

        return redirect()
            ->route('events.consumptions.report', $event)
            ->with('success', 'Konsumsi divisi "'.$result->pickup->division.'" sebanyak '.$result->pickup->portions_count.' porsi berhasil dicatat.');
    }

    public function report(Event $event): View
    {
        Gate::authorize('view', $event);

        $pickups = $event->consumptionPickups()
            ->with(['claims.participant:id,name,organization', 'handedOverBy:id,name'])
            ->withCount('claims')
            ->orderByDesc('picked_up_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('consumptions.report', [
            'event' => $event,
            'pickups' => $pickups,
        ]);
    }

    /**
     * Sajikan foto bukti penyerahan. Foto disimpan di disk privat, jadi hanya bisa
     * diakses panitia yang berhak melihat event ini.
     */
    public function photo(Event $event, ConsumptionPickup $consumptionPickup): StreamedResponse
    {
        Gate::authorize('view', $event);

        abort_unless($consumptionPickup->event_id === $event->id, 404);
        abort_unless($consumptionPickup->hasPhoto(), 404);

        $disk = Storage::disk(ConsumptionPickupService::EVIDENCE_DISK);

        abort_unless($disk->exists($consumptionPickup->photo_path), 404);

        return $disk->response($consumptionPickup->photo_path);
    }
}
