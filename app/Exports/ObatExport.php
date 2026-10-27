<?php

namespace App\Exports;

use App\Models\Obat;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ObatExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection(): Enumerable
    {
        return Obat::all();
    }

    public function headings(): array
    {
        return ['No', 'Nama Obat', 'Kemasan', 'Harga (Rp)'];
    }

    public function map(mixed $row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->nama_obat,
            $row->kemasan,
            $row->harga,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
