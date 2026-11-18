<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SummaryReportSheet implements FromArray, WithHeadings, ShouldAutoSize, WithTitle
{
    /**
     * @param array<string, mixed> $summary
     * @param array<string, int> $sourceCounts
     */
    public function __construct(
        private readonly array $summary = [],
        private readonly string $periodLabel = '',
        private readonly array $sourceCounts = []
    ) {
    }

    public function title(): string
    {
        return 'Ringkasan Eksekutif';
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Parameter / Metrik Analisis',
            'Nilai / Informasi',
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        $totalOmzet = (float) ($this->summary['total_omzet'] ?? 0.0);
        $totalTransactions = (int) ($this->summary['total_transactions'] ?? 0);
        $totalItemsSold = (int) ($this->summary['total_items_sold'] ?? 0);
        $avgTransaction = (float) ($this->summary['avg_transaction'] ?? 0.0);

        $totalHpp = (float) ($this->summary['total_hpp'] ?? 0.0);
        $grossProfit = (float) ($this->summary['gross_profit'] ?? 0.0);
        $hasUnrecorded = (bool) ($this->summary['has_unrecorded_hpp'] ?? false);
        $unrecordedItems = (int) ($this->summary['unrecorded_items_count'] ?? 0);
        $unrecordedOrders = (int) ($this->summary['unrecorded_orders_count'] ?? 0);

        $hppStatus = $hasUnrecorded
            ? "Provisional ({$unrecordedOrders} pesanan / {$unrecordedItems} item belum tercatat HPP)"
            : "Lengkap (100% item memiliki catatan HPP)";

        $webCount = (int) ($this->sourceCounts['web'] ?? 0);
        $walkinCount = (int) ($this->sourceCounts['manual_admin'] ?? 0);
        $importCount = (int) ($this->sourceCounts['import_excel'] ?? 0);
        $otherCount = (int) ($this->sourceCounts['other'] ?? 0);

        $pctWeb = $totalTransactions > 0 ? round(($webCount / $totalTransactions) * 100, 1) : 0;
        $pctWalkin = $totalTransactions > 0 ? round(($walkinCount / $totalTransactions) * 100, 1) : 0;
        $pctImport = $totalTransactions > 0 ? round(($importCount / $totalTransactions) * 100, 1) : 0;
        $pctOther = $totalTransactions > 0 ? round(($otherCount / $totalTransactions) * 100, 1) : 0;

        return [
            ['Nama Laporan', 'Laporan Analisis & Rekap Penjualan Polmart'],
            ['Periode Laporan', $this->periodLabel ?: '-'],
            ['Waktu Export Data', now()->translatedFormat('d F Y H:i:s') . ' WIB'],
            ['Kriteria Data Transaksi', 'Hanya transaksi berstatus SELESAI'],
            ['-----------------------------------------', '-----------------------------------------'],
            ['Total Omzet Penjualan (Rp)', 'Rp ' . number_format($totalOmzet, 0, ',', '.')],
            ['Total HPP Tercatat (Rp)', 'Rp ' . number_format($totalHpp, 0, ',', '.')],
            ['Laba Kotor (Rp)', 'Rp ' . number_format($grossProfit, 0, ',', '.')],
            ['Status Kelengkapan HPP', $hppStatus],
            ['Jumlah Transaksi Sukses', number_format($totalTransactions, 0, ',', '.') . ' Pesanan'],
            ['Total Produk Terjual (Qty)', number_format($totalItemsSold, 0, ',', '.') . ' Pcs'],
            ['Rata-rata Nilai Transaksi (Rp)', 'Rp ' . number_format($avgTransaction, 0, ',', '.')],
            ['-----------------------------------------', '-----------------------------------------'],
            ['Distribusi: Portal Anggota (QRIS)', number_format($webCount, 0, ',', '.') . " Transaksi ({$pctWeb}%)"],
            ['Distribusi: Kasir Walk-in', number_format($walkinCount, 0, ',', '.') . " Transaksi ({$pctWalkin}%)"],
            ['Distribusi: Impor Excel Historis', number_format($importCount, 0, ',', '.') . " Transaksi ({$pctImport}%)"],
            ['Distribusi: Sumber Lainnya', number_format($otherCount, 0, ',', '.') . " Transaksi ({$pctOther}%)"],
        ];
    }
}
