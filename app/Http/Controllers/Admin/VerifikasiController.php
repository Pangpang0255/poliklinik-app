<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Periksa;

class VerifikasiController extends Controller
{
    public function index()
    {
        $tagihans = Periksa::with(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli'])
            ->whereIn('status_pembayaran', ['menunggu_verifikasi', 'terverifikasi'])
            ->latest('updated_at')
            ->get();

        return view('admin.verifikasi.index', compact('tagihans'));
    }

    public function konfirmasi(Periksa $periksa)
    {
        $periksa->load('daftarPoli.pasien', 'daftarPoli.jadwalPeriksa');

        if ($periksa->status_pembayaran !== 'menunggu_verifikasi') {
            return back()->with('error', 'Pembayaran belum dikirim pasien atau sudah diverifikasi.');
        }

        $periksa->update(['status_pembayaran' => 'terverifikasi']);

        $pasienId = optional($periksa->daftarPoli)->id_pasien;

        if ($pasienId) {
            // Cegah duplikasi notifikasi
            $alreadyNotified = AppNotification::where('user_id', $pasienId)
                ->where('type', 'payment_verified')
                ->where('message', 'LIKE', '%periksa #' . $periksa->id . '%')
                ->exists();

            if (!$alreadyNotified) {
                AppNotification::create([
                    'user_id' => $pasienId,
                    'title'   => 'Pembayaran Terverifikasi',
                    'message' => 'Pembayaran untuk periksa #' . $periksa->id . ' telah diverifikasi oleh admin. Status: Lunas.',
                    'type'    => 'payment_verified',
                ]);
            }
        }

        return back()->with('success', 'Pembayaran berhasil dikonfirmasi. Status diubah menjadi Lunas.');
    }
}
