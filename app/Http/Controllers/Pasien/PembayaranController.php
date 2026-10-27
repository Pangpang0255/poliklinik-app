<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    public function index()
    {
        $pembayarans = Periksa::with(['daftarPoli.jadwalPeriksa.dokter.poli', 'detailPeriksas.obat'])
            ->whereHas('daftarPoli', function ($q) {
                $q->where('id_pasien', Auth::id());
            })
            ->whereIn('status_pembayaran', ['belum_bayar', 'menunggu_verifikasi', 'terverifikasi'])
            ->latest('tgl_periksa')
            ->get();

        return view('pasien.pembayaran.index', compact('pembayarans'));
    }

    public function upload(Request $request, Periksa $periksa)
    {
        // Pastikan periksa milik pasien yang login
        $periksa->load('daftarPoli.jadwalPeriksa.dokter');

        if (!$periksa->daftarPoli || $periksa->daftarPoli->id_pasien !== Auth::id()) {
            abort(403);
        }

        if ($periksa->status_pembayaran !== 'belum_bayar') {
            return back()->with('error', 'Pembayaran sudah dikirim atau sudah diverifikasi.');
        }

        $request->validate([
            'bukti_bayar' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'bukti_bayar.required' => 'Bukti pembayaran wajib diupload.',
            'bukti_bayar.image'    => 'File harus berupa gambar.',
            'bukti_bayar.mimes'    => 'Format gambar harus jpg, jpeg, atau png.',
            'bukti_bayar.max'      => 'Ukuran gambar maksimal 2MB.',
        ]);

        $path = $request->file('bukti_bayar')->store('bukti-bayar', 'public');

        $periksa->update([
            'bukti_bayar'       => $path,
            'status_pembayaran' => 'menunggu_verifikasi',
        ]);

        // Kirim notifikasi ke dokter
        $dokterId = optional(optional($periksa->daftarPoli)->jadwalPeriksa)->id_dokter;

        if ($dokterId) {
            // Cegah duplikasi notifikasi
            $alreadyNotified = \App\Models\AppNotification::where('user_id', $dokterId)
                ->where('type', 'payment_created')
                ->where('message', 'LIKE', '%periksa #' . $periksa->id . '%')
                ->exists();

            if (!$alreadyNotified) {
                AppNotification::create([
                    'user_id' => $dokterId,
                    'title'   => 'Bukti Pembayaran Masuk',
                    'message' => 'Pasien ' . Auth::user()->nama . ' telah mengupload bukti pembayaran untuk periksa #' . $periksa->id . '.',
                    'type'    => 'payment_created',
                ]);
            }
        }

        return back()->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi dokter.');
    }

    public function pay(Periksa $periksa)
    {
        return redirect()->route('pasien.pembayaran.index');
    }
}
