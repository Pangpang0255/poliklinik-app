<x-layouts.app title="Periksa Pasien">

	<div class="mb-5">
		<h2 class="text-2xl font-bold text-slate-800">Periksa Pasien</h2>
	</div>

	<div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
		<div class="card-body p-0">
			<div class="overflow-x-auto">
				<table class="table table-zebra w-full">
					<thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
						<tr>
							<th class="px-6 py-4">ID</th>
							<th class="px-6 py-4">Pasien</th>
							<th class="px-6 py-4">Keluhan</th>
							<th class="px-6 py-4">No Antrian</th>
							<th class="px-6 py-4">Pembayaran</th>
							<th class="px-6 py-4 text-right">Aksi</th>
						</tr>
					</thead>
					<tbody>
						@forelse($daftarPasien as $item)
						@php $latestPeriksa = $item->latestPeriksa; @endphp
						<tr class="hover:bg-slate-50 transition">
							<td class="px-6 py-4 text-slate-700">{{ $item->id }}</td>
							<td class="px-6 py-4 font-semibold text-slate-800">{{ $item->pasien->nama ?? '-' }}</td>
							<td class="px-6 py-4 text-slate-600">{{ $item->keluhan ?? '-' }}</td>
							<td class="px-6 py-4 text-slate-700">{{ $item->no_antrian }}</td>
							<td class="px-6 py-4">
								@if(!$latestPeriksa)
								<span class="text-xs text-slate-400">Belum diperiksa</span>
								@elseif($latestPeriksa->status_pembayaran === 'belum_bayar')
								<span class="badge bg-amber-100 text-amber-700 border-none">Belum Bayar</span>
								@elseif($latestPeriksa->status_pembayaran === 'menunggu_verifikasi')
								<span class="badge bg-blue-100 text-blue-700 border-none">Menunggu Verifikasi</span>
								@else
								<span class="badge bg-emerald-100 text-emerald-700 border-none">Terverifikasi</span>
								@endif
							</td>
							<td class="px-6 py-4 text-right">
								@if($latestPeriksa)
								@if($latestPeriksa->status_pembayaran === 'menunggu_verifikasi')
								<form action="{{ route('periksa-pasien.verify-payment', $latestPeriksa->id) }}" method="POST" class="inline-block mr-2">
									@csrf
									<button type="submit" class="btn btn-sm bg-blue-600 hover:bg-blue-700 text-white border-none rounded-lg px-4">
										Verifikasi Bayar
									</button>
								</form>
								@endif
								<span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
									<span class="w-2 h-2 rounded-full bg-emerald-500"></span>
									Sudah Diperiksa
								</span>
								@else
								<a href="{{ route('periksa-pasien.create', $item->id) }}" class="btn btn-sm bg-amber-500 hover:bg-amber-600 text-white border-none rounded-lg px-4">
									<i class="fas fa-stethoscope"></i>
									Periksa
								</a>
								@endif
							</td>
						</tr>
						@empty
						<tr>
							<td colspan="6" class="text-center py-14 text-slate-400">
								<i class="fas fa-inbox text-3xl mb-3 block"></i>
								Belum ada pasien yang mendaftar pada jadwal Anda.
							</td>
						</tr>
						@endforelse
					</tbody>
				</table>
			</div>
		</div>
	</div>

</x-layouts.app>
