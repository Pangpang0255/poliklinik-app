<x-layouts.app title="Detail Pemeriksaan">

    <div class="mb-6">
        <a href="{{ route('pasien.riwayat.index') }}"
            class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-700 mb-3">
            <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Detail Pemeriksaan</h1>
        <p class="text-sm text-slate-500 mt-1">
            Antrian #{{ $daftar->no_antrian }} — {{ $daftar->jadwalPeriksa?->dokter?->poli?->nama_poli ?? '-' }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Info Jadwal --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">

                <h2 class="font-bold text-slate-700 text-sm uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-calendar-days text-indigo-400"></i> Info Jadwal
                </h2>

                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-slate-400 text-xs mb-0.5">Poli</p>
                        <p class="font-semibold text-slate-800">{{ $daftar->jadwalPeriksa?->dokter?->poli?->nama_poli ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs mb-0.5">Dokter</p>
                        <p class="font-semibold text-slate-800">{{ $daftar->jadwalPeriksa?->dokter?->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs mb-0.5">Jadwal</p>
                        <p class="font-semibold text-slate-800">
                            {{ $daftar->jadwalPeriksa->hari ?? '-' }},
                            {{ substr((string) ($daftar->jadwalPeriksa->jam_mulai ?? ''), 0, 5) }}–{{ substr((string) ($daftar->jadwalPeriksa->jam_selesai ?? ''), 0, 5) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs mb-0.5">Nomor Antrian</p>
                        <p class="text-3xl font-extrabold text-indigo-600">{{ $daftar->no_antrian }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs mb-0.5">Tanggal Periksa</p>
                        <p class="font-semibold text-slate-800">
                            {{ \Carbon\Carbon::parse($periksa->tgl_periksa)->format('d M Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hasil Pemeriksaan --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Catatan Dokter --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h2 class="font-bold text-slate-700 text-sm uppercase tracking-wider flex items-center gap-2 mb-4">
                    <i class="fas fa-notes-medical text-indigo-400"></i> Catatan Dokter
                </h2>
                <p class="text-slate-700 text-sm leading-relaxed bg-slate-50 rounded-xl p-4">
                    {{ $periksa->catatan ?? 'Tidak ada catatan.' }}
                </p>
            </div>

            {{-- Daftar Obat --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h2 class="font-bold text-slate-700 text-sm uppercase tracking-wider flex items-center gap-2 mb-4">
                    <i class="fas fa-pills text-indigo-400"></i> Daftar Obat
                </h2>

                @if($periksa->detailPeriksas->isEmpty())
                <p class="text-slate-400 text-sm italic">Tidak ada obat yang diresepkan.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="table table-sm w-full">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="text-xs text-slate-500">No</th>
                                <th class="text-xs text-slate-500">Nama Obat</th>
                                <th class="text-xs text-slate-500">Kemasan</th>
                                <th class="text-xs text-slate-500 text-right">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($periksa->detailPeriksas as $i => $detail)
                            <tr class="hover:bg-slate-50">
                                <td class="text-slate-500">{{ $i + 1 }}</td>
                                <td class="font-medium text-slate-800">{{ optional($detail->obat)->nama_obat ?? '-' }}</td>
                                <td class="text-slate-500">{{ optional($detail->obat)->kemasan ?? '-' }}</td>
                                <td class="text-right text-slate-800">Rp {{ number_format(optional($detail->obat)->harga ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Total Biaya --}}
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-100 rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Total Biaya Pemeriksaan</p>
                        @php
                            $statusMap = [
                                'belum_bayar'         => ['label' => 'Belum Bayar',          'class' => 'bg-red-100 text-red-700 border-red-200'],
                                'menunggu_verifikasi' => ['label' => 'Menunggu Verifikasi',   'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
                                'terverifikasi'       => ['label' => 'Lunas',                 'class' => 'bg-green-100 text-green-700 border-green-200'],
                            ];
                            $st = $statusMap[$periksa->status_pembayaran] ?? ['label' => $periksa->status_pembayaran, 'class' => 'bg-slate-100 text-slate-600'];
                        @endphp
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold mt-1 px-2.5 py-1 rounded-full border {{ $st['class'] }}">
                            {{ $st['label'] }}
                        </span>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-extrabold text-indigo-700">
                            Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</x-layouts.app>
