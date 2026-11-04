<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Event::class);

        $events = Event::query()
            ->withCount(['participants', 'attendances'])
            ->when(
                $request->string('q')->trim()->value(),
                fn ($query, $term) => $query->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($term).'%']),
            )
            ->when(
                $request->enum('status', EventStatus::class),
                fn ($query, $status) => $query->withEffectiveStatus($status),
            )
            ->orderByDesc('starts_at')
            ->paginate(10)
            ->withQueryString();

        return view('events.index', [
            'events' => $events,
            'statuses' => EventStatus::cases(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Event::class);

        return view('events.create', [
            'event' => new Event(['status' => EventStatus::Draft]),
            'statuses' => EventStatus::cases(),
        ]);
    }

    public function store(EventRequest $request): RedirectResponse
    {
        Gate::authorize('create', Event::class);

        $event = Event::create([
            ...$this->attributesFrom($request),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Event berhasil dibuat.');
    }

    public function show(Event $event): View
    {
        Gate::authorize('view', $event);

        $event->loadCount(['participants', 'attendances']);

        return view('events.show', ['event' => $event]);
    }

    public function edit(Event $event): View
    {
        Gate::authorize('update', $event);

        return view('events.edit', [
            'event' => $event,
            'statuses' => EventStatus::cases(),
        ]);
    }

    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        Gate::authorize('update', $event);

        // Slug sengaja tidak ikut diubah supaya URL/QR yang sudah beredar tetap valid.
        $event->update($this->attributesFrom($request));

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'Event berhasil diperbarui.');
    }

    /**
     * Atribut event dari request, dengan penanganan khusus PIN kiosk konsumsi.
     *
     * PIN tersimpan ter-hash sehingga tidak bisa ditampilkan ulang di form. Karena itu:
     * - Kolom PIN dibiarkan kosong saat edit -> PIN lama tetap dipertahankan.
     * - Checkbox "hapus PIN" dicentang -> kiosk dinonaktifkan (PIN dihapus).
     *
     * @return array<string, mixed>
     */
    private function attributesFrom(EventRequest $request): array
    {
        $data = $request->validated();
        $removePin = (bool) ($data['remove_consumption_pin'] ?? false);
        $pin = $data['consumption_pin'] ?? null;

        unset($data['remove_consumption_pin']);

        if ($removePin) {
            $data['consumption_pin'] = null;
        } elseif (blank($pin)) {
            unset($data['consumption_pin']);
        }

        return $data;
    }

    public function destroy(Event $event): RedirectResponse
    {
        Gate::authorize('delete', $event);

        // Soft delete: data peserta dan absensi tetap tersimpan.
        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('success', 'Event berhasil dihapus.');
    }
}
