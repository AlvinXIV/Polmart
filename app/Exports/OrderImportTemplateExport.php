<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrderImportTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'tanggal_transaksi',
            'kode_pesanan_lama',
            'nama_pemesan',
            'kode_barang',
            'nama_barang',
            'qty',
            'harga_satuan',
        ];
    }

    public function array(): array
    {
        return [
            [
                '2026-10-15 09:30:00',
                'OLD-20261015-001',
                'Budi Santoso',
                'ATK-001',
                'Buku Tulis Campus',
                2,
                5000,
            ],
            [
                '2026-10-15 10:15:00',
                'OLD-20261015-001',
                'Budi Santoso',
                'MNM-001',
                'Air Mineral 600ml',
                1,
                3500,
            ],
        ];
    }
}