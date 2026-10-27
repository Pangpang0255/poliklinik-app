<x-layouts.app title="Verifikasi Pembayaran">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Verifikasi Pembayaran</h1>
        <p class="text-sm text-slate-500 mt-1">Daftar tagihan yang menunggu konfirmasi pembayaran</p>
    </div>

    @if($tagihans->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-2xl border border-slate-100">
        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
            <i class="fas fa-clipboard-check text-3xl text-slate-300"></i>
        </div>
        <h3 class="text-slate-500 font-semibold text-lg">Tidak Ada Tagihan</h3>
        <p class="text-slate-400 text-sm mt-1">Semua pembayaran sudah diverifikasi atau belum ada yang masuk.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-xs text-slate-500 font-semibold">No</th>
                        <th class="text-xs text-slate-500 font-semibold">Pasien</th>
                        <th class="text-xs text-slate-500 font-semibold">Dokter / Poli</th>
                        <th class="text-xs text-slate-500 font-semibold">Tgl Periksa</th>
                        <th class="text-xs text-slate-500 font-semibold">Total</th>
                        <th class="text-xs text-slate-500 font-semibold">Bukti Bayar</th>
                        <th class="text-xs text-slate-500 font-semibold">Status</th>
                        <th class="text-xs text-slate-500 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tagihans as $i => $periksa)
                    @php
                        $statusConfig = [
                            'menunggu_verifikasi' => ['label' => 'Menunggu', 'class' => 'badge-warning'],
                            'terverifikasi'       => ['label' => 'Lunas',    'class' => 'badge-success'],
                        ];
                        $st = $statusConfig[$periksa->status_pembayaran] ?? ['label' => $periksa->status_pembayaran, 'class' => 'badge-ghost'];
                        $jadwal = optional($periksa->daftarPoli)->jadwalPeriksa;
                    @endphp
                    <tr class="hover:bg-slate-50 border-b border-slate-50">
                        <td class="text-slate-500 text-sm">{{ $i + 1 }}</td>

                        <td>
                            <p class="font-semibold text-slate-800 text-sm">{{ $periksa->daftarPoli?->pasien?->nama ?? '-' }}</p>
                            <p class="text-xs text-slate-400">No. RM: {{ $periksa->daftarPoli?->pasien?->no_rm ?? '-' }}</p>
                        </td>

                        <td>
                            <p class="text-sm text-slate-700">{{ $jadwal?->dokter?->nama ?? '-' }}</p>
                            <p class="text-xs text-slate-400">{{ $jadwal?->dokter?->poli?->nama_poli ?? '-' }}</p>
                        </td>

                        <td class="text-sm text-slate-600">
                            {{ $periksa->tgl_periksa ? \Carbon\Carbon::parse($periksa->tgl_periksa)->format('d M Y') : '-' }}
                        </td>

                        <td class="font-semibold text-slate-800">
                            Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                        </td>

                        <td>
                            @if($periksa->bukti_bayar)
                            <button onclick="document.getElementById('modal-{{ $periksa->id }}').showModal()"
                                class="btn btn-xs btn-outline border-indigo-300 text-indigo-600 gap-1">
                                <i class="fas fa-image text-xs"></i> Lihat
                            </button>
                            @else
                            <span class="text-xs text-slate-400 italic">Belum upload</span>
                            @endif
                        </td>

                        <td>
                            <span class="badge badge-sm {{ $st['class'] }} font-semibold">{{ $st['label'] }}</span>
                        </td>

                        <td>
                            @if($periksa->status_pembayaran === 'menunggu_verifikasi')
                            <form method="POST" action="{{ route('admin.verifikasi.konfirmasi', $periksa->id) }}"
                                onsubmit="return confirm('Konfirmasi pembayaran pasien ini sebagai LUNAS?')">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-success text-white gap-1">
                                    <i class="fas fa-check text-xs"></i> Konfirmasi
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-green-600 font-semibold"><i class="fas fa-circle-check mr-1"></i>Terverifikasi</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Modal Preview Bukti --}}
                    @if($periksa->bukti_bayar)
                    <dialog id="modal-{{ $periksa->id }}" class="modal">
                        <div class="modal-box max-w-lg">
                            <h3 class="font-bold text-lg mb-1">Bukti Pembayaran</h3>
                            <p class="text-sm text-slate-500 mb-3">Pasien: {{ $periksa->daftarPoli?->pasien?->nama ?? '-' }}</p>
                            <img src="{{ Storage::url($periksa->bukti_bayar) }}" alt="Bukti Bayar" class="w-full rounded-xl border">
                            <div class="modal-action">
                                <form method="dialog"><button class="btn btn-sm">Tutup</button></form>
                            </div>
                        </div>
                    </dialog>
                    @endif

                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</x-layouts.app>
