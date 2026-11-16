<?php

namespace App\Http\Controllers;

use App\Enums\MealSession;
use App\Http\Middleware\EnsureConsumptionKioskUnlocked;
use App\Http\Requests\PublicConsumptionPickupRequest;
use App\Http\Requests\PublicConsumptionPinRequest;
use App\Models\Event;
use App\Services\ConsumptionPickupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Kiosk pengambilan konsumsi di meja pembagian (tanpa login panitia).
 *
 * Dilindungi PIN per event (lihat Event::hasConsumptionKiosk()). Setelah PIN benar,
 * kiosk tetap terbuka sepanjang sesi browser supaya panitia bisa menyerahkan beberapa
 * divisi berturut-turut, dan bisa dikunci kembali secara manual.
 * Penyerahan konsumsi dijaga middleware EnsureConsumptionKioskUnlocked.
 * Setiap penyerahan melekat pada satu SESI MAKAN (makan siang / makan malam).
 */
class PublicConsumptionController extends Controller
{
    public function index(Request $request, Event $event, string $slug, ConsumptionPickupService $service): View
    {
        $this->ensureSlug($event, $slug);

        if (! $event->hasConsumptionKiosk()) {
            return view('public.consumption_unavailable', ['event' => $event]);
        }

        if (! $this->isUnlocked($event)) {
            return view('public.consumption_lock', [
                'event' => $event,
                'lastResult' => session('public_consumption_result'),
            ]);
        }

        $mealSession = $this->mealSessionFrom($request);

        return view('public.consumption_index', [
            'event' => $event,
            'mealSession' => $mealSession,
            'divisions' => $service->divisionSummaries($event, $mealSession),
            'lastResult' => session('public_consumption_result'),
        ]);
    }

    public function unlock(PublicConsumptionPinRequest $request, Event $event, string $slug): RedirectResponse
    {
        $this->ensureSlug($event, $slug);

        if (! $event->hasConsumptionKiosk() || ! $event->verifyConsumptionPin($request->string('pin')->value())) {
            return back()->withErrors(['pin' => 'PIN kiosk salah.'])->withInput();
        }

        $request->session()->put(EnsureConsumptionKioskUnlocked::SESSION_KEY, $event->id);

        return redirect()->route('public.events.consumption.index', ['event' => $event, 'slug' => $event->slug]);
    }

    public function lock(Request $request, Event $event, string $slug): RedirectResponse
    {
        $this->ensureSlug($event, $slug);

        $request->session()->forget(EnsureConsumptionKioskUnlocked::SESSION_KEY);

        return redirect()->route('public.events.consumption.index', ['event' => $event, 'slug' => $event->slug]);
    }

    public function create(Request $request, Event $event, string $slug, ConsumptionPickupService $service): View|RedirectResponse
    {
        $this->ensureSlug($event, $slug);

        $mealSession = $this->mealSessionFrom($request);
        $division = $request->string('division')->trim()->value();

        if ($division === '') {
            return $this->backToKiosk($event, $mealSession, 'Pilih divisi terlebih dahulu dari daftar konsumsi.');
        }

        $participants = $service->eligibleParticipants($event, $division, $mealSession);

        if ($participants->isEmpty()) {
            return $this->backToKiosk($event, $mealSession, 'Tidak ada anggota divisi "'.$division.'" yang hadir dan belum diambil konsumsi '.$mealSession->label().'nya.');
        }

        return view('public.consumption_create', [
            'event' => $event,
            'division' => $division,
            'mealSession' => $mealSession,
            'participants' => $participants,
        ]);
    }

    public function store(PublicConsumptionPickupRequest $request, Event $event, string $slug, ConsumptionPickupService $service): RedirectResponse
    {
        $this->ensureSlug($event, $slug);

        // Alur kiosk tidak punya akun panitia, jadi penyerah dicatat dari nama yang diisi.
        $result = $service->pickUp($event, null, $request->validated(), $request->file('photo'));

        if (! $result->success) {
            return back()->withInput()->withErrors($result->errors);
        }

        return redirect()
            ->route('public.events.consumption.index', ['event' => $event, 'slug' => $event->slug, 'meal_session' => $result->pickup->meal_session->value])
            ->with('public_consumption_result', [
                'success' => true,
                'division' => $result->pickup->division,
                'meal_session' => $result->pickup->meal_session->value,
                'meal_session_label' => $result->pickup->meal_session->label(),
                'portions_count' => $result->pickup->portions_count,
                'representative_name' => $result->pickup->representative_name,
                'handed_over_by_name' => $result->pickup->handed_over_by_name,
                'picked_up_at' => $result->pickup->picked_up_at->translatedFormat('d M Y, H:i').' WIB',
            ]);
    }

    private function isUnlocked(Event $event): bool
    {
        return session(EnsureConsumptionKioskUnlocked::SESSION_KEY) === $event->id;
    }

    private function backToKiosk(Event $event, MealSession $mealSession, ?string $error = null): RedirectResponse
    {
        $redirect = redirect()->route('public.events.consumption.index', [
            'event' => $event,
            'slug' => $event->slug,
            'meal_session' => $mealSession->value,
        ]);

        return $error === null ? $redirect : $redirect->with('error', $error);
    }

    /**
     * Sesi makan yang sedang dipilih dari query string. Bila tidak valid/kosong,
     * jatuh ke Makan Siang sebagai default.
     */
    private function mealSessionFrom(Request $request): MealSession
    {
        return MealSession::tryFrom($request->string('meal_session')->value()) ?? MealSession::Lunch;
    }

    private function ensureSlug(Event $event, string $slug): void
    {
        abort_unless($event->slug === $slug, 404);
    }
}
