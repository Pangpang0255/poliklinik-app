<x-layouts.app title="Ganti Password">

    <div class="max-w-xl mx-auto">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Ganti Password</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui kata sandi akun Anda</p>
        </div>

        {{-- Success --}}
        @if(session('success'))
        <div class="alert alert-success mb-4 rounded-xl shadow-sm">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">

            <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-5">
                @csrf

                {{-- Password Lama --}}
                <div>
                    <label for="password_lama" class="block text-sm font-semibold text-slate-700 mb-1">
                        Password Lama <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password_lama"
                            name="password_lama"
                            placeholder="Masukkan password lama"
                            class="input input-bordered w-full pr-10 @error('password_lama') border-red-400 bg-red-50 @enderror"
                            autocomplete="current-password"
                        >
                        <button type="button" onclick="togglePwd('password_lama', 'icon_lama')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="icon_lama" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password_lama')
                    <p class="text-xs text-red-500 mt-1"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Baru --}}
                <div>
                    <label for="password_baru" class="block text-sm font-semibold text-slate-700 mb-1">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password_baru"
                            name="password_baru"
                            placeholder="Minimal 8 karakter"
                            class="input input-bordered w-full pr-10 @error('password_baru') border-red-400 bg-red-50 @enderror"
                            autocomplete="new-password"
                        >
                        <button type="button" onclick="togglePwd('password_baru', 'icon_baru')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="icon_baru" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password_baru')
                    <p class="text-xs text-red-500 mt-1"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Password Baru --}}
                <div>
                    <label for="password_baru_confirmation" class="block text-sm font-semibold text-slate-700 mb-1">
                        Konfirmasi Password Baru <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password_baru_confirmation"
                            name="password_baru_confirmation"
                            placeholder="Ulangi password baru"
                            class="input input-bordered w-full pr-10 @error('password_baru_confirmation') border-red-400 bg-red-50 @enderror"
                            autocomplete="new-password"
                        >
                        <button type="button" onclick="togglePwd('password_baru_confirmation', 'icon_konfirmasi')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="icon_konfirmasi" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password_baru_confirmation')
                    <p class="text-xs text-red-500 mt-1"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ketentuan --}}
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-700 space-y-1">
                    <p class="font-semibold mb-2"><i class="fas fa-info-circle mr-1"></i>Ketentuan Password:</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li>Minimal 8 karakter</li>
                        <li>Password baru tidak boleh sama dengan password lama</li>
                        <li>Konfirmasi password harus cocok dengan password baru</li>
                    </ul>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="btn btn-primary px-8 gap-2">
                        <i class="fas fa-lock"></i>
                        Simpan Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function togglePwd(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon  = document.getElementById(iconId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
    @endpush

</x-layouts.app>
