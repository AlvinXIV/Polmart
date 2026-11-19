<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class TopProductsReportSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    private int $rank = 0;

    public function __construct(private readonly Collection $topProducts)
    {
    }

    public function title(): string
    {
        return 'Ringkasan Barang Terlaris';
    }

    public function headings(): array
    {
        return [
            'Peringkat',
            'Kode Barang (SKU)',
            'Nama Barang',
            'Kategori',
            'Total Terjual (Qty)',
            'Total Omzet (Rp)',
            'Sisa Stok Saat Ini',
        ];
    }

    public function collection(): Collection
    {
        return $this->topProducts;
    }

    public function map($product): array
    {
        $this->rank++;

        return [
            $this->rank,
            $product->sku,
            $product->name,
            $product->category,
            (int) $product->total_sold,
            (float) $product->total_revenue,
            (int) $product->stock,
        ];
    }
}

