<x-app-layout>
    @section('title', 'Dashboard Admin - Polmart')
    @section('page_title', 'Dashboard Analitik')

    <div class="space-y-6">
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Welcome Banner -->
        <section class="polmart-banner flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="relative z-10 max-w-2xl">
                <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-xs font-bold tracking-wider uppercase mb-3">
                    ✦ POLMART MANAGEMENT PANEL
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Halo, {{ Auth::user()->name }}!
                </h1>
                <p class="text-blue-100 text-sm mt-2 leading-relaxed">
                    Kelola master inventaris barang, transaksi walk-in kasir, validasi kode pengambilan 4-digit, impor riwayat Excel, dan laporan berkala koperasi dalam satu dashboard terintegrasi.
                </p>

                <div class="flex flex-wrap gap-3 mt-5">
                    <a href="{{ route('admin.products.index') }}" class="px-4 py-2.5 bg-white text-blue-700 font-bold text-sm rounded-lg shadow hover:bg-blue-50 transition">
                        📦 Master Barang
                    </a>
                    <a href="{{ route('admin.manual-orders.create') }}" class="px-4 py-2.5 bg-white/15 border border-white/30 text-white font-semibold text-sm rounded-lg hover:bg-white/25 transition">
                        🛒 Kasir Walk-in
                    </a>
                    <a href="{{ route('admin.pickup.index') }}" class="px-4 py-2.5 bg-white/15 border border-white/30 text-white font-semibold text-sm rounded-lg hover:bg-white/25 transition">
                        🔢 Validasi Loket ({{ $stats['ready_pickup_count'] }})
                    </a>
                </div>
            </div>
            <div class="hidden lg:block text-7xl relative z-10 select-none">📊</div>
        </section>

        <!-- Quick Menu -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.products.index') }}" class="polmart-card block hover:border-blue-300">
                <div class="polmart-stat-icon blue mb-3">📦</div>
                <h3 class="font-bold text-slate-900">Master Barang (FR-03)</h3>
                <p class="text-xs text-slate-500 mt-1">CRUD barang, harga, status aktif, restock & mutasi stok.</p>
            </a>
            <a href="{{ route('admin.manual-orders.create') }}" class="polmart-card block hover:border-blue-300">
                <div class="polmart-stat-icon green mb-3">🛒</div>
                <h3 class="font-bold text-slate-900">Kasir Walk-in (FR-09)</h3>
                <p class="text-xs text-slate-500 mt-1">Input pesanan langsung di loket berstatus SELESAI.</p>
            </a>
            <a href="{{ route('admin.order-imports.index') }}" class="polmart-card block hover:border-blue-300">
                <div class="polmart-stat-icon purple mb-3">📊</div>
                <h3 class="font-bold text-slate-900">Impor Excel (FR-10)</h3>
                <p class="text-xs text-slate-500 mt-1">Migrasi transaksi lama tanpa memotong stok fisik.</p>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="polmart-card block hover:border-blue-300">
                <div class="polmart-stat-icon amber mb-3">📑</div>
                <h3 class="font-bold text-slate-900">Laporan & Ekspor (FR-12)</h3>
                <p class="text-xs text-slate-500 mt-1">Rekap harian–tahunan & export .xlsx multi-sheet.</p>
            </a>
        </section>

        <!-- Ringkasan Statistik -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Omzet (SELESAI)</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ number_format($stats['completed_orders_count']) }} transaksi selesai</p>
                </div>
                <div class="polmart-stat-icon green">💰</div>
            </div>

            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Siap Diambil (Loket)</p>
                    <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $stats['ready_pickup_count'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $stats['pending_payment_count'] }} menunggu bayar QRIS</p>
                </div>
                <div class="polmart-stat-icon blue">🔢</div>
            </div>

            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Master Barang</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total_products'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $stats['active_products'] }} aktif • {{ $stats['categories_count'] }} kategori</p>
                </div>
                <div class="polmart-stat-icon purple">📦</div>
            </div>

            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Peringatan Stok</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['low_stock_count'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $stats['out_of_stock_count'] }} produk stok habis (0)</p>
                </div>
                <div class="polmart-stat-icon amber">⚠</div>
            </div>
        </section>

        <!-- Validasi Loket Cepat & Antrean SIAP_DIAMBIL -->
        <section class="polmart-card">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Antrean Validasi Loket (SIAP_DIAMBIL)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Masukkan kode pengambilan 4-digit dari anggota untuk menyelesaikan pesanan.</p>
                </div>

                <form method="POST" action="{{ route('admin.pickup.verify-code') }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="pickup_code" maxlength="6" placeholder="Kode 4-digit (cth: 4829)" required
                           class="polmart-input w-48 font-mono font-bold tracking-wider">
                    <button type="submit" class="polmart-btn polmart-btn-primary whitespace-nowrap">
                        ✓ Verifikasi & Selesai
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto mt-4">
                <table class="polmart-table">
                    <thead>
                        <tr>
                            <th>Kode Pesanan</th>
                            <th>Kode Ambil</th>
                            <th>Pemesan</th>
                            <th>Item</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($readyPickupOrders as $order)
                            <tr>
                                <td class="font-mono font-bold text-blue-700">{{ $order->order_code }}</td>
                                <td>
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 font-mono font-extrabold tracking-widest">
                                        #{{ $order->pickup_code }}
                                    </span>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-900">{{ $order->customer_name }}</div>
                                    <div class="text-xs text-slate-400">{{ $order->user?->member_code ?? '-' }}</div>
                                </td>
                                <td class="text-xs text-slate-600">
                                    {{ $order->items->sum('qty') }} pcs ({{ $order->items->map(fn ($i) => $i->product?->name)->filter()->take(2)->implode(', ') }})
                                </td>
                                <td class="text-right font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('admin.pickup.complete', $order) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="polmart-btn polmart-btn-success polmart-btn-sm">
                                            ✓ Selesaikan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-slate-400 py-6">
                                    Tidak ada pesanan yang menunggu diambil di loket saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Analitik PRD FR-11: Barang Terlaris & Stok Terbanyak -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Barang Terlaris -->
            <div class="polmart-card">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Barang Terlaris (Top Selling)</h2>
                        <p class="text-xs text-slate-500">Agregasi SUM(qty) dari pesanan berstatus SELESAI</p>
                    </div>
                    <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-50 text-xs font-semibold">
                        <a href="{{ route('admin.dashboard', ['top_filter' => 'all']) }}"
                           class="px-3 py-1.5 rounded-md {{ $topFilter === 'all' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:text-slate-900' }}">
                            Sepanjang Waktu
                        </a>
                        <a href="{{ route('admin.dashboard', ['top_filter' => 'month']) }}"
                           class="px-3 py-1.5 rounded-md {{ $topFilter === 'month' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:text-slate-900' }}">
                            Bulan Ini
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="polmart-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Barang</th>
                                <th class="text-center">Terjual</th>
                                <th class="text-right">Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topSellingProducts as $idx => $item)
                                <tr>
                                    <td class="font-bold text-slate-400">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                                        <div class="text-xs text-slate-400 font-mono">{{ $item->sku }} • {{ $item->category }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="polmart-badge polmart-badge-blue">{{ number_format($item->total_sold) }} pcs</span>
                                    </td>
                                    <td class="text-right font-semibold">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-slate-400 py-6">
                                        Belum ada penjualan berstatus SELESAI pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Stok Terbanyak -->
            <div class="polmart-card">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Stok Terbanyak</h2>
                        <p class="text-xs text-slate-500">Barang aktif diurutkan dari stok terbesar (ORDER BY stock DESC)</p>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                        Kelola Stok →
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="polmart-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th class="text-right">Harga</th>
                                <th class="text-center">Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($highestStockProducts as $product)
                                <tr>
                                    <td>
                                        <div class="font-semibold text-slate-900">{{ $product->name }}</div>
                                        <div class="text-xs text-slate-400 font-mono">{{ $product->sku }}</div>
                                    </td>
                                    <td class="text-xs text-slate-600">{{ $product->category }}</td>
                                    <td class="text-right text-xs font-semibold">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if ($product->stock > 10)
                                            <span class="polmart-badge polmart-badge-green">{{ $product->stock }} pcs</span>
                                        @elseif ($product->stock > 0)
                                            <span class="polmart-badge polmart-badge-amber">{{ $product->stock }} pcs</span>
                                        @else
                                            <span class="polmart-badge polmart-badge-red">Habis</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-slate-400 py-6">Belum ada produk aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Transaksi & Mutasi Terkini -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="polmart-card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Transaksi Terbaru</h2>
                        <p class="text-xs text-slate-500">Pesanan web QRIS, walk-in, dan impor Excel</p>
                    </div>
                    <a href="{{ route('admin.transactions.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                        Semua →
                    </a>
                </div>
                <div class="space-y-3">
                    @forelse ($recentOrders as $order)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <div>
                                <div class="font-mono text-xs font-bold text-blue-700">{{ $order->order_code }}</div>
                                <div class="text-sm font-semibold text-slate-900">{{ $order->customer_name }}</div>
                                <div class="text-xs text-slate-400">
                                    {{ $order->transaction_date?->format('d/m/Y H:i') }} • {{ strtoupper($order->source) }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-sm text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                <span class="polmart-badge {{ $order->status === 'SELESAI' ? 'polmart-badge-green' : ($order->status === 'SIAP_DIAMBIL' ? 'polmart-badge-blue' : ($order->status === 'MENUNGGU_BAYAR' ? 'polmart-badge-amber' : 'polmart-badge-red')) }}">
                                    {{ $order->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-sm text-slate-400 py-6">Belum ada transaksi tercatat.</p>
                    @endforelse
                </div>
            </div>

            <div class="polmart-card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Log Mutasi Stok Terbaru</h2>
                        <p class="text-xs text-slate-500">Pergerakan stok masuk, keluar, dan pengembalian</p>
                    </div>
                    <a href="{{ route('admin.stock-mutations.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                        Semua →
                    </a>
                </div>
                <div class="space-y-3">
                    @forelse ($recentMutations as $mutation)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">{{ $mutation->product?->name ?? 'Produk' }}</div>
                                <div class="text-xs text-slate-500">{{ $mutation->notes ?? $mutation->reference ?? '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $mutation->created_at?->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="text-right">
                                <span class="polmart-badge {{ in_array($mutation->type, ['in', 'return'], true) ? 'polmart-badge-green' : 'polmart-badge-amber' }}">
                                    {{ strtoupper($mutation->type) }} {{ $mutation->qty }}
                                </span>
                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $mutation->stock_before }} → <strong>{{ $mutation->stock_after }}</strong>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-sm text-slate-400 py-6">Belum ada mutasi stok tercatat.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
