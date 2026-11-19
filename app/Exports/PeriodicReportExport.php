<?php

namespace App\Exports;

use App\Exports\Sheets\SummaryReportSheet;
use App\Exports\Sheets\TopProductsReportSheet;
use App\Exports\Sheets\TopProfitableProductsReportSheet;
use App\Exports\Sheets\TransactionsReportSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PeriodicReportExport implements WithMultipleSheets, Export
{
    /**
     * @param array<string, mixed> $summary
     * @param array<string, int> $sourceCounts
     */
    public function __construct(
        private readonly Collection $orders,
        private readonly Collection $topProducts,
        private readonly array $summary = [],
        private readonly string $periodLabel = '',
        private readonly array $sourceCounts = [],
        private readonly ?Collection $profitableProducts = null
    ) {
    }

    public function sheets(): array
    {
        $sheets = [
            new SummaryReportSheet($this->summary, $this->periodLabel, $this->sourceCounts),
            new TransactionsReportSheet($this->orders),
            new TopProductsReportSheet($this->topProducts),
        ];

        if ($this->profitableProducts !== null) {
            $sheets[] = new TopProfitableProductsReportSheet($this->profitableProducts);
        }

        return $sheets;
    }
}

