<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class TopProfitableProductsReportSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    private int $rank = 0;

    public function __construct(private readonly Collection $profitableProducts)
    {
    }

    public function title(): string
    {
        return 'Produk Paling Menguntungkan';
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Peringkat Laba',
            'Kode Barang (SKU)',
            'Nama Barang',
            'Kategori',
            'Total Terjual (Qty)',
            'Total Omzet (Rp)',
            'Total HPP Tercatat (Rp)',
            'Total Laba Kotor (Rp)',
            'Margin Laba (%)',
            'Status HPP',
        ];
    }

    public function collection(): Collection
    {
        return $this->profitableProducts;
    }

    /**
     * @param object $product
     * @return array<int, mixed>
     */
    public function map($product): array
    {
        $this->rank++;
        $rev = (float) ($product->total_revenue ?? 0.0);
        $cost = (float) ($product->total_cost ?? 0.0);
        $profit = (float) ($product->total_profit ?? 0.0);
        $unrecorded = (int) ($product->unrecorded_items_count ?? 0);
        $marginPct = $rev > 0 ? round(($profit / $rev) * 100, 1) : 0.0;
        $status = $unrecorded > 0 ? 'Provisional (HPP Belum Lengkap)' : 'Lengkap';

        return [
            $this->rank,
            $product->sku,
            $product->name,
            $product->category,
            (int) $product->total_sold,
            $rev,
            $cost,
            $profit,
            $marginPct . '%',
            $status,
        ];
    }
}
