<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DokterExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection(): Enumerable
    {
        return User::where('role', 'dokter')->with('poli')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Dokter', 'Email', 'No. HP', 'No. KTP', 'Poli', 'Alamat'];
    }

    public function map(mixed $row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->nama,
            $row->email,
            $row->no_hp,
            $row->no_ktp,
            optional($row->poli)->nama_poli ?? '-',
            $row->alamat,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
