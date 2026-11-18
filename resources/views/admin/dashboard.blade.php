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
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Halo, {{ Auth::user()->name }}!
                </h1>

                <div class="flex flex-wrap gap-3 mt-5">
                    <a href="{{ route('admin.products.index') }}" class="px-4 py-2.5 bg-white text-blue-700 font-bold text-sm rounded-lg shadow hover:bg-blue-50 transition inline-flex items-center gap-2">
                        <x-icon name="package" class="w-4 h-4" />
                        <span>Master Barang</span>
                    </a>
                    <a href="{{ route('admin.manual-orders.create') }}" class="px-4 py-2.5 bg-white/15 border border-white/30 text-white font-semibold text-sm rounded-lg hover:bg-white/25 transition inline-flex items-center gap-2">
                        <x-icon name="shopping-cart" class="w-4 h-4" />
                        <span>Kasir Walk-in</span>
                    </a>
                    <a href="{{ route('admin.pickup.index') }}" class="px-4 py-2.5 bg-white/15 border border-white/30 text-white font-semibold text-sm rounded-lg hover:bg-white/25 transition inline-flex items-center gap-2">
                        <x-icon name="check-circle" class="w-4 h-4" />
                        <span>Validasi Loket ({{ $stats['ready_pickup_count'] }})</span>
                    </a>
                </div>
            </div>
            <div class="hidden lg:flex items-center justify-center w-20 h-20 rounded-2xl bg-white/15 text-white relative z-10 select-none">
                <x-icon name="bar-chart-3" class="w-10 h-10" />
            </div>
        </section>

        <!-- =========================================================================
             SECTION: ANALISIS & LAPORAN PENJUALAN TERPADU DASHBOARD ADMIN
             ========================================================================= -->
        <section class="polmart-card space-y-6" id="sales-analytics" x-data="{
            currentPeriod: '{{ $period }}',
            chartMetric: 'omzet'
        }">
            <!-- Header Fitur Analisis & Export Excel -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-xs text-slate-500 font-medium">
                            Periode Aktif: <strong class="text-blue-700">{{ $periodLabel }}</strong>
                        </span>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900">Analisis Penjualan &amp; Laporan Berkala Koperasi</h2>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.reports.export', $exportParams ?? ['period' => $period]) }}"
                       class="polmart-btn polmart-btn-success whitespace-nowrap shadow-sm hover:shadow transition"
                       title="Export laporan {{ $periodLabel }} ke format Excel (.xlsx)">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export Excel (.xlsx)
                    </a>
                </div>
            </div>

            <!-- Filter Bar Periode (Harian, Mingguan, Bulanan, Tahunan) -->
            <div class="p-4 rounded-xl bg-slate-50/90 border border-slate-200">
                <form method="GET" action="{{ route('admin.dashboard') }}#sales-analytics" class="space-y-4">
                    <div class="flex flex-wrap items-center" style="gap: 10px;">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider mr-1">Pilih Periode:</span>
                        <input type="hidden" name="period" :value="currentPeriod">
                        <button type="button" @click="currentPeriod = 'daily'"
                                :class="currentPeriod === 'daily' ? 'bg-blue-600 text-white font-bold shadow' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                style="padding: 8px 16px;"
                                class="text-xs rounded-lg transition flex items-center gap-1.5">
                            <x-icon name="calendar" class="w-4 h-4" /> Harian
                        </button>
                        <button type="button" @click="currentPeriod = 'weekly'"
                                :class="currentPeriod === 'weekly' ? 'bg-blue-600 text-white font-bold shadow' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                style="padding: 8px 16px;"
                                class="text-xs rounded-lg transition flex items-center gap-1.5">
                            <x-icon name="calendar-days" class="w-4 h-4" /> Mingguan
                        </button>
                        <button type="button" @click="currentPeriod = 'monthly'"
                                :class="currentPeriod === 'monthly' ? 'bg-blue-600 text-white font-bold shadow' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                style="padding: 8px 16px;"
                                class="text-xs rounded-lg transition flex items-center gap-1.5">
                            <x-icon name="bar-chart-3" class="w-4 h-4" /> Bulanan
                        </button>
                        <button type="button" @click="currentPeriod = 'yearly'"
                                :class="currentPeriod === 'yearly' ? 'bg-blue-600 text-white font-bold shadow' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                style="padding: 8px 16px;"
                                class="text-xs rounded-lg transition flex items-center gap-1.5">
                            <x-icon name="trending-up" class="w-4 h-4" /> Tahunan
                        </button>
                    </div>

                    <!-- Contextual Input Sesuai Periode yang Dipilih (hanya input aktif yang disubmit) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 items-end pt-1">
                        <!-- Harian -->
                        <div x-show="currentPeriod === 'daily'" class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Tanggal</label>
                            <input type="date" name="date" value="{{ $date }}" :disabled="currentPeriod !== 'daily'" class="polmart-input text-xs py-2">
                        </div>

                        <!-- Mingguan -->
                        <div x-show="currentPeriod === 'weekly'" class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                            <input type="date" name="start_date" value="{{ $startDateInput }}" :disabled="currentPeriod !== 'weekly'" class="polmart-input text-xs py-2">
                        </div>
                        <div x-show="currentPeriod === 'weekly'" class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                            <input type="date" name="end_date" value="{{ $endDateInput }}" :disabled="currentPeriod !== 'weekly'" class="polmart-input text-xs py-2">
                        </div>

                        <!-- Bulanan -->
                        <div x-show="currentPeriod === 'monthly'" class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Bulan</label>
                            <select name="month" :disabled="currentPeriod !== 'monthly'" class="polmart-select text-xs py-2">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div x-show="currentPeriod === 'monthly' || currentPeriod === 'yearly'" class="sm:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tahun</label>
                            <input type="number" name="year" min="2000" max="2100" value="{{ $year }}" :disabled="currentPeriod !== 'monthly' && currentPeriod !== 'yearly'" class="polmart-input text-xs py-2">
                        </div>

                        <!-- Tombol Aksi Filter -->
                        <div class="flex items-center gap-2 sm:col-span-2">
                            <button type="submit" class="polmart-btn polmart-btn-primary polmart-btn-sm whitespace-nowrap">
                                <x-icon name="search" class="w-4 h-4" />
                                <span>Tampilkan Analisis</span>
                            </button>
                            <a href="{{ route('admin.dashboard') }}#sales-analytics" class="polmart-btn polmart-btn-secondary polmart-btn-sm whitespace-nowrap" title="Reset ke Bulan Berjalan">
                                <x-icon name="rotate-ccw" class="w-4 h-4" />
                                <span>Reset</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Ringkasan Finansial: HPP & Laba Kotor -->
            <div>
                <h3 class="text-sm font-extrabold text-slate-900 mb-3">Ringkasan Finansial (HPP &amp; Laba Kotor)</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div class="polmart-stat-card" style="padding: 16px;">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Total Omzet</p>
                            <p class="text-xl font-extrabold text-emerald-900 mt-1">
                                Rp {{ number_format($periodSummary['total_omzet'], 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] text-emerald-600 mt-0.5">
                                {{ number_format($periodSummary['total_transactions']) }} transaksi selesai
                            </p>
                        </div>
                    </div>

                    <div class="polmart-stat-card" style="padding: 16px;">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">HPP Tercatat</p>
                            <p class="text-xl font-extrabold text-rose-900 mt-1">
                                Rp {{ number_format($periodSummary['total_hpp'], 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] text-rose-600 mt-0.5">
                                Snapshot modal barang terjual
                            </p>
                        </div>
                    </div>

                    <div class="polmart-stat-card" style="padding: 16px;">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Laba Kotor</p>
                            <p class="text-xl font-extrabold text-blue-900 mt-1">
                                Rp {{ number_format($periodSummary['gross_profit'], 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] text-blue-600 mt-0.5">
                                @if ($periodSummary['has_unrecorded_hpp'])
                                    <span class="text-amber-700 font-semibold">*Provisional (Omzet - HPP)</span>
                                @else
                                    <span>Omzet dikurangi HPP Tercatat</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="polmart-stat-card" style="padding: 16px;">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Gross Profit Margin</p>
                            <p class="text-xl font-extrabold text-indigo-900 mt-1">
                                {{ $periodSummary['gpm'] !== null ? $periodSummary['gpm'] . '%' : '—' }}
                            </p>
                            <p class="text-[10px] text-indigo-600 mt-0.5">
                                @if ($periodSummary['has_unrecorded_hpp'] && $periodSummary['gpm'] !== null)
                                    <span class="text-amber-700 font-semibold">*Provisional (Laba / Omzet)</span>
                                @else
                                    <span>(Laba Kotor / Total Omzet)</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="polmart-stat-card" style="padding: 16px;">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Markup Penjualan</p>
                            <p class="text-xl font-extrabold text-amber-900 mt-1">
                                {{ $periodSummary['markup'] !== null ? $periodSummary['markup'] . '%' : '—' }}
                            </p>
                            <p class="text-[10px] text-amber-600 mt-0.5">
                                @if ($periodSummary['has_unrecorded_hpp'] && $periodSummary['markup'] !== null)
                                    <span class="text-amber-700 font-semibold">*Provisional (Laba / HPP)</span>
                                @else
                                    <span>(Laba Kotor / HPP Tercatat)</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6 Kartu Ringkasan Penjualan & Operasional (Horizontal, Tanpa Duplikasi) -->
            <div class="polmart-stats-horizontal">
                <div class="polmart-stat-card">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold text-blue-700 uppercase tracking-wider truncate">Jumlah Transaksi</p>
                        <p class="text-xl font-extrabold text-blue-900 mt-1">
                            {{ number_format($periodSummary['total_transactions']) }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                            Pesanan status SELESAI
                        </p>
                    </div>
                    <div class="polmart-stat-icon blue"><x-icon name="receipt" class="w-5 h-5" /></div>
                </div>

                <div class="polmart-stat-card">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold text-purple-700 uppercase tracking-wider truncate">Produk Terjual</p>
                        <p class="text-xl font-extrabold text-purple-900 mt-1">
                            {{ number_format($periodSummary['total_items_sold']) }} <span class="text-xs font-semibold">pcs</span>
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                            Total unit fisik keluar
                        </p>
                    </div>
                    <div class="polmart-stat-icon purple"><x-icon name="package" class="w-5 h-5" /></div>
                </div>

                <div class="polmart-stat-card">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold text-amber-700 uppercase tracking-wider truncate">Rata-rata Nilai Transaksi</p>
                        <p class="text-xl font-extrabold text-amber-900 mt-1">
                            Rp {{ number_format($periodSummary['avg_transaction'], 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                            Rata-rata per pesanan
                        </p>
                    </div>
                    <div class="polmart-stat-icon amber"><x-icon name="bar-chart-3" class="w-5 h-5" /></div>
                </div>

                <div class="polmart-stat-card">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider truncate">Siap Diambil (Loket)</p>
                        <p class="text-xl font-extrabold text-blue-600 mt-1">{{ $stats['ready_pickup_count'] }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">{{ $stats['pending_payment_count'] }} menunggu QRIS</p>
                    </div>
                    <div class="polmart-stat-icon blue"><x-icon name="hash" class="w-5 h-5" /></div>
                </div>

                <div class="polmart-stat-card">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider truncate">Master Barang</p>
                        <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $stats['total_products'] }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">{{ $stats['active_products'] }} aktif • {{ $stats['categories_count'] }} kategori</p>
                    </div>
                    <div class="polmart-stat-icon purple"><x-icon name="tag" class="w-5 h-5" /></div>
                </div>

                <div class="polmart-stat-card">
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider truncate">Peringatan Stok</p>
                        <p class="text-xl font-extrabold text-amber-600 mt-1">{{ $stats['low_stock_count'] }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">{{ $stats['out_of_stock_count'] }} stok habis (0)</p>
                    </div>
                    <div class="polmart-stat-icon amber"><x-icon name="alert-triangle" class="w-5 h-5" /></div>
                </div>
            </div>

            <!-- Grafik Deret Waktu & Distribusi Sumber Transaksi -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Grafik Deret Waktu -->
                <div class="lg:col-span-2 p-5 rounded-xl border border-slate-200 bg-white">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">
                                Grafik Tren Penjualan (Per {{ $chartData['unit_name'] }})
                            </h3>
                            <p class="text-xs text-slate-500">Visualisasi performa penjualan sepanjang {{ strtolower($periodLabel) }}</p>
                        </div>
                        <!-- Toggle Metrik Omzet vs Transaksi -->
                        <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-50 text-xs font-semibold">
                            <button type="button" @click="chartMetric = 'omzet'"
                                    :class="chartMetric === 'omzet' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-3 py-1 rounded-md transition">
                                Omzet (Rp)
                            </button>
                            <button type="button" @click="chartMetric = 'count'"
                                    :class="chartMetric === 'count' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                    class="px-3 py-1 rounded-md transition">
                                Transaksi
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
                                        <div class="group relative flex-1 min-w-[20px] sm:min-w-[26px] flex flex-col items-center justify-end">
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

                                            <!-- Label Sumbu X -->
                                            <span class="text-[10px] text-slate-500 mt-2 truncate w-full text-center select-none font-mono">
                                                {{ $pt['short_label'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center text-center p-6 border border-dashed border-slate-200 rounded-xl" style="height: 224px;">
                            <div class="mb-2 text-slate-400"><x-icon name="trending-down" class="w-8 h-8" /></div>
                            <p class="text-sm font-semibold text-slate-700">Tidak ada transaksi SELESAI pada periode ini.</p>
                            <p class="text-xs text-slate-400 mt-1">Pilih tanggal atau rentang periode lain untuk memuat data penjualan.</p>
                        </div>
                    @endif
                </div>

                <!-- Distribusi Sumber Transaksi -->
                <div class="p-5 rounded-xl border border-slate-200 bg-white flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 mb-1">Distribusi Sumber Transaksi</h3>
                        <p class="text-xs text-slate-500 mb-4">Komposisi pesanan SELESAI berdasarkan channel penjualan</p>

                        @php
                            $totalSrc = $periodSourceCounts['web'] + $periodSourceCounts['manual_admin'];
                            $pctWeb = $totalSrc > 0 ? round(($periodSourceCounts['web'] / $totalSrc) * 100, 1) : 0;
                            $pctWalkin = $totalSrc > 0 ? round(($periodSourceCounts['manual_admin'] / $totalSrc) * 100, 1) : 0;
                        @endphp

                        <div class="space-y-4">
                            <!-- Portal Anggota (QRIS) -->
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-semibold text-slate-700 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                        Portal Anggota (QRIS)
                                    </span>
                                    <span class="font-bold text-slate-900">{{ $periodSourceCounts['web'] }} ({{ $pctWeb }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: {{ $pctWeb }}%"></div>
                                </div>
                            </div>

                            <!-- Kasir Walk-in -->
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-semibold text-slate-700 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        Kasir Walk-in
                                    </span>
                                    <span class="font-bold text-slate-900">{{ $periodSourceCounts['manual_admin'] }} ({{ $pctWalkin }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $pctWalkin }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Total Transaksi Selesai:</span>
                        <strong class="text-slate-900 font-bold">{{ number_format($totalSrc) }} pesanan</strong>
                    </div>
                </div>
            </div>

            <!-- Produk Paling Menguntungkan Berdasarkan Laba Kotor -->
            <div class="p-5 rounded-xl border border-slate-200 bg-white" x-data="{ showAllProfitable: false }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <span>Produk Paling Menguntungkan (Periode Aktif)</span>
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800">
                            Berdasarkan Laba Kotor
                        </span>
                    </h3>
                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-bold font-mono self-start sm:self-auto">
                        Top {{ count($periodProfitableProducts) }}
                    </span>
                </div>

                @php
                    $dashboardCompleteProfitable = $periodProfitableProducts->filter(fn ($p) => (int) ($p->unrecorded_items_count ?? 0) === 0);
                    $dashboardProvisionalProfitable = $periodProfitableProducts->filter(fn ($p) => (int) ($p->unrecorded_items_count ?? 0) > 0);
                @endphp

                @if ($dashboardCompleteProfitable->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="polmart-table text-xs">
                            <thead>
                                <tr>
                                    <th class="w-8">#</th>
                                    <th>Barang</th>
                                    <th class="text-center">Terjual</th>
                                    <th class="text-right">Total Omzet</th>
                                    <th class="text-right">HPP Tercatat</th>
                                    <th class="text-right">Laba Kotor</th>
                                    <th class="text-center">Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dashboardCompleteProfitable as $idx => $item)
                                    @php
                                        $itemRev = (float) ($item->total_revenue ?? 0);
                                        $itemCost = (float) ($item->total_cost ?? 0);
                                        $itemProfit = (float) ($item->total_profit ?? 0);
                                        $itemMargin = $itemRev > 0 ? round(($itemProfit / $itemRev) * 100, 1) : 0.0;
                                    @endphp
                                    <tr x-show="showAllProfitable || {{ $loop->index }} < 3">
                                        <td class="font-bold text-slate-400">{{ $idx + 1 }}</td>
                                        <td>
                                            <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                                            <div class="text-[11px] text-slate-400 font-mono">{{ $item->sku }} • {{ $item->category }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="polmart-badge polmart-badge-blue font-bold">
                                                {{ number_format($item->total_sold) }} pcs
                                            </span>
                                        </td>
                                        <td class="text-right font-medium text-slate-900">
                                            Rp {{ number_format($itemRev, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right font-medium text-rose-700">
                                            Rp {{ number_format($itemCost, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right font-bold text-emerald-700">
                                            Rp {{ number_format($itemProfit, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center font-bold text-blue-700">
                                            {{ $itemMargin }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if ($dashboardProvisionalProfitable->isNotEmpty())
                    <div class="{{ $dashboardCompleteProfitable->isNotEmpty() ? 'mt-4 pt-3 border-t border-slate-200' : '' }}">
                        <div class="overflow-x-auto">
                            <table class="polmart-table text-xs">
                                <thead>
                                    <tr>
                                        <th>Barang</th>
                                        <th class="text-center">Terjual</th>
                                        <th class="text-right">Omzet</th>
                                        <th class="text-right">HPP</th>
                                        <th class="text-right">Laba Kotor</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dashboardProvisionalProfitable as $item)
                                        @php
                                            $itemRev = (float) ($item->total_revenue ?? 0);
                                            $itemCost = (float) ($item->total_cost ?? 0);
                                            $itemProfit = (float) ($item->total_profit ?? 0);
                                        @endphp
                                        <tr x-show="showAllProfitable || {{ $loop->index }} < 3">
                                            <td>
                                                <div class="font-medium text-slate-800">{{ $item->name }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono">{{ $item->sku }}</div>
                                            </td>
                                            <td class="text-center">{{ number_format($item->total_sold) }} pcs</td>
                                            <td class="text-right">Rp {{ number_format($itemRev, 0, ',', '.') }}</td>
                                            <td class="text-right text-rose-700">Rp {{ number_format($itemCost, 0, ',', '.') }}</td>
                                            <td class="text-right font-semibold text-amber-700">Rp {{ number_format($itemProfit, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                    Provisional
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif ($dashboardCompleteProfitable->isEmpty())
                    <div class="p-4 text-center border border-dashed border-slate-200 rounded-xl">
                        <p class="text-sm font-semibold text-slate-600">Belum ada produk dengan data HPP lengkap pada periode ini.</p>
                    </div>
                @endif

                @if ($dashboardCompleteProfitable->count() > 3 || $dashboardProvisionalProfitable->count() > 3)
                    <div class="pt-3 mt-3 border-t border-slate-100 text-center">
                        <button type="button"
                                @click="showAllProfitable = !showAllProfitable"
                                class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline px-3 py-1.5 rounded-lg transition">
                            <span x-show="!showAllProfitable">Lihat Selengkapnya ({{ max($dashboardCompleteProfitable->count(), $dashboardProvisionalProfitable->count()) - 3 }} lainnya)</span>
                            <span x-show="showAllProfitable" style="display: none;">Tampilkan Lebih Sedikit</span>
                        </button>
                    </div>
                @endif
            </div>
        </section>

        <!-- Analitik: Barang Terlaris & Stok Terbanyak (Grafik) -->
        <section class="grid grid-cols-1 gap-6">
            <!-- Grafik Barang Terlaris -->
            <div class="polmart-card">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Barang Terlaris (Top Selling)</h2>
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

                @php
                    $maxTopSold = max(1, (int) ($topSellingProducts->max('total_sold') ?? 1));
                @endphp
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @forelse ($topSellingProducts as $idx => $item)
                        @php
                            $pctSold = min(100, max(6, round(((int) $item->total_sold / $maxTopSold) * 100)));
                        @endphp
                        <div>
                            <div class="flex items-center justify-between gap-3 text-xs" style="margin-bottom: 6px;">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="font-bold text-slate-400 w-5 shrink-0">{{ $idx + 1 }}.</span>
                                    <span class="font-semibold text-slate-800 truncate" title="{{ $item->name }}">{{ $item->name }}</span>
                                </div>
                                <div class="shrink-0 font-bold text-slate-700 whitespace-nowrap">
                                    <span class="text-blue-600">{{ number_format($item->total_sold) }} pcs</span>
                                    <span class="text-slate-400 font-normal ml-1">• Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full overflow-hidden" style="height: 12px;">
                                <div class="rounded-full transition-all duration-300"
                                     style="height: 12px; width: {{ $pctSold }}%; background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-sm text-slate-400 py-8">
                            Belum ada penjualan berstatus SELESAI pada periode ini.
                        </p>
                    @endforelse
                </div>
            </div>

            <!-- Grafik Stok Terbanyak -->
            <div class="polmart-card">
                <div class="flex items-center justify-between gap-3 mb-5">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Stok Terbanyak</h2>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                        Kelola Stok
                    </a>
                </div>

                @php
                    $maxHighStock = max(1, (int) ($highestStockProducts->max('stock') ?? 1));
                @endphp
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @forelse ($highestStockProducts as $product)
                        @php
                            $pctStock = min(100, max(6, round(((int) $product->stock / $maxHighStock) * 100)));
                            $barGrad = $product->stock > 10
                                ? 'linear-gradient(90deg, #10b981 0%, #059669 100%)'
                                : ($product->stock > 0
                                    ? 'linear-gradient(90deg, #f59e0b 0%, #d97706 100%)'
                                    : 'linear-gradient(90deg, #ef4444 0%, #dc2626 100%)');
                        @endphp
                        <div>
                            <div class="flex items-center justify-between gap-3 text-xs" style="margin-bottom: 6px;">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="font-semibold text-slate-800 truncate" title="{{ $product->name }}">{{ $product->name }}</span>
                                    <span class="text-[11px] text-slate-400 shrink-0">({{ $product->category }})</span>
                                </div>
                                <span class="shrink-0 font-bold {{ $product->stock > 10 ? 'text-emerald-700' : ($product->stock > 0 ? 'text-amber-700' : 'text-rose-600') }}">
                                    {{ number_format($product->stock) }} pcs
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full overflow-hidden" style="height: 12px;">
                                <div class="rounded-full transition-all duration-300"
                                     style="height: 12px; width: {{ $pctStock }}%; background: {{ $barGrad }};"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-sm text-slate-400 py-8">Belum ada produk aktif.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- Transaksi Terbaru -->
        <section class="polmart-card">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Transaksi Terbaru</h2>
                    <p class="text-xs text-slate-500">Pesanan web QRIS, walk-in, dan impor Excel</p>
                </div>
                <a href="{{ route('admin.transactions.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                    Semua
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @forelse ($recentOrders as $order)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div>
                            <div class="font-mono text-xs font-bold text-blue-700">{{ $order->order_code }}</div>
                            <div class="text-sm font-semibold text-slate-900">{{ $order->customer_name }}</div>
                            <div class="text-xs text-slate-400">
                                {{ $order->transaction_date?->format('d/m/Y H:i') }} • {{ str_replace('_', ' ', strtoupper($order->source)) }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-sm text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                            <span class="polmart-badge {{ $order->status === 'SELESAI' ? 'polmart-badge-green' : ($order->status === 'SIAP_DIAMBIL' ? 'polmart-badge-blue' : ($order->status === 'MENUNGGU_BAYAR' ? 'polmart-badge-amber' : 'polmart-badge-red')) }}">
                                {{ str_replace('_', ' ', $order->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="col-span-full text-center text-sm text-slate-400 py-6">Belum ada transaksi tercatat.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
