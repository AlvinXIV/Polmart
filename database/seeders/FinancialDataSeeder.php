<?php

namespace Database\Seeders;

use App\Models\OperatingExpense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinancialDataSeeder extends Seeder
{
    /**
     * Rasio estimasi harga modal (cost_price / price) berdasarkan kategori koperasi/minimarket.
     * - Sembako / Mie Instan: margin tipis ~14% (modal ~86%)
     * - ATK & Perlengkapan: margin ~26% (modal ~74%)
     * - Kebutuhan Harian & Kesehatan: margin ~22% (modal ~78%)
     * - Makanan / Minuman / Snack / Roti: margin ~20%-22% (modal ~78%-80%)
     */
    private const CATEGORY_COST_RATIOS = [
        'Mie & Sembako' => 0.86,
        'Alat Tulis' => 0.74,
        'Kebutuhan Harian' => 0.78,
        'Kesehatan' => 0.78,
        'Makanan Ringan' => 0.78,
        'Roti & Kue' => 0.79,
        'Makanan' => 0.78,
        'Minuman' => 0.78,
    ];

    private const WALKIN_CUSTOMERS = [
        'Pelanggan Walk-in',
        'Mahasiswa Kampus',
        'Budi Santoso (Staf)',
        'Siti Aminah (Dosen)',
        'Rizky Pratama (Walk-in)',
        'Dewi Lestari (Walk-in)',
    ];

    public function run(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $stats = [
            'products_updated' => 0,
            'order_items_updated' => 0,
            'orders_inserted' => 0,
            'opex_inserted' => 0,
        ];

        DB::transaction(function () use (&$stats) {
            // 1. Lengkapi cost_price pada produk yang masih 0 atau null
            $products = Product::query()
                ->where(function ($q) {
                    $q->whereNull('cost_price')
                        ->orWhere('cost_price', '<=', 0);
                })
                ->get();

            foreach ($products as $product) {
                $sellPrice = (float) $product->price;
                if ($sellPrice <= 0) {
                    continue;
                }

                $costPrice = $this->calculateRealisticCostPrice($sellPrice, (string) $product->category);
                $product->update(['cost_price' => $costPrice]);
                $stats['products_updated']++;
            }

            // 2. Backfill snapshot HPP pada order_items yang cost_price <= 0 atau subtotal_cost <= 0
            if (Schema::hasTable('order_items')) {
                $itemsToBackfill = OrderItem::query()
                    ->with('product')
                    ->where(function ($q) {
                        $q->whereNull('cost_price')
                            ->orWhere('cost_price', '<=', 0)
                            ->orWhereNull('subtotal_cost')
                            ->orWhere('subtotal_cost', '<=', 0);
                    })
                    ->get();

                foreach ($itemsToBackfill as $item) {
                    if (! $item->product) {
                        continue;
                    }

                    $unitCost = (float) $item->cost_price > 0
                        ? (float) $item->cost_price
                        : (float) $item->product->cost_price;

                    if ($unitCost <= 0 && (float) $item->product->price > 0) {
                        $unitCost = $this->calculateRealisticCostPrice(
                            (float) $item->product->price,
                            (string) $item->product->category
                        );
                    }

                    if ($unitCost <= 0) {
                        continue;
                    }

                    $item->update([
                        'cost_price' => $unitCost,
                        'subtotal_cost' => round($unitCost * (int) $item->qty, 2),
                    ]);
                    $stats['order_items_updated']++;
                }
            }

            // 3. Perkaya variasi transaksi SELESAI di berbagai periode (Harian, Mingguan, Bulanan, Tahunan)
            if (Schema::hasTable('orders') && Schema::hasTable('order_items')) {
                $stats['orders_inserted'] = $this->seedMultiPeriodOrders();
            }

            // 4. Isi catatan OPEX realistis tersebar harian, mingguan, dan bulanan sepanjang tahun berjalan
            if (Schema::hasTable('operating_expenses') && Schema::hasTable('orders')) {
                $stats['opex_inserted'] = $this->seedMultiPeriodOperatingExpenses();
            }
        });

        if ($this->command) {
            $this->command->info(sprintf(
                'Financial enrichment selesai: %d produk diperbarui, %d item dibackfill HPP, %d pesanan SELESAI ditambahkan, %d catatan OPEX ditambahkan.',
                $stats['products_updated'],
                $stats['order_items_updated'],
                $stats['orders_inserted'],
                $stats['opex_inserted']
            ));
        }
    }

    private function calculateRealisticCostPrice(float $sellPrice, string $category): float
    {
        $ratio = self::CATEGORY_COST_RATIOS[$category] ?? 0.78;
        $raw = $sellPrice * $ratio;
        $step = $sellPrice < 5000 ? 50 : 100;
        $rounded = (float) (round($raw / $step) * $step);

        $minCost = min($sellPrice > 50 ? 50.0 : 1.0, $sellPrice - 1.0);
        $maxCost = max($minCost, $sellPrice - $step);

        return max($minCost, min($maxCost, $rounded));
    }

    private function deterministicRand(int &$seed, int $min, int $max): int
    {
        $seed = ($seed * 1103515245 + 12345) & 0x7FFFFFFF;
        if ($min >= $max) {
            return $min;
        }

        return $min + ($seed % ($max - $min + 1));
    }

    private function seedMultiPeriodOrders(): int
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->where('cost_price', '>', 0)
            ->orderBy('id')
            ->get();

        if ($products->isEmpty()) {
            return 0;
        }

        $members = User::query()->where('role', 'member')->orderBy('id')->get();
        $adminId = User::query()->where('role', 'admin')->orderBy('id')->value('id') ?? $members->first()?->id;

        if (! $adminId) {
            return 0;
        }

        $today = now()->startOfDay();
        $year = (int) $today->year;
        $inserted = 0;

        // A. Bulan-bulan sebelumnya dalam tahun berjalan (Januari s.d. bulan sebelum bulan ini)
        for ($month = 1; $month < (int) $today->month; $month++) {
            $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $existingMonthCount = Order::query()
                ->where('status', 'SELESAI')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->count();

            if ($existingMonthCount >= 10) {
                continue;
            }

            // Target variasi musiman koperasi (12 - 18 transaksi per bulan)
            $seed = crc32("month-{$year}-{$month}") & 0x7FFFFFFF;
            $targetMonthTx = $this->deterministicRand($seed, 12, 18);
            $needed = max(0, $targetMonthTx - $existingMonthCount);
            $daysInMonth = $monthStart->daysInMonth;

            for ($i = 0; $i < $needed; $i++) {
                // Sebar tanggal di minggu 1..4 dengan kecenderungan hari kerja
                $day = min($daysInMonth, 1 + (int) floor(($i * $daysInMonth) / max(1, $needed)) + ($i % 2));
                $txDate = Carbon::create($year, $month, $day);

                if ($this->createSeededOrder($txDate, $seed, $products, $members, (int) $adminId)) {
                    $inserted++;
                }
            }
        }

        // B. 28 hari terakhir hingga hari ini (mencakup Harian hari ini & 7 hari terakhir + Mingguan 4 minggu terakhir + Bulan berjalan)
        for ($daysAgo = 27; $daysAgo >= 0; $daysAgo--) {
            $targetDate = $today->copy()->subDays($daysAgo);
            $dateStr = $targetDate->toDateString();
            $seed = crc32("day-{$dateStr}") & 0x7FFFFFFF;

            $isWeekend = $targetDate->isWeekend();
            // 7 hari terakhir: 3-5 transaksi di hari kerja, 2-3 di akhir pekan; minggu sebelumnya 2-4 hari kerja, 1-2 akhir pekan
            if ($daysAgo <= 6) {
                $targetCount = $isWeekend
                    ? $this->deterministicRand($seed, 2, 3)
                    : $this->deterministicRand($seed, 3, 5);
            } else {
                $targetCount = $isWeekend
                    ? $this->deterministicRand($seed, 1, 2)
                    : $this->deterministicRand($seed, 2, 4);
            }

            $existingDayCount = Order::query()
                ->where('status', 'SELESAI')
                ->whereDate('transaction_date', $dateStr)
                ->count();

            $toCreate = max(0, $targetCount - $existingDayCount);
            for ($k = 0; $k < $toCreate; $k++) {
                if ($this->createSeededOrder($targetDate, $seed, $products, $members, (int) $adminId)) {
                    $inserted++;
                }
            }
        }

        return $inserted;
    }

    private function createSeededOrder(
        Carbon $date,
        int &$seed,
        $products,
        $members,
        int $adminId
    ): bool {
        // Jam operasional koperasi realistis 07:30 - 16:30 (ramai pagi & istirahat siang)
        $peakHours = [8, 9, 10, 12, 12, 13, 14, 15];
        $hour = $peakHours[$this->deterministicRand($seed, 0, count($peakHours) - 1)];
        $minute = $this->deterministicRand($seed, 0, 59);
        $second = $this->deterministicRand($seed, 0, 59);

        $txTime = $date->copy()->setTime($hour, $minute, $second);
        // Pastikan transaksi hari ini tidak melewati waktu sekarang
        if ($txTime->isFuture()) {
            $txTime = now()->subMinutes($this->deterministicRand($seed, 5, 180));
        }

        $dateCompact = $txTime->format('Ymd');
        $seq = 9001;
        do {
            $orderCode = sprintf('PLM-%s-%04d', $dateCompact, $seq);
            $seq++;
        } while (Order::where('order_code', $orderCode)->exists());

        $isWeb = $members->isNotEmpty() && $this->deterministicRand($seed, 1, 100) <= 55;
        $source = $isWeb ? 'web' : 'manual_admin';

        if ($isWeb) {
            $member = $members[$this->deterministicRand($seed, 0, $members->count() - 1)];
            $userId = $member->id;
            $customerName = $member->name;
            $pickupCode = sprintf('%04d', $this->deterministicRand($seed, 1000, 9999));
        } else {
            $userId = $adminId;
            $customerName = self::WALKIN_CUSTOMERS[$this->deterministicRand($seed, 0, count(self::WALKIN_CUSTOMERS) - 1)];
            $pickupCode = null;
        }

        $itemCount = $this->deterministicRand($seed, 1, min(4, $products->count()));
        $pickedIndices = [];
        $preparedItems = [];
        $totalAmount = 0.0;

        for ($p = 0; $p < $itemCount; $p++) {
            $idx = $this->deterministicRand($seed, 0, $products->count() - 1);
            $attempts = 0;
            while (isset($pickedIndices[$idx]) && $attempts < 10) {
                $idx = ($idx + 1) % $products->count();
                $attempts++;
            }
            $pickedIndices[$idx] = true;

            $product = $products[$idx];
            $qty = $this->deterministicRand($seed, 1, 3);
            $unitPrice = (float) $product->price;
            $costPrice = (float) $product->cost_price;
            $subtotal = round($unitPrice * $qty, 2);
            $subtotalCost = round($costPrice * $qty, 2);
            $totalAmount += $subtotal;

            $preparedItems[] = [
                'product_id' => $product->id,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'cost_price' => $costPrice,
                'subtotal_cost' => $subtotalCost,
            ];
        }

        $order = Order::create([
            'order_code' => $orderCode,
            'pickup_code' => $pickupCode,
            'user_id' => $userId,
            'customer_name' => $customerName,
            'source' => $source,
            'status' => 'SELESAI',
            'total_amount' => $totalAmount,
            'expires_at' => null,
            'paid_at' => $txTime,
            'transaction_date' => $txTime,
        ]);

        // Set created_at/updated_at konsisten dengan waktu historis
        DB::table('orders')->where('id', $order->id)->update([
            'created_at' => $txTime,
            'updated_at' => $txTime,
        ]);

        foreach ($preparedItems as $item) {
            $order->items()->create($item);
        }

        return true;
    }

    private function seedMultiPeriodOperatingExpenses(): int
    {
        $today = now()->startOfDay();
        $year = (int) $today->year;
        $adminId = User::query()->where('role', 'admin')->orderBy('id')->value('id');
        $inserted = 0;

        for ($month = 1; $month <= (int) $today->month; $month++) {
            $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $monthEnd = ($month === (int) $today->month)
                ? $today->copy()->endOfDay()
                : $monthStart->copy()->endOfMonth();

            $omzet = (float) Order::query()
                ->where('status', 'SELESAI')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('total_amount');

            if ($omzet <= 0) {
                continue;
            }

            $hpp = (float) OrderItem::query()
                ->whereHas('order', function ($q) use ($monthStart, $monthEnd) {
                    $q->where('status', 'SELESAI')
                        ->whereBetween('transaction_date', [$monthStart, $monthEnd]);
                })
                ->sum('subtotal_cost');

            $grossProfit = max(0.0, $omzet - $hpp);
            $existingOpexTotal = (float) OperatingExpense::query()
                ->whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('amount');

            // Jaga agar total OPEX sebulan tetap proporsional (~45%-55% dari Laba Kotor)
            $budgetCeiling = round(($grossProfit * 0.55) / 500) * 500;
            $remainingBudget = $budgetCeiling - $existingOpexTotal;

            if ($remainingBudget < 5000) {
                continue;
            }

            $ym = $monthStart->format('Ym');
            $isCurrentMonth = ($month === (int) $today->month);

            $templates = $isCurrentMonth
                ? [
                    [
                        'expense_code' => "OPX-{$ym}-901",
                        'name' => 'Internet / WiFi Kasir & Sistem Polmart',
                        'category' => 'utilitas',
                        'weight' => 0.72,
                        'date' => $monthStart->copy()->day(min(10, $today->day))->toDateString(),
                        'notes' => 'Langganan koneksi internet kasir koperasi.',
                    ],
                    [
                        'expense_code' => "OPX-{$ym}-902",
                        'name' => 'ATK Kasir, Pulpen & Isi Staples',
                        'category' => 'perlengkapan',
                        'weight' => 0.08,
                        'date' => $today->copy()->subDay()->toDateString(),
                        'notes' => 'Perlengkapan administrasi harian meja kasir.',
                    ],
                    [
                        'expense_code' => "OPX-{$ym}-903",
                        'name' => 'Air Galon & Konsumsi Petugas Piket',
                        'category' => 'lainnya',
                        'weight' => 0.09,
                        'date' => $today->toDateString(),
                        'notes' => 'Isi ulang galon air minum dan konsumsi petugas piket loket.',
                    ],
                ]
                : [
                    [
                        'expense_code' => "OPX-{$ym}-901",
                        'name' => 'Listrik & Air Koperasi',
                        'category' => 'utilitas',
                        'weight' => 0.32,
                        'date' => $monthStart->copy()->day(3)->toDateString(),
                        'notes' => 'Pembayaran rutin listrik showcase dan air bersih bulanan.',
                    ],
                    [
                        'expense_code' => "OPX-{$ym}-902",
                        'name' => 'Internet / WiFi Kasir Polmart',
                        'category' => 'utilitas',
                        'weight' => 0.20,
                        'date' => $monthStart->copy()->day(8)->toDateString(),
                        'notes' => 'Biaya koneksi internet dan sistem QRIS koperasi.',
                    ],
                    [
                        'expense_code' => "OPX-{$ym}-903",
                        'name' => 'Kantong Plastik & Kertas Struk Thermal',
                        'category' => 'perlengkapan',
                        'weight' => 0.18,
                        'date' => $monthStart->copy()->day(14)->toDateString(),
                        'notes' => 'Pengadaan kantong belanja dan gulungan kertas struk.',
                    ],
                    [
                        'expense_code' => "OPX-{$ym}-904",
                        'name' => 'Transportasi & Ongkos Kulakan Stok',
                        'category' => 'transportasi',
                        'weight' => 0.16,
                        'date' => $monthStart->copy()->day(19)->toDateString(),
                        'notes' => 'Biaya antar-jemput kulakan barang dagangan ke distributor.',
                    ],
                    [
                        'expense_code' => "OPX-{$ym}-905",
                        'name' => 'Kebersihan & Perawatan Etalase',
                        'category' => 'pemeliharaan',
                        'weight' => 0.14,
                        'date' => $monthStart->copy()->day(24)->toDateString(),
                        'notes' => 'Pembersihan rak pajangan dan perawatan rutin chiller.',
                    ],
                ];

            foreach ($templates as $row) {
                if (OperatingExpense::where('expense_code', $row['expense_code'])->exists()) {
                    continue;
                }

                $amount = max(2000.0, round(($remainingBudget * $row['weight']) / 500) * 500);
                if ($amount <= 0) {
                    continue;
                }

                OperatingExpense::create([
                    'expense_code' => $row['expense_code'],
                    'name' => $row['name'],
                    'category' => $row['category'],
                    'amount' => $amount,
                    'expense_date' => $row['date'],
                    'user_id' => $adminId,
                    'notes' => $row['notes'],
                ]);

                $inserted++;
            }
        }

        return $inserted;
    }
}
