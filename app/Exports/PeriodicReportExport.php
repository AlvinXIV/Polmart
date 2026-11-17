<?php

namespace App\Exports;

use App\Exports\Sheets\TopProductsReportSheet;
use App\Exports\Sheets\TransactionsReportSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PeriodicReportExport implements WithMultipleSheets
{
    public function __construct(
        private readonly Collection $orders,
        private readonly Collection $topProducts
    ) {
    }

    public function sheets(): array
    {
        return [
            new TransactionsReportSheet($this->orders),
            new TopProductsReportSheet($this->topProducts),
        ];
    }
}

