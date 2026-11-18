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
            <a href="#sales-analytics" class="polmart-card block hover:border-blue-300">
                <div class="polmart-stat-icon amber mb-3">📑</div>
                <h3 class="font-bold text-slate-900">Laporan &amp; Analisis (FR-12)</h3>
                <p class="text-xs text-slate-500 mt-1">Analisis penjualan terpadu &amp; export .xlsx periode aktif.</p>
            </a>
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
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-xs font-bold uppercase tracking-wider">
                            ✦ Performa &amp; Laporan Penjualan
                        </span>
                        <span class="text-xs text-slate-500 font-medium">
                            Periode Aktif: <strong class="text-blue-700">{{ $periodLabel }}</strong>
                        </span>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900">Analisis Penjualan &amp; Laporan Berkala Koperasi</h2>
                    <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                        Pantau performa omzet aktual, tren waktu transaksi, distribusi pembayaran, dan produk terlaris tanpa berpindah halaman.
                    </p>
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
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider mr-1">Pilih Periode:</span>
                        <input type="hidden" name="period" :value="currentPeriod">
                        <button type="button" @click="currentPeriod = 'daily'"
                                :class="currentPeriod === 'daily' ? 'bg-blue-600 text-white font-bold shadow' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                class="px-3.5 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5">
                            <span>📅</span> Harian
                        </button>
                        <button type="button" @click="currentPeriod = 'weekly'"
                                :class="currentPeriod === 'weekly' ? 'bg-blue-600 text-white font-bold shadow' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                class="px-3.5 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5">
                            <span>📆</span> Mingguan
                        </button>
                        <button type="button" @click="currentPeriod = 'monthly'"
                                :class="currentPeriod === 'monthly' ? 'bg-blue-600 text-white font-bold shadow' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                class="px-3.5 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5">
                            <span>📊</span> Bulanan
                        </button>
                        <button type="button" @click="currentPeriod = 'yearly'"
                                :class="currentPeriod === 'yearly' ? 'bg-blue-600 text-white font-bold shadow' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'"
                                class="px-3.5 py-1.5 text-xs rounded-lg transition flex items-center gap-1.5">
                            <span>📈</span> Tahunan
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
                                🔍 Tampilkan Analisis
                            </button>
                            <a href="{{ route('admin.dashboard') }}#sales-analytics" class="polmart-btn polmart-btn-secondary polmart-btn-sm whitespace-nowrap" title="Reset ke Bulan Berjalan">
                                ↺ Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Ringkasan Finansial: HPP & Laba Kotor (Task 2B) -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-slate-200/80">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-extrabold text-slate-900">Ringkasan Finansial (HPP &amp; Laba Kotor)</h3>
                            @if ($periodSummary['has_unrecorded_hpp'])
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="Terdapat transaksi tanpa data HPP tercatat">
                                    <span>⚠️</span>
                                    <span>Laba Kotor Provisional</span>
                                </span>
                            @elseif ($periodSummary['total_transactions'] > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span>✓</span>
                                    <span>Data HPP Lengkap (100%)</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Berdasarkan snapshot biaya modal (HPP) riil transaksi SELESAI periode {{ strtolower($periodLabel) }}.
                        </p>
                    </div>
                    <div class="text-xs text-slate-500">
                        Kelengkapan HPP:
                        @if ($periodSummary['completeness_pct'] !== null)
                            <strong class="{{ $periodSummary['has_unrecorded_hpp'] ? 'text-amber-600' : 'text-emerald-600' }}">{{ $periodSummary['completeness_pct'] }}%</strong>
                            ({{ $periodSummary['total_order_items_count'] - $periodSummary['unrecorded_items_count'] }}/{{ $periodSummary['total_order_items_count'] }} item)
                        @else
                            <strong class="text-slate-400">—</strong>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div class="p-3.5 rounded-lg border border-emerald-200 bg-white shadow-sm">
                        <p class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Total Omzet</p>
                        <p class="text-xl font-extrabold text-emerald-900 mt-1">
                            Rp {{ number_format($periodSummary['total_omzet'], 0, ',', '.') }}
                        </p>
                        <p class="text-[10px] text-emerald-600 mt-0.5">
                            Subtotal penjualan transaksi selesai
                        </p>
                    </div>

                    <div class="p-3.5 rounded-lg border border-rose-200 bg-white shadow-sm">
                        <p class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">HPP Tercatat</p>
                        <p class="text-xl font-extrabold text-rose-900 mt-1">
                            Rp {{ number_format($periodSummary['total_hpp'], 0, ',', '.') }}
                        </p>
                        <p class="text-[10px] text-rose-600 mt-0.5">
                            Snapshot modal barang terjual
                        </p>
                    </div>

                    <div class="p-3.5 rounded-lg border border-blue-200 bg-white shadow-sm">
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

                    <div class="p-3.5 rounded-lg border border-indigo-200 bg-white shadow-sm">
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

                    <div class="p-3.5 rounded-lg border border-amber-200 bg-white shadow-sm">
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

                <p class="text-[11px] text-slate-500">
                    *Catatan Metrik: <strong>GPM (Gross Profit Margin)</strong> dihitung terhadap Total Omzet Penjualan; <strong>Markup</strong> dihitung terhadap Total HPP Tercatat.
                </p>

                @if ($periodSummary['has_unrecorded_hpp'])
                    <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2">
                        <span class="text-sm select-none">⚠️</span>
                        <div class="leading-relaxed">
                            <strong>Perhatian Data HPP:</strong> Terdapat <strong>{{ $periodSummary['unrecorded_orders_count'] }} transaksi</strong> (<strong>{{ $periodSummary['unrecorded_items_count'] }} item</strong>) dengan HPP belum tercatat. Nilai laba kotor di atas bersifat <em>provisional</em> dan tidak mengasumsikan biaya modal historis bernilai nol.
                        </div>
                    </div>
                @endif
            </div>

            <!-- 4 Kartu Ringkasan Penjualan Periode Aktif -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl border border-emerald-100 bg-emerald-50/40 flex items-center justify-between shadow-sm">
                    <div>
                        <p class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Total Omzet</p>
                        <p class="text-xl sm:text-2xl font-extrabold text-emerald-900 mt-1">
                            Rp {{ number_format($periodSummary['total_omzet'], 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-emerald-700 mt-0.5">
                            {{ number_format($periodSummary['total_transactions']) }} transaksi selesai
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-bold">
                        💰
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-blue-100 bg-blue-50/40 flex items-center justify-between shadow-sm">
                    <div>
                        <p class="text-xs font-bold text-blue-700 uppercase tracking-wider">Jumlah Transaksi</p>
                        <p class="text-xl sm:text-2xl font-extrabold text-blue-900 mt-1">
                            {{ number_format($periodSummary['total_transactions']) }}
                        </p>
                        <p class="text-[11px] text-blue-700 mt-0.5">
                            Pesanan status SELESAI
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl font-bold">
                        🧾
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-purple-100 bg-purple-50/40 flex items-center justify-between shadow-sm">
                    <div>
                        <p class="text-xs font-bold text-purple-700 uppercase tracking-wider">Produk Terjual</p>
                        <p class="text-xl sm:text-2xl font-extrabold text-purple-900 mt-1">
                            {{ number_format($periodSummary['total_items_sold']) }} <span class="text-sm font-semibold">pcs</span>
                        </p>
                        <p class="text-[11px] text-purple-700 mt-0.5">
                            Total unit fisik keluar
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl font-bold">
                        📦
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-amber-100 bg-amber-50/40 flex items-center justify-between shadow-sm">
                    <div>
                        <p class="text-xs font-bold text-amber-700 uppercase tracking-wider">Rata-rata Nilai Transaksi</p>
                        <p class="text-xl sm:text-2xl font-extrabold text-amber-900 mt-1">
                            Rp {{ number_format($periodSummary['avg_transaction'], 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-amber-700 mt-0.5">
                            Rata-rata belanja per pesanan
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl font-bold">
                        📊
                    </div>
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
                        <div class="relative pt-6 pb-2">
                            <div class="h-56 w-full flex items-end gap-1 sm:gap-2 px-1 border-b border-slate-200 overflow-x-auto">
                                @foreach ($chartData['series'] as $pt)
                                    @php
                                        $hOmzet = $chartData['max_omzet'] > 0 ? max(4, round(($pt['omzet'] / $chartData['max_omzet']) * 100)) : 4;
                                        $hCount = $chartData['max_count'] > 0 ? max(4, round(($pt['count'] / $chartData['max_count']) * 100)) : 4;
                                    @endphp
                                    <div class="group relative flex-1 min-w-[18px] sm:min-w-[24px] flex flex-col items-center h-full justify-end">
                                        <!-- Tooltip Hover -->
                                        <div class="opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity absolute bottom-full mb-2 z-20 px-2.5 py-1.5 rounded-lg bg-slate-900 text-white text-[11px] font-medium shadow-xl whitespace-nowrap">
                                            <div class="font-bold text-blue-300">{{ $pt['label'] }}</div>
                                            <div>Omzet: <strong>Rp {{ number_format($pt['omzet'], 0, ',', '.') }}</strong></div>
                                            <div>Transaksi: <strong>{{ $pt['count'] }} pesanan</strong></div>
                                        </div>

                                        <!-- Bar Omzet -->
                                        <div class="w-full rounded-t-md transition-all duration-300"
                                             x-show="chartMetric === 'omzet'"
                                             style="height: {{ $pt['omzet'] > 0 ? $hOmzet : 4 }}%; background: {{ $pt['omzet'] > 0 ? 'linear-gradient(180deg, #3b82f6 0%, #1d4ed8 100%)' : '#e2e8f0' }};">
                                        </div>

                                        <!-- Bar Jumlah Transaksi -->
                                        <div class="w-full rounded-t-md transition-all duration-300"
                                             x-show="chartMetric === 'count'"
                                             style="height: {{ $pt['count'] > 0 ? $hCount : 4 }}%; background: {{ $pt['count'] > 0 ? 'linear-gradient(180deg, #10b981 0%, #047857 100%)' : '#e2e8f0' }};">
                                        </div>

                                        <!-- Label Sumbu X -->
                                        <span class="text-[10px] text-slate-400 mt-2 truncate w-full text-center select-none font-mono">
                                            {{ $pt['short_label'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="h-56 flex flex-col items-center justify-center text-center p-6 border border-dashed border-slate-200 rounded-xl">
                            <span class="text-3xl mb-2">📉</span>
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
                            $totalSrc = $periodSummary['total_transactions'];
                            $pctWeb = $totalSrc > 0 ? round(($periodSourceCounts['web'] / $totalSrc) * 100, 1) : 0;
                            $pctWalkin = $totalSrc > 0 ? round(($periodSourceCounts['manual_admin'] / $totalSrc) * 100, 1) : 0;
                            $pctImport = $totalSrc > 0 ? round(($periodSourceCounts['import_excel'] / $totalSrc) * 100, 1) : 0;
                            $otherCount = $periodSourceCounts['other'] ?? 0;
                            $pctOther = $totalSrc > 0 ? round(($otherCount / $totalSrc) * 100, 1) : 0;
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

                            <!-- Impor Excel -->
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-semibold text-slate-700 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                                        Impor Excel Historis
                                    </span>
                                    <span class="font-bold text-slate-900">{{ $periodSourceCounts['import_excel'] }} ({{ $pctImport }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-purple-500 h-2 rounded-full transition-all duration-500" style="width: {{ $pctImport }}%"></div>
                                </div>
                            </div>

                            <!-- Sumber Lainnya (jika ada transaksi di luar 3 sumber utama) -->
                            @if ($otherCount > 0)
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-semibold text-slate-700 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                                        Sumber Lainnya
                                    </span>
                                    <span class="font-bold text-slate-900">{{ $otherCount }} ({{ $pctOther }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-slate-400 h-2 rounded-full transition-all duration-500" style="width: {{ $pctOther }}%"></div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Total Transaksi Selesai:</span>
                        <strong class="text-slate-900 font-bold">{{ number_format($totalSrc) }} pesanan</strong>
                    </div>
                </div>
            </div>

            <!-- Produk Terlaris & Detail Transaksi Periode Aktif -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <!-- Tabel Produk Terlaris Periode Aktif -->
                <div class="p-5 rounded-xl border border-slate-200 bg-white">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Produk Terlaris (Periode Aktif)</h3>
                            <p class="text-xs text-slate-500">Urutan barang dengan kuantitas penjualan tertinggi pada periode ini</p>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs font-bold font-mono">
                            Top {{ count($periodTopProducts) }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="polmart-table text-xs">
                            <thead>
                                <tr>
                                    <th class="w-8">#</th>
                                    <th>Barang</th>
                                    <th class="text-center">Terjual</th>
                                    <th class="text-right">Total Omzet</th>
                                    <th class="text-center">Sisa Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($periodTopProducts as $idx => $item)
                                    <tr>
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
                                        <td class="text-right font-bold text-slate-900">
                                            Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <span class="font-mono text-slate-600">{{ $item->stock }} pcs</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-slate-400 py-6">
                                            Belum ada produk terjual pada periode ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabel Detail Transaksi Penjualan Periode Aktif -->
                <div class="p-5 rounded-xl border border-slate-200 bg-white">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Detail Transaksi Penjualan</h3>
                            <p class="text-xs text-slate-500">Daftar transaksi SELESAI yang terekam dalam periode ini</p>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-bold font-mono">
                            @if (($totalPeriodOrders ?? count($periodOrders)) > count($periodOrders))
                                Menampilkan {{ count($periodOrders) }} dari {{ number_format($totalPeriodOrders) }} Transaksi
                            @else
                                {{ count($periodOrders) }} Transaksi
                            @endif
                        </span>
                    </div>

                    <div class="overflow-x-auto max-h-[360px] overflow-y-auto">
                        <table class="polmart-table text-xs">
                            <thead class="sticky top-0 bg-white shadow-sm z-10">
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Waktu</th>
                                    <th>Pemesan</th>
                                    <th>Sumber</th>
                                    <th class="text-center">Item</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($periodOrders as $ord)
                                    <tr>
                                        <td class="font-mono font-bold text-blue-700">{{ $ord->order_code }}</td>
                                        <td class="text-[11px] text-slate-500">{{ $ord->transaction_date?->format('d/m/Y H:i') }}</td>
                                        <td class="font-semibold text-slate-800">{{ $ord->customer_name }}</td>
                                        <td>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                                @if ($ord->source === 'web') bg-blue-50 text-blue-700
                                                @elseif ($ord->source === 'manual_admin') bg-emerald-50 text-emerald-700
                                                @elseif ($ord->source === 'import_excel') bg-purple-50 text-purple-700
                                                @else bg-slate-100 text-slate-600 @endif">
                                                {{ match($ord->source) {
                                                    'web' => 'QRIS Web',
                                                    'manual_admin' => 'Kasir',
                                                    'import_excel' => 'Impor',
                                                    default => 'Lainnya (' . ($ord->source ?: '-') . ')'
                                                } }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $ord->items->sum('qty') }}</td>
                                        <td class="text-right font-bold text-slate-900">Rp {{ number_format($ord->total_amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-slate-400 py-6">
                                            Tidak ada transaksi berstatus SELESAI pada periode ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if (($totalPeriodOrders ?? count($periodOrders)) > count($periodOrders))
                        <p class="text-[11px] text-slate-500 mt-2.5 text-right italic">
                            * Menampilkan {{ count($periodOrders) }} transaksi terbaru pada periode ini. Gunakan tombol <strong>Export Excel (.xlsx)</strong> di atas untuk mengunduh seluruh {{ number_format($totalPeriodOrders) }} transaksi.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Produk Paling Menguntungkan Berdasarkan Laba Kotor (Task 2C) -->
            <div class="p-5 rounded-xl border border-slate-200 bg-white">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                            <span>Produk Paling Menguntungkan (Periode Aktif)</span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800">
                                Berdasarkan Laba Kotor
                            </span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Urutan produk dengan total laba kotor tertinggi (Omzet - HPP Snapshot) transaksi SELESAI</p>
                    </div>
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
                                    <th class="text-right">Total Laba Kotor</th>
                                    <th class="text-center">Margin</th>
                                    <th class="text-center">Status</th>
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
                                    <tr>
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
                                        <td class="text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ✓ Lengkap
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-center border border-dashed border-slate-200 rounded-xl space-y-1">
                        <p class="text-sm font-semibold text-slate-700">Belum ada produk dengan data HPP lengkap pada periode ini.</p>
                        <p class="text-xs text-slate-400">Pastikan transaksi berstatus SELESAI dan seluruh item memiliki biaya modal (HPP) yang tercatat.</p>
                    </div>
                @endif

                @if ($dashboardProvisionalProfitable->isNotEmpty())
                    <div class="mt-4 pt-3 border-t border-slate-200">
                        <h4 class="text-xs font-bold text-amber-800 flex items-center gap-1.5 mb-2">
                            <span>⚠️</span>
                            <span>Produk Terjual dengan Data HPP Belum Lengkap (Provisional)</span>
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="polmart-table text-xs opacity-90">
                                <thead class="bg-amber-50/50">
                                    <tr>
                                        <th>Barang</th>
                                        <th class="text-center">Terjual</th>
                                        <th class="text-right">Total Omzet</th>
                                        <th class="text-right">HPP Tercatat</th>
                                        <th class="text-right">Laba Kotor (Semu)</th>
                                        <th class="text-center">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dashboardProvisionalProfitable as $item)
                                        @php
                                            $itemRev = (float) ($item->total_revenue ?? 0);
                                            $itemCost = (float) ($item->total_cost ?? 0);
                                            $itemProfit = (float) ($item->total_profit ?? 0);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="font-medium text-slate-800">{{ $item->name }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono">{{ $item->sku }}</div>
                                            </td>
                                            <td class="text-center">{{ number_format($item->total_sold) }} pcs</td>
                                            <td class="text-right">Rp {{ number_format($itemRev, 0, ',', '.') }}</td>
                                            <td class="text-right text-rose-700">Rp {{ number_format($itemCost, 0, ',', '.') }}</td>
                                            <td class="text-right font-medium text-amber-700">Rp {{ number_format($itemProfit, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800" title="Sebagian atau seluruh penjualan item ini tidak memiliki snapshot HPP">
                                                    Provisional ({{ $item->unrecorded_items_count }} item belum ada HPP)
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
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
