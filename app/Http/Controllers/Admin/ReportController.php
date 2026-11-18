<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PeriodicReportExport;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(Request $request, OrderService $orderService): View
    {
        $orderService->cancelExpiredOrders();

        $report = $this->buildReportData($request);

        return view('admin.laporan', $report);
    }

    public function export(Request $request, OrderService $orderService): BinaryFileResponse
    {
        $orderService->cancelExpiredOrders();

        $report = $this->buildReportData($request);

        $period = $report['period'];
        $date = $report['date'];
        $startDate = $report['startDate'];
        $endDate = $report['endDate'];
        $month = sprintf('%02d', (int) $report['month']);
        $year = (int) $report['year'];

        $filename = match ($period) {
            'daily' => "Laporan_Penjualan_Harian_{$date}.xlsx",
            'weekly' => "Laporan_Penjualan_Mingguan_{$startDate}_sd_{$endDate}.xlsx",
            'yearly' => "Laporan_Penjualan_Tahunan_{$year}.xlsx",
            default => "Laporan_Penjualan_Bulanan_{$year}-{$month}.xlsx",
        };

        return Excel::download(
            new PeriodicReportExport(
                $report['orders'],
                $report['topProducts'],
                $report['summary'],
                $report['periodLabel'],
                $report['sourceCounts']
            ),
            $filename
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReportData(Request $request): array
    {
        $period = $request->input('period', 'monthly');
        if (! in_array($period, ['daily', 'weekly', 'monthly', 'yearly'], true)) {
            $period = 'monthly';
        }

        $rawDate = $request->input('date');
        $rawStartDate = $request->input('start_date');
        $rawEndDate = $request->input('end_date');
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = (int) now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) now()->year;
        }

        [$start, $end, $periodLabel] = match ($period) {
            'daily' => (function () use ($rawDate) {
                $parsed = $this->safeParseDate($rawDate, now());
                return [
                    $parsed->copy()->startOfDay(),
                    $parsed->copy()->endOfDay(),
                    'Harian: ' . $parsed->translatedFormat('d F Y'),
                ];
            })(),
            'weekly' => (function () use ($rawStartDate, $rawEndDate) {
                $s = $this->safeParseDate($rawStartDate, now()->startOfWeek())->startOfDay();
                $e = $this->safeParseDate($rawEndDate, now()->endOfWeek())->endOfDay();
                if ($e->lt($s)) {
                    [$s, $e] = [$e->copy()->startOfDay(), $s->copy()->endOfDay()];
                }
                // Batasi rentang mingguan agar benar-benar maksimal 7 hari
                if ($s->diffInDays($e) > 6) {
                    $e = $s->copy()->addDays(6)->endOfDay();
                }
                return [$s, $e, 'Mingguan: ' . $s->format('d/m/Y') . ' - ' . $e->format('d/m/Y')];
            })(),
            'yearly' => [
                Carbon::create($year, 1, 1)->startOfYear(),
                Carbon::create($year, 12, 31)->endOfYear(),
                'Tahunan: ' . $year,
            ],
            default => [
                Carbon::create($year, $month, 1)->startOfMonth(),
                Carbon::create($year, $month, 1)->endOfMonth(),
                'Bulanan: ' . Carbon::create($year, $month, 1)->translatedFormat('F Y'),
            ],
        };

        $date = $start->toDateString();
        $startDate = $start->toDateString();
        $endDate = $end->toDateString();

        $orders = Order::with(['user', 'items.product'])
            ->where('status', 'SELESAI')
            ->whereBetween('transaction_date', [$start, $end])
            ->orderByDesc('transaction_date')
            ->get();

        $totalOmzet = (float) $orders->sum('total_amount');
        $totalTransactions = $orders->count();
        $totalItemsSold = (int) $orders->sum(fn (Order $order) => $order->items->sum('qty'));
        $avgTransaction = $totalTransactions > 0 ? $totalOmzet / $totalTransactions : 0.0;

        $sourceCounts = [
            'web' => $orders->where('source', 'web')->count(),
            'manual_admin' => $orders->where('source', 'manual_admin')->count(),
            'import_excel' => $orders->where('source', 'import_excel')->count(),
            'other' => $orders->whereNotIn('source', ['web', 'manual_admin', 'import_excel'])->count(),
        ];

        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'SELESAI')
            ->whereBetween('orders.transaction_date', [$start, $end])
            ->select(
                'products.id',
                'products.sku',
                'products.name',
                'products.category',
                'products.stock',
                DB::raw('SUM(order_items.qty) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy(
                'products.id',
                'products.sku',
                'products.name',
                'products.category',
                'products.stock'
            )
            ->orderByDesc('total_sold')
            ->get();

        return [
            'period' => $period,
            'date' => $date,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'month' => $month,
            'year' => $year,
            'periodLabel' => $periodLabel,
            'orders' => $orders,
            'topProducts' => $topProducts,
            'summary' => [
                'total_omzet' => $totalOmzet,
                'total_transactions' => $totalTransactions,
                'total_items_sold' => $totalItemsSold,
                'avg_transaction' => $avgTransaction,
            ],
            'sourceCounts' => $sourceCounts,
        ];
    }

    /**
     * Parsing tanggal aman dengan validasi kalender ketat (menolak non-string/array, format non-YYYY-MM-DD, dan tanggal kalender tidak valid).
     */
    private function safeParseDate(mixed $value, Carbon $fallback): Carbon
    {
        if (! is_string($value) || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches)) {
            return $fallback->copy();
        }

        $year = (int) $matches[1];
        $month = (int) $matches[2];
        $day = (int) $matches[3];

        if (! checkdate($month, $day, $year)) {
            return $fallback->copy();
        }

        try {
            return Carbon::create($year, $month, $day);
        } catch (\Throwable) {
            return $fallback->copy();
        }
    }
}

