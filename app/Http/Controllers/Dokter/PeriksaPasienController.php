<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'latestPeriksa'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create($id)
    {
        $obats = Obat::all();
        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'obat_json' => 'required',
            'catatan' => 'nullable|string',
            'biaya_periksa' => 'required|integer',
        ]);

        $obatIds = json_decode($request->obat_json, true);

        $periksa = Periksa::create([
            'id_daftar_poli' => $request->id_daftar_poli,
            'tgl_periksa' => now(),
            'catatan' => $request->catatan,
            'biaya_periksa' => $request->biaya_periksa + 150000,
            'status_pembayaran' => 'belum_bayar',
        ]);

        if (is_array($obatIds)) {
            foreach ($obatIds as $idObat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $idObat,
                ]);
            }
        }

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data periksa berhasil disimpan.');
    }

    public function verifyPayment(Periksa $periksa)
    {
        $periksa->load('daftarPoli.jadwalPeriksa', 'daftarPoli.pasien');

        $isOwnedByDoctor = $periksa->daftarPoli
            && $periksa->daftarPoli->jadwalPeriksa
            && (int) $periksa->daftarPoli->jadwalPeriksa->id_dokter === (int) Auth::id();

        if (!$isOwnedByDoctor) {
            abort(403);
        }

        if ($periksa->status_pembayaran !== 'menunggu_verifikasi') {
            return redirect()
                ->back()
                ->with('error', 'Pembayaran belum dikirim pasien atau sudah diverifikasi.');
        }

        $periksa->update([
            'status_pembayaran' => 'terverifikasi',
        ]);

        $pasienId = optional($periksa->daftarPoli)->id_pasien;
        if ($pasienId) {
            $alreadyNotified = AppNotification::where('user_id', $pasienId)
                ->where('type', 'payment_verified')
                ->where('message', 'LIKE', '%periksa #' . $periksa->id . '%')
                ->exists();

            if (!$alreadyNotified) {
                AppNotification::create([
                    'user_id' => $pasienId,
                    'title' => 'Pembayaran Terverifikasi',
                    'message' => 'Pembayaran untuk periksa #' . $periksa->id . ' telah diverifikasi oleh dokter.',
                    'type' => 'payment_verified',
                ]);
            }
        }

        return redirect()
            ->back()
            ->with('success', 'Pembayaran pasien berhasil diverifikasi.');
    }
}