<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMutation;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, OrderService $orderService): View
    {
        $orderService->cancelExpiredOrders();

        // =========================================================================
        // 1. FILTER PERIODE ANALISIS PENJUALAN (Harian, Mingguan, Bulanan, Tahunan)
        // =========================================================================
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
        $startDateInput = $start->toDateString();
        $endDateInput = $end->toDateString();

        // 1. Agregasi Statistik Penjualan Langsung via Database (efisien untuk periode besar/tahunan)
        $completedOrdersBase = DB::table('orders')
            ->where('status', 'SELESAI')
            ->whereBetween('transaction_date', [$start, $end]);

        $periodTotalOmzet = (float) (clone $completedOrdersBase)->sum('total_amount');
        $periodTotalTransactions = (int) (clone $completedOrdersBase)->count();
        $periodTotalItemsSold = (int) DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'SELESAI')
            ->whereBetween('orders.transaction_date', [$start, $end])
            ->sum('order_items.qty');
        $periodAvgTransaction = $periodTotalTransactions > 0 ? $periodTotalOmzet / $periodTotalTransactions : 0.0;

        $periodSummary = [
            'total_omzet' => $periodTotalOmzet,
            'total_transactions' => $periodTotalTransactions,
            'total_items_sold' => $periodTotalItemsSold,
            'avg_transaction' => $periodAvgTransaction,
        ];

        // 2. Agregasi Distribusi Sumber Transaksi via Group By (efisien & deteksi sumber tidak dikenal)
        $sourceCountsRaw = (clone $completedOrdersBase)
            ->select('source', DB::raw('count(*) as count'))
            ->groupBy('source')
            ->pluck('count', 'source')
            ->toArray();

        $periodSourceCounts = [
            'web' => (int) ($sourceCountsRaw['web'] ?? 0),
            'manual_admin' => (int) ($sourceCountsRaw['manual_admin'] ?? 0),
            'import_excel' => (int) ($sourceCountsRaw['import_excel'] ?? 0),
            'other' => 0,
        ];
        foreach ($sourceCountsRaw as $srcName => $count) {
            if (! in_array($srcName, ['web', 'manual_admin', 'import_excel'], true)) {
                $periodSourceCounts['other'] += (int) $count;
            }
        }

        // 3. Produk Terlaris Periode Aktif via Query Grouped
        $periodTopProducts = DB::table('order_items')
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
            ->limit(10)
            ->get();

        // 4. Pengambilan Data Grafik Tren (hanya ambil kolom transaksi yang dibutuhkan)
        $chartOrders = DB::table('orders')
            ->where('status', 'SELESAI')
            ->whereBetween('transaction_date', [$start, $end])
            ->select('transaction_date', 'total_amount')
            ->get();

        $chartData = $this->buildChartDataFromDb($period, $start, $end, $chartOrders);

        // 5. Daftar Transaksi untuk Tabel Dashboard (dibatasi 50 transaksi terbaru agar DOM ringan pada periode tahunan)
        $periodOrders = Order::with(['user', 'items.product'])
            ->where('status', 'SELESAI')
            ->whereBetween('transaction_date', [$start, $end])
            ->orderByDesc('transaction_date')
            ->limit(50)
            ->get();

        $totalPeriodOrders = $periodTotalTransactions;

        // 6. Parameter Export Excel Terfilter (hanya berisi parameter periode yang relevan)
        $exportParams = match ($period) {
            'daily' => ['period' => 'daily', 'date' => $date],
            'weekly' => ['period' => 'weekly', 'start_date' => $startDateInput, 'end_date' => $endDateInput],
            'yearly' => ['period' => 'yearly', 'year' => $year],
            default => ['period' => 'monthly', 'month' => $month, 'year' => $year],
        };

        // =========================================================================
        // 2. DATA UTILITY & INVENTARIS BAWAAN DASHBOARD (DIPERTAHANKAN UTUH)
        // =========================================================================
        $topFilter = $request->input('top_filter', 'all') === 'month' ? 'month' : 'all';

        $topSellingQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'SELESAI');

        if ($topFilter === 'month') {
            $topSellingQuery->whereBetween('orders.transaction_date', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ]);
        }

        $topSellingProducts = $topSellingQuery
            ->select(
                'products.id',
                'products.sku',
                'products.name',
                'products.category',
                'products.price',
                'products.stock',
                DB::raw('SUM(order_items.qty) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy(
                'products.id',
                'products.sku',
                'products.name',
                'products.category',
                'products.price',
                'products.stock'
            )
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        $highestStockProducts = Product::where('is_active', true)
            ->orderByDesc('stock')
            ->orderBy('name')
            ->limit(10)
            ->get();

        $readyPickupOrders = Order::with(['user', 'items.product'])
            ->where('status', 'SIAP_DIAMBIL')
            ->latest('paid_at')
            ->limit(8)
            ->get();

        $recentOrders = Order::with('items')
            ->latest('transaction_date')
            ->limit(6)
            ->get();

        $recentMutations = StockMutation::with(['product', 'user'])
            ->latest()
            ->limit(6)
            ->get();

        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'low_stock_count' => Product::where('stock', '>', 0)->where('stock', '<=', 10)->count(),
            'out_of_stock_count' => Product::where('stock', '<=', 0)->count(),
            'categories_count' => Product::distinct('category')->count('category'),
            'completed_orders_count' => Order::where('status', 'SELESAI')->count(),
            'total_revenue' => (float) Order::where('status', 'SELESAI')->sum('total_amount'),
            'ready_pickup_count' => Order::where('status', 'SIAP_DIAMBIL')->count(),
            'pending_payment_count' => Order::where('status', 'MENUNGGU_BAYAR')->count(),
        ];

        return view('admin.dashboard', compact(
            'period',
            'date',
            'startDateInput',
            'endDateInput',
            'month',
            'year',
            'periodLabel',
            'periodOrders',
            'totalPeriodOrders',
            'periodSummary',
            'periodSourceCounts',
            'periodTopProducts',
            'chartData',
            'exportParams',
            'topFilter',
            'topSellingProducts',
            'highestStockProducts',
            'readyPickupOrders',
            'recentOrders',
            'recentMutations',
            'stats'
        ));
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

    /**
     * Membangun dataset deret waktu untuk grafik tren penjualan sesuai periode aktif.
     *
     * @param Collection<int, object> $orders
     * @return array<string, mixed>
     */
    private function buildChartDataFromDb(string $period, Carbon $start, Carbon $end, Collection $orders): array
    {
        $items = [];

        if ($period === 'daily') {
            for ($h = 0; $h < 24; $h++) {
                $hourKey = sprintf('%02d', $h);
                $items[$hourKey] = [
                    'label' => sprintf('%02d:00', $h),
                    'short_label' => sprintf('%02d', $h),
                    'omzet' => 0.0,
                    'count' => 0,
                ];
            }

            foreach ($orders as $order) {
                if (! empty($order->transaction_date)) {
                    $dt = Carbon::parse($order->transaction_date);
                    $hourKey = $dt->format('H');
                    if (isset($items[$hourKey])) {
                        $items[$hourKey]['omzet'] += (float) $order->total_amount;
                        $items[$hourKey]['count']++;
                    }
                }
            }
        } elseif ($period === 'weekly') {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $dateKey = $cursor->toDateString();
                $items[$dateKey] = [
                    'label' => $cursor->translatedFormat('D, d M'),
                    'short_label' => $cursor->translatedFormat('D'),
                    'omzet' => 0.0,
                    'count' => 0,
                ];
                $cursor->addDay();
            }

            foreach ($orders as $order) {
                if (! empty($order->transaction_date)) {
                    $dt = Carbon::parse($order->transaction_date);
                    $dateKey = $dt->toDateString();
                    if (isset($items[$dateKey])) {
                        $items[$dateKey]['omzet'] += (float) $order->total_amount;
                        $items[$dateKey]['count']++;
                    }
                }
            }
        } elseif ($period === 'yearly') {
            for ($m = 1; $m <= 12; $m++) {
                $monthKey = sprintf('%02d', $m);
                $items[$monthKey] = [
                    'label' => Carbon::create(null, $m, 1)->translatedFormat('F'),
                    'short_label' => Carbon::create(null, $m, 1)->translatedFormat('M'),
                    'omzet' => 0.0,
                    'count' => 0,
                ];
            }

            foreach ($orders as $order) {
                if (! empty($order->transaction_date)) {
                    $dt = Carbon::parse($order->transaction_date);
                    $monthKey = $dt->format('m');
                    if (isset($items[$monthKey])) {
                        $items[$monthKey]['omzet'] += (float) $order->total_amount;
                        $items[$monthKey]['count']++;
                    }
                }
            }
        } else {
            // Default: monthly
            $daysInMonth = (int) $start->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dayKey = sprintf('%02d', $d);
                $items[$dayKey] = [
                    'label' => 'Tgl ' . $d . ' ' . $start->translatedFormat('M'),
                    'short_label' => (string) $d,
                    'omzet' => 0.0,
                    'count' => 0,
                ];
            }

            foreach ($orders as $order) {
                if (! empty($order->transaction_date)) {
                    $dt = Carbon::parse($order->transaction_date);
                    $dayKey = $dt->format('d');
                    if (isset($items[$dayKey])) {
                        $items[$dayKey]['omzet'] += (float) $order->total_amount;
                        $items[$dayKey]['count']++;
                    }
                }
            }
        }

        $series = array_values($items);
        $maxOmzet = 0.0;
        $maxCount = 0;

        foreach ($series as $point) {
            if ($point['omzet'] > $maxOmzet) {
                $maxOmzet = $point['omzet'];
            }
            if ($point['count'] > $maxCount) {
                $maxCount = $point['count'];
            }
        }

        return [
            'period' => $period,
            'unit_name' => match ($period) {
                'daily' => 'Jam',
                'weekly' => 'Hari',
                'yearly' => 'Bulan',
                default => 'Tanggal',
            },
            'series' => $series,
            'max_omzet' => $maxOmzet > 0 ? $maxOmzet : 1.0,
            'max_count' => $maxCount > 0 ? $maxCount : 1,
            'has_data' => $orders->isNotEmpty(),
        ];
    }
}