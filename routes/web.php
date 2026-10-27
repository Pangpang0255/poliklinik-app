<?php


use App\Http\Controllers\Dokter\JadwalPeriksaController;
use App\Http\Controllers\Dokter\PeriksaPasienController;
use App\Http\Controllers\Dokter\RiwayatPasienController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Admin\PasienController;
use App\Http\Controllers\Admin\DokterController;
use App\Http\Controllers\Admin\AdminPoliController;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\Pasien\DashboardController;
use App\Http\Controllers\Pasien\PoliController;
use App\Http\Controllers\Pasien\PembayaranController;
use App\Http\Controllers\Pasien\RiwayatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ============================================================
// PROFILE (semua role yang sudah login)
// ============================================================
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/password', [ProfileController::class, 'showChangePassword'])->name('profile.password');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});


// ============================================================
// ADMIN
// ============================================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::resource('polis', AdminPoliController::class);
    Route::resource('dokter', DokterController::class);
    Route::resource('pasien', PasienController::class);
    Route::resource('obat', ObatController::class);

    // Export Excel
    Route::get('/dokter/export/excel', [DokterController::class, 'export'])->name('admin.dokter.export');
    Route::get('/pasien/export/excel', [PasienController::class, 'export'])->name('admin.pasien.export');
    Route::get('/obat/export/excel', [ObatController::class, 'export'])->name('admin.obat.export');

    // Verifikasi Pembayaran
    Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('admin.verifikasi.index');
    Route::post('/verifikasi/{periksa}', [VerifikasiController::class, 'konfirmasi'])->name('admin.verifikasi.konfirmasi');
});


// ============================================================
// DOKTER
// ============================================================
Route::middleware(['auth', 'role:dokter'])->prefix('dokter')->group(function () {
    Route::get('/dashboard', function () {
        return view('dokter.dashboard');
    })->name('dokter.dashboard');

    Route::resource('jadwal-periksa', JadwalPeriksaController::class);

    Route::get('/periksa-pasien', [PeriksaPasienController::class, 'index'])
        ->name('periksa-pasien.index');

    Route::post('/periksa-pasien', [PeriksaPasienController::class, 'store'])
        ->name('periksa-pasien.store');

    Route::post('/periksa-pasien/{periksa}/verify-payment', [PeriksaPasienController::class, 'verifyPayment'])
        ->name('periksa-pasien.verify-payment');

    Route::get('/periksa-pasien/{id}', [PeriksaPasienController::class, 'create'])
        ->name('periksa-pasien.create');

    Route::get('/riwayat-pasien', [RiwayatPasienController::class, 'index'])->name('riwayat-pasien.index');
    Route::get('/riwayat-pasien/{id}', [RiwayatPasienController::class, 'show'])->name('riwayat-pasien.show');

    // Export Excel
    Route::get('/jadwal-periksa/export/excel', [JadwalPeriksaController::class, 'export'])->name('dokter.jadwal.export');
    Route::get('/riwayat-pasien/export/excel', [RiwayatPasienController::class, 'export'])->name('dokter.riwayat.export');
});


// ============================================================
// PASIEN
// ============================================================
Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('pasien.dashboard');
    Route::get('/dashboard/queue-data', [DashboardController::class, 'queueData'])->name('pasien.dashboard.queue-data');

    Route::get('/daftar', [PoliController::class, 'get'])->name('pasien.daftar');
    Route::post('/daftar', [PoliController::class, 'submit'])->name('pasien.daftar.submit');

    // Pembayaran (Upload Bukti)
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pasien.pembayaran.index');
    Route::post('/pembayaran/{periksa}/upload', [PembayaranController::class, 'upload'])->name('pasien.pembayaran.upload');
    Route::match(['get', 'post'], '/pembayaran/{periksa}/pay', [PembayaranController::class, 'pay'])->name('pasien.pembayaran.pay');

    // Riwayat Pendaftaran
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('pasien.riwayat.index');
    Route::get('/riwayat/{id}', [RiwayatController::class, 'show'])->name('pasien.riwayat.show');
});
