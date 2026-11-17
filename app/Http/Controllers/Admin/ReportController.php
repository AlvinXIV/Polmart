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
        $filename = sprintf(
            'laporan_polmart_%s_%s.xlsx',
            $report['period'],
            now()->format('Ymd_His')
        );

        return Excel::download(
            new PeriodicReportExport($report['orders'], $report['topProducts']),
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

        $date = $request->input('date', now()->toDateString());
        $startDateInput = $request->input('start_date', now()->startOfWeek()->toDateString());
        $endDateInput = $request->input('end_date', now()->endOfWeek()->toDateString());
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        if ($month < 1 || $month > 12) {
            $month = now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = now()->year;
        }

        [$start, $end, $periodLabel] = match ($period) {
            'daily' => [
                Carbon::parse($date)->startOfDay(),
                Carbon::parse($date)->endOfDay(),
                'Harian: ' . Carbon::parse($date)->translatedFormat('d F Y'),
            ],
            'weekly' => (function () use ($startDateInput, $endDateInput) {
                $s = Carbon::parse($startDateInput)->startOfDay();
                $e = Carbon::parse($endDateInput)->endOfDay();
                if ($e->lt($s)) {
                    [$s, $e] = [$e->copy()->startOfDay(), $s->copy()->endOfDay()];
                }
                return [$s, $e, 'Pekanan: ' . $s->format('d/m/Y') . ' - ' . $e->format('d/m/Y')];
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
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
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
}

