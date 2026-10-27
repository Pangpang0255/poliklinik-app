<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\Periksa;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    public function index()
    {
        $riwayats = DaftarPoli::with([
                'jadwalPeriksa.dokter.poli',
                'latestPeriksa',
            ])
            ->where('id_pasien', Auth::id())
            ->latest('id')
            ->get();

        return view('pasien.riwayat.index', compact('riwayats'));
    }

    public function show($id)
    {
        $daftar = DaftarPoli::with([
                'jadwalPeriksa.dokter.poli',
                'latestPeriksa.detailPeriksas.obat',
            ])
            ->where('id_pasien', Auth::id())
            ->findOrFail($id);

        $periksa = $daftar->latestPeriksa;

        if (!$periksa) {
            return redirect()
                ->route('pasien.riwayat.index')
                ->with('error', 'Data pemeriksaan belum tersedia.');
        }

        return view('pasien.riwayat.show', compact('daftar', 'periksa'));
    }
}
