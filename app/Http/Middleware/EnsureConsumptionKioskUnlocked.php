<?php

namespace App\Http\Middleware;

use App\Models\Event;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gerbang kiosk konsumsi publik: hanya boleh dilewati bila kiosk event ini aktif
 * (punya PIN) dan sudah dibuka pada sesi browser. Dipasang pada route yang menyerahkan
 * konsumsi, sehingga pengecekan berjalan SEBELUM validasi form.
 */
class EnsureConsumptionKioskUnlocked
{
    /**
     * Penanda event yang kiosknya sedang terbuka pada sesi ini.
     */
    public const SESSION_KEY = 'public_consumption_event_id';

    public function handle(Request $request, Closure $next): Response
    {
        $event = $request->route('event');

        if (! $event instanceof Event || $event->slug !== $request->route('slug')) {
            abort(404);
        }

        if (! $event->hasConsumptionKiosk() || $request->session()->get(self::SESSION_KEY) !== $event->id) {
            return redirect()->route('public.events.consumption.index', [
                'event' => $event,
                'slug' => $event->slug,
            ]);
        }

        return $next($request);
    }
}
