<?php

namespace App\Exports;

use App\Models\Periksa;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RiwayatPasienExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection(): Enumerable
    {
        return Periksa::whereHas('daftarPoli.jadwalPeriksa', function ($q) {
                $q->where('id_dokter', Auth::id());
            })
            ->with(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa'])
            ->orderBy('tgl_periksa', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return ['No', 'Tanggal Periksa', 'Nama Pasien', 'Catatan', 'Biaya (Rp)', 'Status Pembayaran'];
    }

    public function map(mixed $row): array
    {
        static $no = 0;
        $no++;

        $statusMap = [
            'belum_bayar'        => 'Belum Bayar',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'terverifikasi'      => 'Lunas',
        ];

        return [
            $no,
            $row->tgl_periksa ? \Carbon\Carbon::parse($row->tgl_periksa)->format('d/m/Y') : '-',
            optional(optional($row->daftarPoli)->pasien)->nama ?? '-',
            $row->catatan ?? '-',
            $row->biaya_periksa,
            $statusMap[$row->status_pembayaran] ?? $row->status_pembayaran,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
