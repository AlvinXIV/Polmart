<x-app-layout>
    @section('title', 'Laporan & Export Excel - Polmart')
    @section('page_title', 'Laporan & Export Excel')

    <div class="space-y-6" x-data="{ period: '{{ $period }}' }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Laporan Berkala & Export Excel (FR-12)</h1>
                <p class="text-sm text-slate-500 mt-1">Rekap omzet, transaksi berstatus SELESAI, dan barang terlaris beserta ekspor .xlsx multi-sheet.</p>
            </div>
            <a href="{{ route('admin.reports.export', request()->query()) }}"
               class="polmart-btn polmart-btn-success whitespace-nowrap">
                ↓ Export Excel (.xlsx) Multi-Sheet
            </a>
        </div>

        <!-- Filter Periode -->
        <section class="polmart-card">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label for="period" class="polmart-label">Jenis Periode</label>
                    <select id="period" name="period" x-model="period" class="polmart-select">
                        <option value="daily">Harian</option>
                        <option value="weekly">Pekanan (Rentang Tanggal)</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>

                <div x-show="period === 'daily'">
                    <label for="date" class="polmart-label">Tanggal</label>
                    <input id="date" name="date" type="date" value="{{ $date }}" class="polmart-input">
                </div>

                <div x-show="period === 'weekly'">
                    <label for="start_date" class="polmart-label">Dari Tanggal</label>
                    <input id="start_date" name="start_date" type="date" value="{{ $startDate }}" class="polmart-input">
                </div>

                <div x-show="period === 'weekly'">
                    <label for="end_date" class="polmart-label">Sampai Tanggal</label>
                    <input id="end_date" name="end_date" type="date" value="{{ $endDate }}" class="polmart-input">
                </div>

                <div x-show="period === 'monthly'">
                    <label for="month" class="polmart-label">Bulan</label>
                    <select id="month" name="month" class="polmart-select">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div x-show="period === 'monthly' || period === 'yearly'">
                    <label for="year" class="polmart-label">Tahun</label>
                    <input id="year" name="year" type="number" min="2000" max="2100" value="{{ $year }}" class="polmart-input">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="polmart-btn polmart-btn-primary">Tampilkan Laporan</button>
                </div>
            </form>

            <div class="mt-3 text-xs text-slate-500">
                Periode Aktif: <strong class="text-slate-900">{{ $periodLabel }}</strong> (Hanya menghitung transaksi berstatus <strong>SELESAI</strong>)
            </div>
        </section>

        <!-- Ringkasan Statistik -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total Omzet</p>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1">Rp {{ number_format($summary['total_omzet'], 0, ',', '.') }}</p>
                </div>
                <div class="polmart-stat-icon green">💰</div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Jumlah Transaksi</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($summary['total_transactions']) }}</p>
                </div>
                <div class="polmart-stat-icon blue">🧾</div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total Item Terjual</p>
                    <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ number_format($summary['total_items_sold']) }} pcs</p>
                </div>
                <div class="polmart-stat-icon purple">📦</div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Rata-Rata Transaksi</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">Rp {{ number_format($summary['avg_transaction'], 0, ',', '.') }}</p>
                </div>
                <div class="polmart-stat-icon amber">📊</div>
            </div>
        </section>

        <!-- Distribusi Sumber & Barang Terlaris -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="polmart-card space-y-4">
                <h2 class="text-base font-extrabold text-slate-900">Sumber Transaksi</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Portal Anggota (QRIS)</span>
                        <strong class="text-blue-700">{{ $sourceCounts['web'] }} transaksi</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Kasir Walk-in</span>
                        <strong class="text-emerald-700">{{ $sourceCounts['manual_admin'] }} transaksi</strong>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <span class="text-slate-600">Impor Excel Historis</span>
                        <strong class="text-purple-700">{{ $sourceCounts['import_excel'] }} transaksi</strong>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 polmart-card">
                <h2 class="text-base font-extrabold text-slate-900 mb-3">Ringkasan Barang Terlaris (Sheet 2)</h2>
                <div class="overflow-x-auto">
                    <table class="polmart-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>SKU</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th class="text-center">Terjual</th>
                                <th class="text-right">Total Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProducts as $idx => $item)
                                <tr>
                                    <td class="font-bold text-slate-400">{{ $idx + 1 }}</td>
                                    <td class="font-mono text-xs">{{ $item->sku }}</td>
                                    <td class="font-semibold">{{ $item->name }}</td>
                                    <td class="text-xs text-slate-600">{{ $item->category }}</td>
                                    <td class="text-center">
                                        <span class="polmart-badge polmart-badge-blue">{{ number_format($item->total_sold) }} pcs</span>
                                    </td>
                                    <td class="text-right font-semibold">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-slate-400 py-6">Tidak ada penjualan pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Rekap Transaksi Periode -->
        <section class="polmart-card">
            <h2 class="text-base font-extrabold text-slate-900 mb-3">Rekap Transaksi Lengkap (Sheet 1)</h2>
            <div class="overflow-x-auto">
                <table class="polmart-table">
                    <thead>
                        <tr>
                            <th>Kode Pesanan</th>
                            <th>Tanggal Transaksi</th>
                            <th>Pemesan</th>
                            <th>Sumber</th>
                            <th class="text-center">Total Item</th>
                            <th class="text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td class="font-mono font-bold text-blue-700">{{ $order->order_code }}</td>
                                <td class="text-xs text-slate-600">{{ $order->transaction_date?->format('d/m/Y H:i') }}</td>
                                <td class="font-semibold">{{ $order->customer_name }}</td>
                                <td class="text-xs">
                                    {{ match($order->source) {
                                        'web' => 'Portal Anggota (QRIS)',
                                        'manual_admin' => 'Kasir Walk-in',
                                        'import_excel' => 'Impor Excel',
                                        default => $order->source,
                                    } }}
                                </td>
                                <td class="text-center">{{ $order->items->sum('qty') }}</td>
                                <td class="text-right font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-slate-400 py-6">Belum ada transaksi selesai pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
