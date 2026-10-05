<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ParticipantQrController;
use App\Http\Controllers\PublicCheckInController;
use Illuminate\Support\Facades\Route;

// Halaman publik (TANPA login) — untuk peserta umum melakukan self check-in.
// Slug diulang sebagai segment kedua supaya SEO-friendly & mencegah enum ID guessing.
Route::prefix('e/{event}/{slug}')->name('public.events.')->group(function () {
    Route::get('/', [PublicCheckInController::class, 'welcome'])->name('check-in');
    Route::get('check-in/{mode}', [PublicCheckInController::class, 'form'])->name('check-in.mode');
    Route::post('submit/qr', [PublicCheckInController::class, 'submitQr'])->name('check-in.submit-qr');
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
        Route::get('participants/qr-codes', [ParticipantQrController::class, 'index'])
            ->name('participants.qr.index');
        Route::get('participants/{participant}/qr', [ParticipantQrController::class, 'show'])
            ->name('participants.qr.show');
        Route::get('participants/{participant}/qr.png', [ParticipantQrController::class, 'download'])
            ->name('participants.qr.download');

        Route::get('attendances/check-in', [AttendanceController::class, 'checkInForm'])
            ->name('attendances.check-in');
        Route::post('attendances/check-in/qr', [AttendanceController::class, 'checkInByQr'])
            ->name('attendances.check-in.qr');
        Route::post('attendances/check-in/nik', [AttendanceController::class, 'checkInByNik'])
            ->name('attendances.check-in.nik');
        Route::post('attendances/check-in/manual', [AttendanceController::class, 'checkInManual'])
            ->name('attendances.check-in.manual');

        Route::get('attendances', [AttendanceController::class, 'index'])
            ->name('attendances.index');
        Route::get('attendances/export', [AttendanceController::class, 'export'])
            ->name('attendances.export');
    });
});
