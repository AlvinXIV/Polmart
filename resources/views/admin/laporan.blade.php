<x-app-layout>
    @section('title', 'Laporan & Export Excel - Polmart')
    @section('page_title', 'Laporan & Export Excel')

    <div class="space-y-6" x-data="{ period: '{{ $period }}', chartMetric: 'omzet' }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Laporan Berkala & Export Excel</h1>
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
                    <input id="date" name="date" type="date" value="{{ $date }}" :disabled="period !== 'daily'" class="polmart-input">
                </div>

                <div x-show="period === 'weekly'">
                    <label for="start_date" class="polmart-label">Dari Tanggal</label>
                    <input id="start_date" name="start_date" type="date" value="{{ $startDate }}" :disabled="period !== 'weekly'" class="polmart-input">
                </div>

                <div x-show="period === 'weekly'">
                    <label for="end_date" class="polmart-label">Sampai Tanggal</label>
                    <input id="end_date" name="end_date" type="date" value="{{ $endDate }}" :disabled="period !== 'weekly'" class="polmart-input">
                </div>

                <div x-show="period === 'monthly'">
                    <label for="month" class="polmart-label">Bulan</label>
                    <select id="month" name="month" :disabled="period !== 'monthly'" class="polmart-select">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div x-show="period === 'monthly' || period === 'yearly'">
                    <label for="year" class="polmart-label">Tahun</label>
                    <input id="year" name="year" type="number" min="2000" max="2100" value="{{ $year }}" :disabled="period !== 'monthly' && period !== 'yearly'" class="polmart-input">
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

        <!-- Grafik Tren Penjualan & Transaksi -->
        <section class="polmart-card" x-data="{ chartMetric: 'omzet' }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">
                        Grafik Tren Penjualan (Per {{ $chartData['unit_name'] }})
                    </h2>
                    <p class="text-xs text-slate-500">Visualisasi performa omzet dan volume transaksi sepanjang {{ strtolower($periodLabel) }}</p>
                </div>
                <!-- Toggle Metrik Omzet vs Transaksi -->
                <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-50 text-xs font-semibold">
                    <button type="button" @click="chartMetric = 'omzet'"
                            :class="chartMetric === 'omzet' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3 py-1.5 rounded-md transition">
                        Omzet (Rp)
                    </button>
                    <button type="button" @click="chartMetric = 'count'"
                            :class="chartMetric === 'count' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3 py-1.5 rounded-md transition">
                        Jumlah Transaksi
                    </button>
                </div>
            </div>

            @if ($chartData['has_data'])
                @php
                    $chartMaxHeightPx = 180;
                    $minActivePx = 8;
                    $zeroBasePx = 4;
                    $maxOmzet = (float) ($chartData['max_omzet'] ?? 1);
                    $maxCount = (int) ($chartData['max_count'] ?? 1);
                @endphp
                <div class="relative pt-10 pb-2">
                    <div class="w-full overflow-x-auto">
                        <div class="min-w-full w-max flex items-end gap-1 sm:gap-2 px-1 border-b border-slate-200">
                            @foreach ($chartData['series'] as $pt)
                                @php
                                    $valOmzet = (float) ($pt['omzet'] ?? 0);
                                    $valCount = (int) ($pt['count'] ?? 0);

                                    $hOmzetPx = ($maxOmzet > 0 && $valOmzet > 0)
                                        ? max($minActivePx, (int) round(($valOmzet / $maxOmzet) * $chartMaxHeightPx))
                                        : $zeroBasePx;

                                    $hCountPx = ($maxCount > 0 && $valCount > 0)
                                        ? max($minActivePx, (int) round(($valCount / $maxCount) * $chartMaxHeightPx))
                                        : $zeroBasePx;
                                @endphp
                                <div class="group relative flex-1 min-w-[20px] sm:min-w-[28px] flex flex-col items-center justify-end">
                                    <!-- Tooltip Hover -->
                                    <div class="opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-150 absolute bottom-full mb-2 z-30 px-2.5 py-1.5 rounded-lg bg-slate-900 text-white text-[11px] font-medium shadow-xl whitespace-nowrap">
                                        <div class="font-bold text-blue-300">{{ $pt['label'] }}</div>
                                        <div>Omzet: <strong>Rp {{ number_format($valOmzet, 0, ',', '.') }}</strong></div>
                                        <div>Transaksi: <strong>{{ number_format($valCount) }} pesanan</strong></div>
                                    </div>

                                    <!-- Area Batang (Track) dengan tinggi eksplisit dalam pixel -->
                                    <div class="w-full flex items-end justify-center" style="height: {{ $chartMaxHeightPx }}px;">
                                        <!-- Bar Omzet -->
                                        <div class="w-full rounded-t-md transition-all duration-300 cursor-pointer"
                                             x-show="chartMetric === 'omzet'"
                                             title="{{ $pt['label'] }} - Omzet: Rp {{ number_format($valOmzet, 0, ',', '.') }} ({{ number_format($valCount) }} transaksi)"
                                             style="height: {{ $hOmzetPx }}px; background: {{ $valOmzet > 0 ? 'linear-gradient(180deg, #3b82f6 0%, #1d4ed8 100%)' : '#e2e8f0' }};">
                                        </div>

                                        <!-- Bar Jumlah Transaksi -->
                                        <div class="w-full rounded-t-md transition-all duration-300 cursor-pointer"
                                             x-show="chartMetric === 'count'"
                                             title="{{ $pt['label'] }} - Transaksi: {{ number_format($valCount) }} pesanan (Omzet: Rp {{ number_format($valOmzet, 0, ',', '.') }})"
                                             style="height: {{ $hCountPx }}px; background: {{ $valCount > 0 ? 'linear-gradient(180deg, #10b981 0%, #047857 100%)' : '#e2e8f0' }};">
                                        </div>
                                    </div>

                                    <!-- Label Sumbu X (Tetap terlihat dan rapi di bawah batang) -->
                                    <span class="text-[10px] text-slate-500 mt-2 truncate w-full text-center select-none font-mono">
                                        {{ $pt['short_label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-500 mt-3 pt-2">
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center gap-1.5 cursor-pointer" @click="chartMetric = 'omzet'">
                            <span class="w-2.5 h-2.5 rounded-sm bg-blue-600 inline-block shadow-sm"></span>
                            <span :class="chartMetric === 'omzet' ? 'font-bold text-slate-900' : 'text-slate-500'">Tren Omzet</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 cursor-pointer" @click="chartMetric = 'count'">
                            <span class="w-2.5 h-2.5 rounded-sm bg-emerald-600 inline-block shadow-sm"></span>
                            <span :class="chartMetric === 'count' ? 'font-bold text-slate-900' : 'text-slate-500'">Jumlah Transaksi</span>
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-400">*Arahkan kursor pada batang grafik untuk melihat rincian tanggal, omzet, dan transaksi</span>
                </div>
            @else
                <div class="h-56 flex flex-col items-center justify-center text-center p-6 border border-dashed border-slate-200 rounded-xl">
                    <span class="text-3xl mb-2">📉</span>
                    <p class="text-sm font-semibold text-slate-700">Tidak ada transaksi SELESAI pada periode ini.</p>
                    <p class="text-xs text-slate-400 mt-1">Pilih tanggal atau rentang periode lain untuk memuat data penjualan.</p>
                </div>
            @endif
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
