<?php

use App\Enums\AttendanceMethod;
use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use App\Models\User;
use App\Services\CheckInService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// Pakai DB yang sudah dikonfigurasi, bungkus dengan TRANSACTION yg di-ROLLBACK
// supaya tidak meninggalkan data test.
$db = DB::connection();
$db->beginTransaction();

echo "== Evaluasi Fitur Scan QR (PostgreSQL + Transaction Rollback) ==\n\n";

$pass = 0;
$fail = 0;
$logs = [];
function log_result(string $line): void
{
    global $logs;
    $logs[] = $line;
}
function it(string $name, Closure $assert): void
{
    global $pass, $fail;
    try {
        $assert();
        echo "  ✅ $name\n";
        log_result("✅ $name");
        $pass++;
    } catch (Throwable $e) {
        echo "  ❌ $name\n";
        $msg = '     '.get_class($e).': '.$e->getMessage();
        echo "$msg\n";
        echo '     at '.basename($e->getFile()).':'.$e->getLine()."\n";
        log_result("❌ $name — ".$e->getMessage().' ('.basename($e->getFile()).':'.$e->getLine().')');
        $fail++;
    }
}

// Setup test data
$admin = User::forceCreate([
    'name' => 'Admin QR Test',
    'email' => 'admin_qr_uniq_'.bin2hex(random_bytes(4)).'@example.com',
    'password' => 'password123',
    'role' => UserRole::Admin,
    'is_active' => true,
    'email_verified_at' => now(),
]);
$staff = User::forceCreate([
    'name' => 'Panitia QR Test',
    'email' => 'staff_qr_uniq_'.bin2hex(random_bytes(4)).'@example.com',
    'password' => 'password123',
    'role' => UserRole::Staff,
    'is_active' => true,
    'email_verified_at' => now(),
]);

$eventDraft = Event::create([
    'name' => 'QR Test - Draft',
    'description' => '-',
    'starts_at' => '2026-12-01 09:00',
    'ends_at' => '2026-12-01 12:00',
    'location' => '-',
    'status' => EventStatus::Draft,
    'created_by' => $admin->id,
]);
$eventOpen = Event::create([
    'name' => 'QR Test - Open',
    'description' => '-',
    'starts_at' => '2026-12-01 09:00',
    'ends_at' => '2026-12-01 12:00',
    'location' => '-',
    'status' => EventStatus::Open,
    'created_by' => $admin->id,
]);
$p1 = Participant::create(['event_id' => $eventOpen->id, 'name' => 'QR Peserta 1', 'nik' => '1111111111111111']);
$p2 = Participant::create(['event_id' => $eventOpen->id, 'name' => 'QR Peserta 2', 'nik' => '2222222222222222']);
$pOther = Participant::create(['event_id' => $eventDraft->id, 'name' => 'QR Peserta Draft', 'nik' => '3333333333333333']);

$svc = app(CheckInService::class);

// ---- [A] Data & Token ----
it('(DATA) Participant baru otomatis punya qr_token unik 40 char', function () use ($p1, $p2) {
    if (! $p1->qr_token || strlen($p1->qr_token) < 32) {
        throw new Exception('qr1 pendek');
    }
    if (! $p2->qr_token || strlen($p2->qr_token) < 32) {
        throw new Exception('qr2 pendek');
    }
    if ($p1->qr_token === $p2->qr_token) {
        throw new Exception('qr1 == qr2 collision');
    }
});

it('(DB) Unique index (event_id,nik) aktif: NIK sama dalam 1 event ditolak', function () use ($eventOpen) {
    Participant::create(['event_id' => $eventOpen->id, 'name' => 'X', 'nik' => '9999']);
    $threw = false;
    try {
        Participant::create(['event_id' => $eventOpen->id, 'name' => 'Y', 'nik' => '9999']);
    } catch (UniqueConstraintViolationException $e) {
        $threw = true;
    }
    if (! $threw) {
        throw new Exception('Unique NIK per event tidak ditegakkan');
    }
});

