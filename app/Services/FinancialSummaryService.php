<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialSummaryService
{
    /**
     * Mengambil agregasi Beban Operasional (OPEX) dari database berdasarkan rentang tanggal periode aktif.
     *
     * @return array{
     *     total_opex: float,
     *     opex_count: int,
     *     has_opex: bool
     * }
     */
    public function getOpexSummary(Carbon $start, Carbon $end): array
    {
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();

        $query = DB::table('operating_expenses')
            ->whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate);

        $totalOpex = (float) (clone $query)->sum('amount');
        $opexCount = (int) (clone $query)->count();

        return [
            'total_opex' => $totalOpex,
            'opex_count' => $opexCount,
            'has_opex' => $opexCount > 0,
        ];
    }

    /**
     * Menghitung Laba Bersih (Net Profit), Net Profit Margin (NPM), dan status provisional secara terpusat.
     * Digunakan bersama oleh DashboardController, ReportController, dan Sheet Ringkasan Excel.
     *
     * Rumus Bisnis:
     * - Net Profit = Gross Profit - Total OPEX
     * - NPM = (Net Profit / Total Omzet) * 100% (null jika omzet 0 untuk mencegah pembagian dengan nol)
     *
     * @return array{
     *     net_profit: float,
     *     npm: ?float,
     *     is_net_profit_provisional: bool,
     *     provisional_reasons: array{
     *         unrecorded_hpp: bool,
     *         no_opex: bool
     *     }
     * }
     */
    public function calculateNetProfit(
        float $grossProfit,
        float $totalOmzet,
        float $totalOpex,
        int $opexCount,
        bool $hasUnrecordedHpp,
        int $totalTransactions = 0
    ): array {
        $netProfit = $grossProfit - $totalOpex;
        $npm = $totalOmzet > 0 ? round(($netProfit / $totalOmzet) * 100, 1) : null;

        $isProvisionalNoOpex = ($opexCount === 0 && ($totalTransactions > 0 || $totalOmzet > 0));
        $isNetProfitProvisional = $hasUnrecordedHpp || $isProvisionalNoOpex;

        return [
            'net_profit' => $netProfit,
            'npm' => $npm,
            'is_net_profit_provisional' => $isNetProfitProvisional,
            'provisional_reasons' => [
                'unrecorded_hpp' => $hasUnrecordedHpp,
                'no_opex' => $isProvisionalNoOpex,
            ],
        ];
    }
}
