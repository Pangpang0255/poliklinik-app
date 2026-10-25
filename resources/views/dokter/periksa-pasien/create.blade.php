<x-layouts.app title="Periksa Pasien">

	<div class="flex items-center gap-3 mb-6">
		<a href="{{ route('periksa-pasien.index') }}" class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
			<i class="fas fa-arrow-left text-xs"></i>
		</a>
		<h2 class="text-2xl font-bold text-slate-800">Periksa Pasien</h2>
	</div>

	<div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
		<div class="card-body p-7">

			<form action="{{ route('periksa-pasien.store') }}" method="POST" id="formPeriksaPasien">
				@csrf

				<input type="hidden" name="id_daftar_poli" value="{{ $id }}">
				<input type="hidden" name="obat_json" id="obatJson" value="{{ old('obat_json', '[]') }}">
				<input type="hidden" name="biaya_periksa" id="biayaPeriksa" value="{{ old('biaya_periksa', 0) }}">

				<div class="grid grid-cols-1 gap-5">

					<div>
						<label class="block text-sm font-semibold text-slate-700 mb-1">
							Pilih Obat <span class="text-red-500">*</span>
						</label>
						<div class="flex gap-2">
							<select id="obatSelect" class="select select-bordered w-full rounded-lg text-sm @error('obat_json') select-error @enderror">
								<option value="">-- Pilih Obat --</option>
								@foreach($obats as $obat)
								<option value="{{ $obat->id }}" data-nama="{{ $obat->nama_obat }}" data-kemasan="{{ $obat->kemasan ?? '-' }}" data-harga="{{ $obat->harga }}">
									{{ $obat->nama_obat }} ({{ $obat->kemasan ?? '-' }}) - Rp {{ number_format($obat->harga, 0, ',', '.') }}
								</option>
								@endforeach
							</select>
							<button type="button" id="btnTambahObat" class="btn bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-4">
								Tambah
							</button>
						</div>
						@error('obat_json')
						<p class="text-xs text-red-500 mt-1">{{ $message }}</p>
						@enderror
					</div>

					<div>
						<label class="block text-sm font-semibold text-slate-700 mb-2">Obat Terpilih</label>
						<div id="obatKosong" class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-3 text-sm text-slate-500">
							Belum ada obat yang dipilih.
						</div>
						<div id="daftarObatTerpilih" class="space-y-2"></div>
					</div>

					<div>
						<label class="block text-sm font-semibold text-slate-700 mb-1">Total Harga</label>
						<div class="flex items-center border-2 border-slate-200 rounded-lg px-4 py-2.5 bg-slate-50">
							<span class="text-slate-600 text-sm font-semibold mr-2">Rp</span>
							<input type="text" id="totalHargaTampil" class="w-full text-sm text-slate-700 bg-transparent focus:outline-none" value="0" readonly>
						</div>
						@error('biaya_periksa')
						<p class="text-xs text-red-500 mt-1">{{ $message }}</p>
						@enderror
					</div>

					<div>
						<label class="block text-sm font-semibold text-slate-700 mb-1">Catatan (Opsional)</label>
						<textarea name="catatan" rows="4" placeholder="Masukkan catatan..." class="textarea textarea-bordered w-full rounded-lg text-sm resize-none @error('catatan') textarea-error @enderror">{{ old('catatan') }}</textarea>
						@error('catatan')
						<p class="text-xs text-red-500 mt-1">{{ $message }}</p>
						@enderror
					</div>

				</div>

				<div class="flex gap-3 mt-6">
					<button type="submit" class="btn bg-[#2d4499] hover:bg-[#1e2d6b] text-white border-none rounded-lg px-6">
						<i class="fas fa-save"></i>
						Simpan
					</button>
					<a href="{{ route('periksa-pasien.index') }}" class="btn bg-slate-100 hover:bg-slate-200 text-slate-600 border-none rounded-lg px-6">
						Batal
					</a>
				</div>

			</form>
		</div>
	</div>

	<script>
		(() => {
			const obatSelect = document.getElementById('obatSelect');
			const btnTambahObat = document.getElementById('btnTambahObat');
			const daftarObatTerpilih = document.getElementById('daftarObatTerpilih');
			const obatKosong = document.getElementById('obatKosong');
			const obatJson = document.getElementById('obatJson');
			const biayaPeriksa = document.getElementById('biayaPeriksa');
			const totalHargaTampil = document.getElementById('totalHargaTampil');

			const selectedObat = new Map();

			const formatRupiah = (angka) => new Intl.NumberFormat('id-ID').format(angka || 0);

			const updateState = () => {
				const ids = Array.from(selectedObat.keys());
				const total = Array.from(selectedObat.values()).reduce((sum, item) => sum + Number(item.harga), 0);

				obatJson.value = JSON.stringify(ids);
				biayaPeriksa.value = total;
				totalHargaTampil.value = formatRupiah(total);

				obatKosong.style.display = ids.length ? 'none' : 'block';
			};

			const renderDaftar = () => {
				daftarObatTerpilih.innerHTML = '';

				selectedObat.forEach((item, id) => {
					const row = document.createElement('div');
					row.className = 'flex items-center justify-between border border-slate-200 rounded-lg px-3 py-2 bg-white';
					row.innerHTML = `
						<div>
							<p class="text-sm font-semibold text-slate-700">${item.nama}</p>
							<p class="text-xs text-slate-500">${item.kemasan} - Rp ${formatRupiah(item.harga)}</p>
						</div>
						<button type="button" class="btn btn-xs bg-red-100 hover:bg-red-200 text-red-600 border-none" data-id="${id}">
							Hapus
						</button>
					`;
					daftarObatTerpilih.appendChild(row);
				});

				updateState();
			};

			const tambahObat = () => {
				const option = obatSelect.options[obatSelect.selectedIndex];
				const id = option.value;

				if (!id) {
					return;
				}

				if (!selectedObat.has(id)) {
					selectedObat.set(id, {
						nama: option.dataset.nama,
						kemasan: option.dataset.kemasan,
						harga: Number(option.dataset.harga),
					});
					renderDaftar();
				}

				obatSelect.value = '';
			};

			btnTambahObat.addEventListener('click', tambahObat);
			obatSelect.addEventListener('change', () => {
				if (obatSelect.value) {
					tambahObat();
				}
			});

			daftarObatTerpilih.addEventListener('click', (event) => {
				const button = event.target.closest('button[data-id]');
				if (!button) {
					return;
				}

				selectedObat.delete(button.dataset.id);
				renderDaftar();
			});

			try {
				const oldIds = JSON.parse(obatJson.value || '[]');
				if (Array.isArray(oldIds)) {
					oldIds.forEach((id) => {
						const option = obatSelect.querySelector(`option[value="${id}"]`);
						if (option) {
							selectedObat.set(String(id), {
								nama: option.dataset.nama,
								kemasan: option.dataset.kemasan,
								harga: Number(option.dataset.harga),
							});
						}
					});
				}
			} catch (error) {
				obatJson.value = '[]';
			}

			renderDaftar();
		})();
	</script>

</x-layouts.app>