it('(DB) Unique index participant_id di attendances aktif (anti duplikat DB-level)', function () use ($eventOpen, $p1, $staff) {
    // pakai INSERT RAW dua kali dengan DB transaction savepoint
    $conn = $p1->getConnection();
    try {
        $conn->beginTransaction();
        Attendance::create([
            'event_id' => $eventOpen->id,
            'participant_id' => $p1->id,
            'checked_in_at' => now(),
            'method' => AttendanceMethod::Qr,
            'checked_in_by' => $staff->id,
        ]);
        $ok = false;
        try {
            Attendance::create([
                'event_id' => $eventOpen->id,
                'participant_id' => $p1->id,
                'checked_in_at' => now(),
                'method' => AttendanceMethod::Qr,
                'checked_in_by' => $staff->id,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            $ok = true;
        }
        $conn->rollBack();
        if (! $ok) {
            throw new Exception('DB UNIQUE participant_id tidak mencegah 2 insert sama');
        }
    } catch (Throwable $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        throw $e;
    }
});

// ---- [B] Service Check-in QR ----
it('(SERVICE) Token VALID + Event OPEN = Berhasil, method = qr', function () use ($svc, $eventOpen, $p2, $staff) {
    $r = $svc->checkInByQrToken($eventOpen, $p2->qr_token, $staff);
    if (! $r->success) {
        throw new Exception('errors: '.json_encode($r->errors, JSON_UNESCAPED_UNICODE));
    }
    if ($r->attendance->method !== AttendanceMethod::Qr) {
        throw new Exception('method bukan QR');
    }
    if (is_null($r->attendance->checked_in_at)) {
        throw new Exception('checked_in_at null');
    }
    if ((int) $r->attendance->checked_in_by !== (int) $staff->id) {
        throw new Exception('checked_in_by salah');
    }
});

it('(SERVICE) Token sama di check-in-kan ulang = GAGAL duplicate dengan pesan "sudah absen"', function () use ($svc, $eventOpen, $p2, $staff) {
    $r = $svc->checkInByQrToken($eventOpen, $p2->qr_token, $staff);
    if ($r->success) {
        throw new Exception('Harusnya gagal duplicate');
    }
    $all = implode(' ', $r->errors['_duplicate'] ?? []);
    if (! str_contains($all, 'sudah absen')) {
        throw new Exception("Pesan salah: $all");
    }
});

it('(SERVICE) Token RANDOM (tidak ada) = GAGAL "QR tidak dikenali"', function () use ($svc, $eventOpen, $staff) {
    $r = $svc->checkInByQrToken($eventOpen, 'RANDOM-XX-'.bin2hex(random_bytes(10)), $staff);
    if ($r->success) {
        throw new Exception('Harus gagal');
    }
    $msg = implode(' ', $r->errors['_participant'] ?? []);
    if (! str_contains($msg, 'QR tidak dikenali')) {
        throw new Exception("Pesan salah: $msg");
    }
});

it('(SERVICE) Token milik EVENT LAIN (pindah event) = GAGAL', function () use ($svc, $eventOpen, $pOther, $staff) {
    $r = $svc->checkInByQrToken($eventOpen, $pOther->qr_token, $staff);
    if ($r->success) {
        throw new Exception('Seharusnya gagal, token beda event');
    }
});

it('(SERVICE) Event DRAFT / CLOSED token valid = DIBLOKIR, pesan menyesatkan soal status OPEN', function () use ($svc, $eventDraft, $pOther, $staff) {
    $r = $svc->checkInByQrToken($eventDraft, $pOther->qr_token, $staff);
    if ($r->success) {
        throw new Exception('Draft harus diblokir');
    }
    $msg = implode(' ', $r->errors['_event'] ?? []);
    if (! str_contains($msg, 'Dibuka')) {
        throw new Exception("Pesan event salah: $msg");
    }
});

// ---- [C] HTTP Layer ----
function make_request(string $verb, string $url, array $data = [], ?User $user = null): Response
{
    global $app;
    $req = Request::create($url, $verb, $data, [], [], [
        'HTTP_ACCEPT' => 'text/html',
    ]);
    $app->forgetInstance('request');
    if ($user) {
        $app['auth']->guard()->setUser($user);
        $req->setUserResolver(static fn () => $user);
    }
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    try {
        return $kernel->handle($req);
    } finally {
        // gc refs
    }
}

it('(HTTP PANITIA) GET /events/{e}/attendances/check-in TANPA login = REDIRECT ke /login', function () use ($eventOpen) {
    $resp = make_request('GET', route('events.attendances.check-in', $eventOpen));
    if ($resp->getStatusCode() !== 302) {
        throw new Exception('Status = '.$resp->getStatusCode());
    }
    if (! str_contains((string) $resp->headers->get('Location'), '/login')) {
        throw new Exception('Redirect ke = '.$resp->headers->get('Location'));
    }
});

it('(HTTP PANITIA) POST check-in/qr TOKEN VALID sbg panitia LOGGED-IN = 302 ke form + attendance terbentuk', function () use ($staff, $eventOpen, $p1) {
    // p1 belum punya attendance (cuma savepoint yg di-rollback tadi)
    $resp = make_request('POST', route('events.attendances.check-in.qr', $eventOpen), ['qr_token' => $p1->qr_token], $staff);
    $status = $resp->getStatusCode();
    if (! in_array($status, [302])) {
        throw new Exception("Status bukan 302 = $status");
    }
    $a = Attendance::where('participant_id', $p1->id)->first();
    if (! $a) {
        throw new Exception('Attendance tidak terbentuk');
    }
    if ($a->method !== AttendanceMethod::Qr) {
        throw new Exception('method salah');
    }
});

it('(HTTP PUBLIK) GET /e/{id}/{slug} (welcome) TANPA login = 200 OK', function () use ($eventOpen) {
    $resp = make_request('GET', route('public.events.check-in', ['event' => $eventOpen, 'slug' => $eventOpen->slug]));
    if ($resp->getStatusCode() !== 200) {
        throw new Exception('Status = '.$resp->getStatusCode());
    }
});

it('(HTTP PUBLIK) Mode check-in/{qr,nik,name} TANPA login = 200 OK', function () use ($eventOpen) {
    foreach (['qr', 'nik', 'name'] as $mode) {
        $url = route('public.events.check-in.mode', ['event' => $eventOpen, 'slug' => $eventOpen->slug, 'mode' => $mode]);
        $resp = make_request('GET', $url);
        if ($resp->getStatusCode() !== 200) {
            throw new Exception("mode $mode status = ".$resp->getStatusCode());
        }
    }
});

it('(HTTP PUBLIK) submit/qr dengan token BEDA EVENT = 302 redirect (bukan 500) + session error', function () use ($eventOpen, $pOther) {
    $app = $GLOBALS['app'];
    // inject array session driver
    $session = $app['session']->driver('array');
    $session->setId('sess_pub_test_'.uniqid());
    $session->start();
    $url = route('public.events.check-in.submit-qr', ['event' => $eventOpen, 'slug' => $eventOpen->slug]);
    $req = Request::create($url, 'POST', ['qr_token' => $pOther->qr_token]);
    $req->setLaravelSession($session);
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $resp = $kernel->handle($req);
    if (! in_array($resp->getStatusCode(), [200, 302])) {
        throw new Exception('Status = '.$resp->getStatusCode());
    }
    // cek errornya masuk session
    $err = $session->get('errors')?->getBag('default');
    $pubResult = $session->get('public_check_in_result');
    if (! $pubResult && ! $err) {
        throw new Exception('Tidak ada feedback hasil (session kosong)');
    }
    if ($pubResult && $pubResult['success'] !== false) {
        throw new Exception('Seharusnya gagal, token beda event');
    }
});

// ---- [D] CEK HALAMAN VIEW (ada/tidak nya scanner kamera) ----
echo "\n--- [PEMERIKSAAN KODE VIEW] ---\n";
$panitiaQr = file_get_contents(__DIR__.'/resources/views/attendances/check_in.blade.php');
$pubQr = file_get_contents(__DIR__.'/resources/views/public/check_in.blade.php');

$hasScanner = function (string $html): bool {
    return preg_match('/jsQR|html5-qrcode|instascan|quagga|getUserMedia|navigator\.mediaDevices|<video|<zxing|html5Qrcode/i', $html) === 1;
};

echo '    [?] Halaman panitia check_in.blade.php punya library scanner QR kamera (jsQR/html5-qrcode/etc): '.($hasScanner($panitiaQr) ? '✅ ADA' : '⚠️  TIDAK ADA (INPUT PASTE SAJA)')."\n";
log_result(($hasScanner($panitiaQr) ? '✅' : '⚠️ ').' Halaman panitia: scanner kamera = '.($hasScanner($panitiaQr) ? 'ada' : 'TIDAK ADA (hanya paste)'));

echo '    [?] Halaman publik check_in.blade.php punya library scanner QR kamera: '.($hasScanner($pubQr) ? '✅ ADA' : '⚠️  TIDAK ADA (INPUT PASTE SAJA)')."\n";
log_result(($hasScanner($pubQr) ? '✅' : '⚠️ ').' Halaman publik: scanner kamera = '.($hasScanner($pubQr) ? 'ada' : 'TIDAK ADA (hanya paste)'));

$generate = function (string $path): bool {
    $c = file_get_contents($path);

    return preg_match('/qrcode|QRCode|simple-qrcode|BaconQrCode|endroid\/qr|barcode|2fawps/i', $c) === 1;
};
echo '    [?] Ada fitur GENERATE QR CODE image (cetak kartu/undangan): '.($generate(__DIR__.'/composer.json') ? '✅ ADA dependensi' : '⚠️  TIDAK ADA dependensi QR generator (bacon-qr/simple-qrcode dll)')."\n";
log_result(($generate(__DIR__.'/composer.json') ? '✅' : '⚠️ ').' Dependensi QR code generator = '.($generate(__DIR__.'/composer.json') ? 'ada' : 'TIDAK ADA'));

echo "\n=== RINGKASAN ===\n";
echo "PASS  : $pass\n";
echo "FAIL  : $fail\n";
echo 'TOTAL : '.($pass + $fail)."\n";

echo "\n=== CLEANUP: ROLLBACK TRANSACTION ===\n";
$db->rollBack();
echo "Rollback OK.\n";

echo "\n=== LOGS DETAIL (untuk laporan) ===\n";
echo implode("\n", $logs)."\n";

exit($fail > 0 ? 1 : 0);
