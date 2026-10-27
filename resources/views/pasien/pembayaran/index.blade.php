<x-layouts.app title="Pembayaran">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Pembayaran</h1>
        <p class="text-sm text-slate-500 mt-1">Upload bukti pembayaran untuk tagihan pemeriksaan Anda</p>
    </div>

    @if($pembayarans->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-2xl border border-slate-100">
        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
            <i class="fas fa-receipt text-3xl text-slate-300"></i>
        </div>
        <h3 class="text-slate-500 font-semibold text-lg">Tidak Ada Tagihan</h3>
        <p class="text-slate-400 text-sm mt-1">Belum ada tagihan yang perlu dibayar saat ini.</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($pembayarans as $periksa)
        @php
            $statusConfig = [
                'belum_bayar' => [
                    'label' => 'Belum Bayar',
                    'class' => 'bg-red-100 text-red-700 border-red-200',
                    'icon'  => 'fa-circle-xmark',
                ],
                'menunggu_verifikasi' => [
                    'label' => 'Menunggu Verifikasi',
                    'class' => 'bg-amber-100 text-amber-700 border-amber-200',
                    'icon'  => 'fa-clock',
                ],
                'terverifikasi' => [
                    'label' => 'Lunas',
                    'class' => 'bg-green-100 text-green-700 border-green-200',
                    'icon'  => 'fa-circle-check',
                ],
            ];
            $st = $statusConfig[$periksa->status_pembayaran] ?? ['label' => $periksa->status_pembayaran, 'class' => 'bg-slate-100 text-slate-600', 'icon' => 'fa-circle'];
            $jadwal = optional($periksa->daftarPoli)->jadwalPeriksa;
        @endphp

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-5">

                {{-- Header --}}
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <p class="text-xs text-slate-400 mb-0.5">Periksa #{{ $periksa->id }}</p>
                        <h3 class="font-bold text-slate-800">
                            {{ $jadwal?->dokter?->nama ?? '-' }}
                        </h3>
                        <p class="text-sm text-slate-500">
                            {{ $jadwal?->dokter?->poli?->nama_poli ?? '-' }}
                            &bull;
                            {{ \Carbon\Carbon::parse($periksa->tgl_periksa)->format('d M Y') }}
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full border flex-shrink-0 {{ $st['class'] }}">
                        <i class="fas {{ $st['icon'] }} text-xs"></i> {{ $st['label'] }}
                    </span>
                </div>

                {{-- Daftar Obat Ringkas --}}
                @if($periksa->detailPeriksas->isNotEmpty())
                <div class="bg-slate-50 rounded-xl p-3 mb-4">
                    <p class="text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">Obat yang Diberikan</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($periksa->detailPeriksas as $detail)
                        <span class="inline-flex items-center gap-1 text-xs bg-white border border-slate-200 text-slate-600 px-2.5 py-1 rounded-lg">
                            <i class="fas fa-pills text-[10px] text-slate-400"></i>
                            {{ optional($detail->obat)->nama_obat ?? '-' }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Total + Catatan --}}
                <div class="flex items-center justify-between">
                    <div>
                        @if($periksa->catatan)
                        <p class="text-xs text-slate-400">Catatan: <span class="text-slate-600">{{ Str::limit($periksa->catatan, 60) }}</span></p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-400 mb-0.5">Total Tagihan</p>
                        <p class="text-2xl font-extrabold text-slate-800">
                            Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- Upload / Status Section --}}
            @if($periksa->status_pembayaran === 'belum_bayar')
            <div class="border-t border-dashed border-slate-200 p-5 bg-slate-50/50">
                <p class="text-sm font-semibold text-slate-700 mb-3">
                    <i class="fas fa-upload mr-2 text-indigo-400"></i>Upload Bukti Pembayaran
                </p>
                <form method="POST"
                    action="{{ route('pasien.pembayaran.upload', $periksa->id) }}"
                    enctype="multipart/form-data"
                    class="flex flex-col sm:flex-row gap-3 items-start">
                    @csrf
                    <div class="flex-1">
                        <input type="file" name="bukti_bayar" accept="image/jpg,image/jpeg,image/png"
                            class="file-input file-input-bordered file-input-sm w-full @error('bukti_bayar') file-input-error @enderror">
                        @error('bukti_bayar')
                        <p class="text-xs text-red-500 mt-1"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-slate-400 mt-1">Format: JPG/PNG, max 2MB</p>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm gap-2 flex-shrink-0">
                        <i class="fas fa-paper-plane"></i> Kirim Bukti
                    </button>
                </form>
            </div>

            @elseif($periksa->status_pembayaran === 'menunggu_verifikasi')
            <div class="border-t border-dashed border-amber-200 p-4 bg-amber-50/50 flex items-center gap-3">
                @if($periksa->bukti_bayar)
                <img src="{{ Storage::url($periksa->bukti_bayar) }}"
                    alt="Bukti Bayar"
                    class="w-16 h-16 rounded-lg object-cover border border-amber-200 cursor-pointer"
                    onclick="document.getElementById('modal-{{ $periksa->id }}').showModal()">
                @endif
                <div>
                    <p class="text-sm font-semibold text-amber-700">Bukti Dikirim — Menunggu Verifikasi</p>
                    <p class="text-xs text-amber-600 mt-0.5">Dokter/admin akan memverifikasi pembayaran Anda.</p>
                </div>
            </div>

            @elseif($periksa->status_pembayaran === 'terverifikasi')
            <div class="border-t border-dashed border-green-200 p-4 bg-green-50/50 flex items-center gap-3">
                @if($periksa->bukti_bayar)
                <img src="{{ Storage::url($periksa->bukti_bayar) }}"
                    alt="Bukti Bayar"
                    class="w-16 h-16 rounded-lg object-cover border border-green-200 cursor-pointer"
                    onclick="document.getElementById('modal-{{ $periksa->id }}').showModal()">
                @endif
                <div>
                    <p class="text-sm font-semibold text-green-700"><i class="fas fa-circle-check mr-1"></i>Pembayaran Lunas</p>
                    <p class="text-xs text-green-600 mt-0.5">Terima kasih! Pembayaran Anda sudah terverifikasi.</p>
                </div>
            </div>
            @endif

        </div>

        {{-- Modal Preview Bukti --}}
        @if($periksa->bukti_bayar)
        <dialog id="modal-{{ $periksa->id }}" class="modal">
            <div class="modal-box max-w-lg">
                <h3 class="font-bold text-lg mb-3">Bukti Pembayaran #{{ $periksa->id }}</h3>
                <img src="{{ Storage::url($periksa->bukti_bayar) }}" alt="Bukti Bayar" class="w-full rounded-xl">
                <div class="modal-action">
                    <form method="dialog"><button class="btn">Tutup</button></form>
                </div>
            </div>
        </dialog>
        @endif

        @endforeach
    </div>
    @endif

</x-layouts.app>
