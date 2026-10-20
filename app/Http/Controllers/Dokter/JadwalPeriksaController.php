<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalPeriksaController extends Controller
{
    private array $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function index()
    {
        $jadwals = JadwalPeriksa::where('id_dokter', Auth::id())
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai')
            ->get();

        return view('dokter.jadwal-periksa.index', compact('jadwals'));
    }

    public function create()
    {
        $hariList = $this->hariList;
        return view('dokter.jadwal-periksa.create', compact('hariList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'hari'        => 'required|in:' . implode(',', $this->hariList),
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        JadwalPeriksa::create([
            'id_dokter'   => Auth::id(),
            'hari'        => $request->hari,
            'jam_mulai'   => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'status'      => false,
        ]);

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('message', 'Jadwal Periksa berhasil ditambah')
            ->with('type', 'success');
    }

    public function edit(JadwalPeriksa $jadwal_periksa)
    {
        $this->authorizeOwner($jadwal_periksa);

        $hariList = $this->hariList;
        return view('dokter.jadwal-periksa.edit', compact('jadwal_periksa', 'hariList'));
    }

    public function update(Request $request, JadwalPeriksa $jadwal_periksa)
    {
        $this->authorizeOwner($jadwal_periksa);

        $request->validate([
            'hari'        => 'required|in:' . implode(',', $this->hariList),
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'status'      => 'required|boolean',
        ]);

        // Hanya satu jadwal yang boleh aktif per dokter
        if ($request->status) {
            JadwalPeriksa::where('id_dokter', Auth::id())
                ->where('id', '!=', $jadwal_periksa->id)
                ->update(['status' => false]);
        }

        $jadwal_periksa->update([
            'hari'        => $request->hari,
            'jam_mulai'   => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'status'      => $request->status,
        ]);

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('message', 'Jadwal Periksa berhasil diubah')
            ->with('type', 'success');
    }

    public function destroy(JadwalPeriksa $jadwal_periksa)
    {
        $this->authorizeOwner($jadwal_periksa);

        $jadwal_periksa->delete();

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('message', 'Jadwal Periksa berhasil dihapus')
            ->with('type', 'success');
    }

    /**
     * Pastikan jadwal ini milik dokter yang sedang login.
     */
    private function authorizeOwner(JadwalPeriksa $jadwal): void
    {
        abort_if($jadwal->id_dokter !== Auth::id(), 403);
    }
}