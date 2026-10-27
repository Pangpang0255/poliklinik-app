<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Periksa;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $queueSnapshot = $this->buildQueueSnapshot();
        $pendingPayments = $this->getPendingPayments();

        return view('pasien.dashboard', [
            'activeQueue' => $queueSnapshot['active_queue'],
            'scheduleQueues' => $queueSnapshot['schedule_queues'],
            'pendingPayments' => $pendingPayments,
        ]);
    }

    public function queueData()
    {
        $queueSnapshot = $this->buildQueueSnapshot();

        return response()->json($queueSnapshot);
    }

    private function buildQueueSnapshot(): array
    {
        $userId = Auth::id();

        $activeRegistration = DaftarPoli::with('jadwalPeriksa.dokter.poli')
            ->where('id_pasien', $userId)
            ->whereDoesntHave('periksas')
            ->latest('id')
            ->first();

        $jadwals = JadwalPeriksa::with('dokter.poli')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $scheduleQueues = $jadwals->map(function ($jadwal) {
            $servedQueue = DaftarPoli::where('id_jadwal', $jadwal->id)
                ->whereHas('periksas')
                ->max('no_antrian');

            $nextQueue = DaftarPoli::where('id_jadwal', $jadwal->id)
                ->whereDoesntHave('periksas')
                ->min('no_antrian');

            $currentQueue = $nextQueue ?? $servedQueue ?? 0;

            return [
                'id_jadwal' => $jadwal->id,
                'poli' => optional(optional($jadwal->dokter)->poli)->nama_poli ?? '-',
                'dokter' => optional($jadwal->dokter)->nama ?? '-',
                'hari' => $jadwal->hari,
                'jam' => substr((string) $jadwal->jam_mulai, 0, 5) . ' - ' . substr((string) $jadwal->jam_selesai, 0, 5),
                'nomor_sedang_dilayani' => (int) $currentQueue,
            ];
        })->values()->all();

        $activeQueue = null;

        if ($activeRegistration) {
            $scheduleQueue = collect($scheduleQueues)
                ->firstWhere('id_jadwal', $activeRegistration->id_jadwal);

            $jadwal = $activeRegistration->jadwalPeriksa;
            $activeQueue = [
                'id_daftar' => $activeRegistration->id,
                'poli' => optional(optional(optional($jadwal)->dokter)->poli)->nama_poli ?? '-',
                'dokter' => optional(optional($jadwal)->dokter)->nama ?? '-',
                'jadwal' => ($jadwal->hari ?? '-') . ' (' . substr((string) ($jadwal->jam_mulai ?? ''), 0, 5) . ' - ' . substr((string) ($jadwal->jam_selesai ?? ''), 0, 5) . ')',
                'no_antrian_pasien' => (int) $activeRegistration->no_antrian,
                'nomor_sedang_dilayani' => (int) ($scheduleQueue['nomor_sedang_dilayani'] ?? 0),
            ];
        }

        return [
            'active_queue' => $activeQueue,
            'schedule_queues' => $scheduleQueues,
        ];
    }

    private function getPendingPayments()
    {
        return Periksa::with('daftarPoli.jadwalPeriksa.dokter.poli')
            ->whereHas('daftarPoli', function ($query) {
                $query->where('id_pasien', Auth::id());
            })
            ->whereIn('status_pembayaran', ['belum_bayar', 'menunggu_verifikasi'])
            ->latest('tgl_periksa')
            ->get();
    }
}
