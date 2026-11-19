<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MemberImportTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'kode_anggota',
            'nama',
            'email',
            'no_hp',
            'password',
        ];
    }

    public function array(): array
    {
        return [
            ['224443101', 'Rina Kartika', 'rina.kartika@polmart.test', '081234567801', 'password123'],
            ['224443102', 'Andi Wijaya', 'andi.wijaya@polmart.test', '081234567802', ''],
        ];
    }
}
