<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PublicCheckInController;
use Illuminate\Support\Facades\Route;

// Halaman publik (TANPA login) — satu-satunya tempat check-in, dipakai peserta sendiri.
// Metode: input NIK atau cari nama. Slug diulang sebagai segment kedua supaya ID tidak bisa ditebak.
Route::prefix('e/{event}/{slug}')
    ->name('public.events.')
    ->middleware('throttle:public-check-in')
    ->group(function () {
        Route::get('/', [PublicCheckInController::class, 'welcome'])->name('check-in');
        Route::get('check-in/{mode}', [PublicCheckInController::class, 'form'])
            ->whereIn('mode', ['nik', 'name'])
            ->name('check-in.mode');
        Route::post('submit/nik', [PublicCheckInController::class, 'submitNik'])->name('check-in.submit-nik');
        Route::post('submit/name', [PublicCheckInController::class, 'submitName'])->name('check-in.submit-name');
    });

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/', fn () => redirect()->route('events.index'))->name('home');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::resource('events', EventController::class);

    Route::prefix('events/{event}')->name('events.')->group(function () {
        Route::get('participants', [ParticipantController::class, 'index'])
            ->name('participants.index');
        Route::get('participants/create', [ParticipantController::class, 'create'])
            ->name('participants.create');
        Route::post('participants', [ParticipantController::class, 'store'])
            ->name('participants.store');
        Route::get('participants/import', [ParticipantController::class, 'importCreate'])
            ->name('participants.import');
        Route::post('participants/import', [ParticipantController::class, 'importStore'])
            ->name('participants.import.store');

        // Panitia hanya MELIHAT kehadiran; check-in dilakukan peserta di halaman publik.
        Route::get('attendances', [AttendanceController::class, 'index'])
            ->name('attendances.index');
        Route::get('attendances/export', [AttendanceController::class, 'export'])
            ->name('attendances.export');
    });
});
