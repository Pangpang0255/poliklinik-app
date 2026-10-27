<?php

namespace App\Exports;

use App\Models\JadwalPeriksa;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JadwalExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection(): Enumerable
    {
        return JadwalPeriksa::with('dokter.poli')
            ->where('id_dokter', Auth::id())
            ->orderBy('hari')
            ->get();
    }

    public function headings(): array
    {
        return ['No', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Dokter', 'Poli'];
    }

    public function map(mixed $row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->hari,
            substr((string) $row->jam_mulai, 0, 5),
            substr((string) $row->jam_selesai, 0, 5),
            optional($row->dokter)->nama ?? '-',
            optional(optional($row->dokter)->poli)->nama_poli ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
