<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class TransactionsReportSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    public function __construct(private readonly Collection $orders)
    {
    }

    public function title(): string
    {
        return 'Rekap Transaksi Lengkap';
    }

    public function headings(): array
    {
        return [
            'Kode Pesanan',
            'Tanggal Transaksi',
            'Nama Pemesan',
            'Sumber',
            'Status',
            'Total Item',
            'Total Nominal (Rp)',
            'Waktu Bayar',
        ];
    }

    public function collection(): Collection
    {
        return $this->orders;
    }

    public function map($order): array
    {
        return [
            $order->order_code,
            $order->transaction_date?->format('Y-m-d H:i:s'),
            $order->customer_name,
            match ($order->source) {
                'web' => 'Portal Anggota (QRIS)',
                'manual_admin' => 'Kasir Walk-in',
                'import_excel' => 'Impor Excel',
                default => $order->source,
            },
            $order->status,
            $order->items->sum('qty'),
            (float) $order->total_amount,
            $order->paid_at?->format('Y-m-d H:i:s') ?? '-',
        ];
    }
}

