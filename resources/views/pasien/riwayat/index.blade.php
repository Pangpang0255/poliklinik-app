<x-layouts.app title="Riwayat Pendaftaran">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Riwayat Pendaftaran Poli</h1>
        <p class="text-sm text-slate-500 mt-1">Daftar semua riwayat pendaftaran pemeriksaan Anda</p>
    </div>

    @if($riwayats->isEmpty())
    <div class="flex flex-col items-center justify-center py-20 text-center bg-white rounded-2xl border border-slate-100">
        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
            <i class="fas fa-clipboard-list text-3xl text-slate-300"></i>
        </div>
        <h3 class="text-slate-500 font-semibold text-lg">Belum Ada Riwayat</h3>
        <p class="text-slate-400 text-sm mt-1">Anda belum pernah mendaftar ke poli manapun.</p>
        <a href="{{ route('pasien.daftar') }}" class="btn btn-primary btn-sm mt-4 gap-2">
            <i class="fas fa-plus"></i> Daftar Sekarang
        </a>
    </div>
    @else
    <div class="space-y-3">
        @foreach($riwayats as $daftar)
        @php
            $jadwal  = $daftar->jadwalPeriksa;
            $periksa = $daftar->latestPeriksa;
            $statusPemeriksaan = $periksa ? 'Sudah Diperiksa' : 'Menunggu Pemeriksaan';

            $statusClass = $periksa
                ? 'bg-green-100 text-green-700 border-green-200'
                : 'bg-amber-100 text-amber-700 border-amber-200';

            $statusIcon = $periksa ? 'fa-circle-check' : 'fa-clock';
        @endphp

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between gap-4">

                <div class="flex items-start gap-4 flex-1 min-w-0">

                    {{-- Nomor Antrian Badge --}}
                    <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-100 flex flex-col items-center justify-center">
                        <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">No.</span>
                        <span class="text-2xl font-bold text-indigo-600 leading-tight">{{ $daftar->no_antrian }}</span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="font-bold text-slate-800">
                                {{ $jadwal?->dokter?->nama ?? '-' }}
                            </h3>
                            <span class="text-xs text-slate-400">•</span>
                            <span class="text-sm text-slate-500">
                                {{ $jadwal?->dokter?->poli?->nama_poli ?? '-' }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-500">
                            <span><i class="fas fa-calendar-days mr-1.5 text-slate-400"></i>
                                {{ $jadwal->hari ?? '-' }},
                                {{ substr((string) ($jadwal->jam_mulai ?? ''), 0, 5) }}–{{ substr((string) ($jadwal->jam_selesai ?? ''), 0, 5) }}
                            </span>
                            @if($daftar->keluhan)
                            <span class="truncate max-w-xs"><i class="fas fa-notes-medical mr-1.5 text-slate-400"></i>{{ $daftar->keluhan }}</span>
                            @endif
                        </div>

                        @if($periksa)
                        <p class="text-xs text-slate-400 mt-1">
                            <i class="fas fa-stethoscope mr-1"></i>
                            Diperiksa: {{ \Carbon\Carbon::parse($periksa->tgl_periksa)->format('d M Y') }}
                        </p>
                        @endif
                    </div>
                </div>

                {{-- Status + Tombol --}}
                <div class="flex flex-col items-end gap-2 flex-shrink-0">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full border {{ $statusClass }}">
                        <i class="fas {{ $statusIcon }} text-xs"></i>
                        {{ $statusPemeriksaan }}
                    </span>

                    @if($periksa)
                    <a href="{{ route('pasien.riwayat.show', $daftar->id) }}"
                        class="btn btn-xs btn-outline border-indigo-300 text-indigo-600 hover:bg-indigo-50 gap-1">
                        <i class="fas fa-eye text-xs"></i>
                        Detail
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</x-layouts.app>
