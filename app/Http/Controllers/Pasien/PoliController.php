<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Periksa;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PoliController extends Controller
{
    public function get()
    {
        $user = Auth::user();
        $polis = Poli::all();
        $jadwal = JadwalPeriksa::with('dokter', 'dokter.poli')->get();
        $hasActiveQueue = DaftarPoli::where('id_pasien', $user->id)
            ->whereDoesntHave('periksas')
            ->exists();

        return view('pasien.daftar', [
            'user' => $user,
            'polis' => $polis,
            'jadwals' => $jadwal,
            'hasActiveQueue' => $hasActiveQueue,
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'id_poli' => 'required|exists:poli,id',
            'id_jadwal' => 'required|exists:jadwal_periksa,id',
            'keluhan' => 'nullable|string',
            'id_pasien' => 'required|exists:users,id|in:' . Auth::id(),
        ]);

        $existingActiveQueue = DaftarPoli::where('id_pasien', Auth::id())
            ->whereDoesntHave('periksas')
            ->exists();

        if ($existingActiveQueue) {
            return redirect()
                ->back()
                ->withInput()
                ->with('message', 'Anda masih memiliki antrian aktif. Selesaikan pemeriksaan terlebih dahulu.')
                ->with('type', 'error');
        }

        $jumlahSudahDaftar = DaftarPoli::where('id_jadwal', $request->id_jadwal)->count();

        DaftarPoli::create([
            'id_pasien' => $request->id_pasien,
            'id_jadwal' => $request->id_jadwal,
            'keluhan' => $request->keluhan,
            'no_antrian' => $jumlahSudahDaftar + 1,
        ]);

        return redirect()
            ->back()
            ->with('message', 'Berhasil Mendaftar ke Poli')
            ->with('type', 'success');
    }

    public function pay(Periksa $periksa)
    {
        $periksa->load('daftarPoli.jadwalPeriksa.dokter');

        if (!$periksa->daftarPoli || $periksa->daftarPoli->id_pasien !== Auth::id()) {
            abort(403);
        }

        if ($periksa->status_pembayaran !== 'belum_bayar') {
            return redirect()
                ->back()
                ->with('error', 'Pembayaran untuk pemeriksaan ini sudah diproses sebelumnya.');
        }

        $periksa->update([
            'status_pembayaran' => 'menunggu_verifikasi',
        ]);

        $dokterId = optional(optional($periksa->daftarPoli)->jadwalPeriksa)->id_dokter;

        if ($dokterId) {
            AppNotification::create([
                'user_id' => $dokterId,
                'title' => 'Pembayaran Masuk',
                'message' => 'Pasien ' . Auth::user()->nama . ' telah mengirim pembayaran untuk periksa #' . $periksa->id . '.',
                'type' => 'payment_created',
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Pembayaran berhasil dikirim. Menunggu verifikasi dokter.');
    }
}