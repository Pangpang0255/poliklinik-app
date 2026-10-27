<x-layouts.app title="Dashboard Pasien">

    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard Pasien</h1>
            <p class="text-sm text-slate-500">Informasi antrian per poli diperbarui otomatis secara real-time.</p>
        </div>

        <div id="activeQueueWrapper">
            @if($activeQueue)
            <div class="relative overflow-hidden rounded-2xl md:rounded-3xl text-white p-6 sm:p-7 md:p-8 shadow-xl" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 35%, #4f46e5 75%, #5850ec 100%);">
                <!-- Decorative background rings -->
                <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full border-[8px] border-white/10 pointer-events-none"></div>
                <div class="absolute right-24 -top-20 w-56 h-56 rounded-full border-[6px] border-white/10 pointer-events-none"></div>
                <div class="absolute right-48 bottom-0 w-32 h-32 rounded-full border-[4px] border-white/5 pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <!-- Left Info -->
                    <div class="space-y-3 sm:space-y-4">
                        <p class="text-[11px] sm:text-xs font-bold uppercase tracking-widest text-blue-200">ANTRIAN AKTIF ANDA</p>
                        <div>
                            <p class="text-[11px] text-blue-200/90 font-medium">Poliklinik</p>
                            <h2 id="activeQueuePoli" class="text-xl sm:text-2xl md:text-3xl font-extrabold text-white mt-0.5 tracking-tight">{{ $activeQueue['poli'] }}</h2>
                        </div>
                        <div>
                            <p class="text-[11px] text-blue-200/90 font-medium">Dokter</p>
                            <p id="activeQueueDokter" class="text-sm sm:text-base font-bold text-white mt-0.5">{{ $activeQueue['dokter'] }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] text-blue-200/90 font-medium">Jadwal Periksa</p>
                            <p id="activeQueueJadwal" class="text-xs sm:text-sm font-medium text-blue-100 mt-0.5">{{ $activeQueue['jadwal'] }}</p>
                        </div>
                    </div>

                    <!-- Right Cards -->
                    <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                        <!-- Card 1: Nomor Anda -->
                        <div class="w-28 sm:w-32 md:w-36 h-32 sm:h-36 rounded-2xl bg-white/15 backdrop-blur-md border border-white/25 shadow-inner p-3.5 sm:p-4 text-center flex flex-col items-center justify-between">
                            <span class="text-xs sm:text-sm font-medium text-white/90">Nomor Anda</span>
                            <span id="activeQueueNumber" class="text-4xl sm:text-5xl font-black text-white leading-none my-auto">{{ $activeQueue['no_antrian_pasien'] }}</span>
                            <span class="text-[10px] sm:text-xs opacity-0 select-none pointer-events-none" aria-hidden="true">Live Update</span>
                        </div>

                        <!-- Card 2: Sedang Dilayani -->
                        <div class="w-28 sm:w-32 md:w-36 h-32 sm:h-36 rounded-2xl bg-white shadow-xl p-3.5 sm:p-4 text-center flex flex-col items-center justify-between">
                            <span class="text-[10px] sm:text-xs font-bold text-slate-800 tracking-wider uppercase">SEDANG DILAYANI</span>
                            <span id="activeQueueCurrent" class="text-4xl sm:text-5xl font-black text-indigo-600 leading-none my-auto transition-transform duration-300">{{ $activeQueue['nomor_sedang_dilayani'] }}</span>
                            <div class="flex items-center gap-1.5">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
                                </span>
                                <span class="text-[10px] sm:text-xs font-semibold text-indigo-600">Live Update</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div id="activeQueueEmpty" class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-slate-600">
                Anda belum memiliki antrian aktif saat ini.
            </div>
            @endif
        </div>

        @if($pendingPayments->isNotEmpty())
        <div class="card bg-base-100 border border-slate-200 shadow-sm rounded-2xl">
            <div class="card-body p-0">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800">Status Pembayaran Pemeriksaan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">No</th>
                                <th class="px-6 py-4">Poli</th>
                                <th class="px-6 py-4">Dokter</th>
                                <th class="px-6 py-4">Biaya</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingPayments as $index => $payment)
                            <tr>
                                <td class="px-6 py-4 text-slate-600">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ optional(optional(optional(optional($payment->daftarPoli)->jadwalPeriksa)->dokter)->poli)->nama_poli ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ optional(optional(optional($payment->daftarPoli)->jadwalPeriksa)->dokter)->nama ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-700">Rp {{ number_format($payment->biaya_periksa, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    @if($payment->status_pembayaran === 'belum_bayar')
                                    <span class="badge bg-amber-100 text-amber-700 border-none">Belum Bayar</span>
                                    @elseif($payment->status_pembayaran === 'menunggu_verifikasi')
                                    <span class="badge bg-blue-100 text-blue-700 border-none">Menunggu Verifikasi</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($payment->status_pembayaran === 'belum_bayar')
                                    <a href="{{ route('pasien.pembayaran.index') }}" class="btn btn-sm bg-blue-600 hover:bg-blue-700 text-white border-none rounded-lg px-4 inline-flex items-center gap-1.5">
                                        <i class="fas fa-credit-card text-xs"></i>
                                        Bayar Sekarang
                                    </a>
                                    @else
                                    <span class="text-xs text-slate-500">Menunggu verifikasi</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <div class="card bg-base-100 border border-slate-200 shadow-sm rounded-2xl">
            <div class="card-body p-0">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800">Daftar Jadwal dan Antrian Per Poli</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full" id="scheduleQueueTable">
                        <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">No</th>
                                <th class="px-6 py-4">Poli</th>
                                <th class="px-6 py-4">Dokter</th>
                                <th class="px-6 py-4">Hari</th>
                                <th class="px-6 py-4">Jam Periksa</th>
                                <th class="px-6 py-4">Sedang Dilayani</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($scheduleQueues as $index => $queue)
                            <tr data-jadwal-id="{{ $queue['id_jadwal'] }}">
                                <td class="px-6 py-4 text-slate-600">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $queue['poli'] }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ $queue['dokter'] }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ $queue['hari'] }}</td>
                                <td class="px-6 py-4 text-slate-700">{{ $queue['jam'] }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center justify-center min-w-10 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 font-semibold js-current-queue">
                                        {{ $queue['nomor_sedang_dilayani'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 text-slate-400">
                                    Belum ada jadwal yang tersedia.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (() => {
            const queueDataEndpoint = @json(route('pasien.dashboard.queue-data'));
            const activeQueueWrapper = document.getElementById('activeQueueWrapper');

            const renderActiveQueue = (activeQueue) => {
                if (!activeQueue) {
                    activeQueueWrapper.innerHTML = `
                        <div id="activeQueueEmpty" class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-slate-600">
                            Anda belum memiliki antrian aktif saat ini.
                        </div>
                    `;
                    return;
                }

                const poliEl = document.getElementById('activeQueuePoli');
                const dokterEl = document.getElementById('activeQueueDokter');
                const jadwalEl = document.getElementById('activeQueueJadwal');
                const numberEl = document.getElementById('activeQueueNumber');
                const currentEl = document.getElementById('activeQueueCurrent');

                if (!poliEl || !dokterEl || !jadwalEl || !numberEl || !currentEl) {
                    activeQueueWrapper.innerHTML = `
                        <div class="relative overflow-hidden rounded-2xl md:rounded-3xl text-white p-6 sm:p-7 md:p-8 shadow-xl" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 35%, #4f46e5 75%, #5850ec 100%);">
                            <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full border-[8px] border-white/10 pointer-events-none"></div>
                            <div class="absolute right-24 -top-20 w-56 h-56 rounded-full border-[6px] border-white/10 pointer-events-none"></div>
                            <div class="absolute right-48 bottom-0 w-32 h-32 rounded-full border-[4px] border-white/5 pointer-events-none"></div>

                            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div class="space-y-3 sm:space-y-4">
                                    <p class="text-[11px] sm:text-xs font-bold uppercase tracking-widest text-blue-200">ANTRIAN AKTIF ANDA</p>
                                    <div>
                                        <p class="text-[11px] text-blue-200/90 font-medium">Poliklinik</p>
                                        <h2 id="activeQueuePoli" class="text-xl sm:text-2xl md:text-3xl font-extrabold text-white mt-0.5 tracking-tight"></h2>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-blue-200/90 font-medium">Dokter</p>
                                        <p id="activeQueueDokter" class="text-sm sm:text-base font-bold text-white mt-0.5"></p>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-blue-200/90 font-medium">Jadwal Periksa</p>
                                        <p id="activeQueueJadwal" class="text-xs sm:text-sm font-medium text-blue-100 mt-0.5"></p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                                    <div class="w-28 sm:w-32 md:w-36 h-32 sm:h-36 rounded-2xl bg-white/15 backdrop-blur-md border border-white/25 shadow-inner p-3.5 sm:p-4 text-center flex flex-col items-center justify-between">
                                        <span class="text-xs sm:text-sm font-medium text-white/90">Nomor Anda</span>
                                        <span id="activeQueueNumber" class="text-4xl sm:text-5xl font-black text-white leading-none my-auto"></span>
                                        <span class="text-[10px] sm:text-xs opacity-0 select-none pointer-events-none" aria-hidden="true">Live Update</span>
                                    </div>

                                    <div class="w-28 sm:w-32 md:w-36 h-32 sm:h-36 rounded-2xl bg-white shadow-xl p-3.5 sm:p-4 text-center flex flex-col items-center justify-between">
                                        <span class="text-[10px] sm:text-xs font-bold text-slate-800 tracking-wider uppercase">SEDANG DILAYANI</span>
                                        <span id="activeQueueCurrent" class="text-4xl sm:text-5xl font-black text-indigo-600 leading-none my-auto transition-transform duration-300"></span>
                                        <div class="flex items-center gap-1.5">
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
                                            </span>
                                            <span class="text-[10px] sm:text-xs font-semibold text-indigo-600">Live Update</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                document.getElementById('activeQueuePoli').textContent = activeQueue.poli;
                document.getElementById('activeQueueDokter').textContent = activeQueue.dokter;
                document.getElementById('activeQueueJadwal').textContent = activeQueue.jadwal;
                document.getElementById('activeQueueNumber').textContent = activeQueue.no_antrian_pasien;
                
                const curEl = document.getElementById('activeQueueCurrent');
                if (curEl) {
                    if (curEl.textContent !== String(activeQueue.nomor_sedang_dilayani)) {
                        curEl.textContent = activeQueue.nomor_sedang_dilayani;
                        curEl.classList.add('scale-110');
                        setTimeout(() => curEl.classList.remove('scale-110'), 300);
                    }
                }
            };

            const updateScheduleTable = (scheduleQueues) => {
                scheduleQueues.forEach((queue) => {
                    const row = document.querySelector(`[data-jadwal-id="${queue.id_jadwal}"]`);
                    if (!row) {
                        return;
                    }

                    const currentQueueEl = row.querySelector('.js-current-queue');
                    if (currentQueueEl) {
                        currentQueueEl.textContent = queue.nomor_sedang_dilayani;
                    }
                });
            };

            const pollQueueData = async () => {
                try {
                    const response = await fetch(queueDataEndpoint, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    renderActiveQueue(payload.active_queue || null);
                    updateScheduleTable(payload.schedule_queues || []);
                } catch (error) {
                    // Keep silent to avoid UI noise during temporary network failures.
                }
            };

            pollQueueData();
            setInterval(pollQueueData, 5000);
        })();
    </script>
    @endpush

</x-layouts.app>