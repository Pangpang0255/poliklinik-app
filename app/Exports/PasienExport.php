<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PasienExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection(): Enumerable
    {
        return User::where('role', 'pasien')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Pasien', 'Email', 'No. HP', 'No. KTP', 'No. RM', 'Alamat'];
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
            $row->no_rm ?? '-',
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
