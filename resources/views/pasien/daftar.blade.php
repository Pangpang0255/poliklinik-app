<x-layouts.app title="Daftar Poli">

    <div class="max-w-4xl mx-auto">

    @if(session('message'))
    <div class="alert {{ session('type') === 'success' ? 'alert-success' : 'alert-error' }} mb-4 rounded-xl shadow-sm">
        <i class="fas {{ session('type') === 'success' ? 'fa-check-circle' : 'fa-circle-xmark' }}"></i>
        <span>{{ session('message') }}</span>
    </div>
    @endif

    <div class="card bg-base-100 shadow-sm rounded-xl border border-slate-200 mb-6">
        <div class="card-body p-6 md:p-8">
            <h2 class="text-2xl font-bold text-slate-800 text-center mb-6">
                <span class="mr-2">🏥</span>Pendaftaran Poli
            </h2>

            @if($hasActiveQueue)
            <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 px-4 py-3 text-sm">
                Anda masih memiliki antrian aktif. Anda bisa mendaftar kembali setelah pemeriksaan selesai.
            </div>
            @endif

            <form action="{{ route('pasien.daftar.submit') }}" method="POST">
                @csrf

                <input type="hidden" name="id_pasien" value="{{ $user->id }}">

                <div class="form-control mb-4">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Nomor Rekam Medis</span>
                    </label>
                    <input type="text" value="{{ $user->no_rm ?? '-' }}" class="input input-bordered w-full rounded-lg text-sm bg-slate-100 text-slate-500" readonly>
                </div>

                <div class="form-control mb-4">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Pilih Poli</span>
                    </label>
                    <select name="id_poli" class="select select-bordered w-full rounded-lg text-sm @error('id_poli') select-error @enderror" required {{ $hasActiveQueue ? 'disabled' : '' }}>
                        <option value="">-- Pilih Poli --</option>
                        @foreach($polis as $poli)
                        <option value="{{ $poli->id }}" {{ old('id_poli') == $poli->id ? 'selected' : '' }}>
                            {{ $poli->nama_poli }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_poli')
                    <label class="label pt-1">
                        <span class="label-text-alt text-red-500">{{ $message }}</span>
                    </label>
                    @enderror
                </div>

                <div class="form-control mb-4">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Pilih Jadwal Periksa</span>
                    </label>
                    <select name="id_jadwal" class="select select-bordered w-full rounded-lg text-sm @error('id_jadwal') select-error @enderror" required {{ $hasActiveQueue ? 'disabled' : '' }}>
                        <option value="">-- Pilih Jadwal --</option>
                        @foreach($jadwals as $jadwal)
                        <option value="{{ $jadwal->id }}" {{ old('id_jadwal') == $jadwal->id ? 'selected' : '' }}>
                            {{ $jadwal->dokter->nama ?? '-' }} - {{ $jadwal->dokter->poli->nama_poli ?? '-' }} - {{ $jadwal->hari ?? '-' }} {{ $jadwal->jam_mulai ?? '' }}-{{ $jadwal->jam_selesai ?? '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_jadwal')
                    <label class="label pt-1">
                        <span class="label-text-alt text-red-500">{{ $message }}</span>
                    </label>
                    @enderror
                </div>

                <div class="form-control mb-5">
                    <label class="label pb-1">
                        <span class="text-sm font-semibold text-gray-700">Keluhan</span>
                    </label>
                    <textarea name="keluhan" rows="3" placeholder="Tulis keluhan anda..." class="textarea textarea-bordered w-full rounded-lg text-sm resize-none @error('keluhan') textarea-error @enderror" {{ $hasActiveQueue ? 'disabled' : '' }}>{{ old('keluhan') }}</textarea>
                    @error('keluhan')
                    <label class="label pt-1">
                        <span class="label-text-alt text-red-500">{{ $message }}</span>
                    </label>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-sm bg-[#2d6cf5] hover:bg-[#1f57d5] text-white border-none rounded-md px-6" {{ $hasActiveQueue ? 'disabled' : '' }}>
                        Daftar Poli
                    </button>
                </div>
            </form>
        </div>
    </div>

    </div>

</x-layouts.app>
