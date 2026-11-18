<x-app-layout>
    @section('title', 'Riwayat Transaksi - Polmart')
    @section('page_title', 'Riwayat Transaksi')

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Riwayat Seluruh Transaksi</h1>
                <p class="text-sm text-slate-500 mt-1">Rekap seluruh pesanan dari portal anggota (QRIS), kasir walk-in, dan impor Excel.</p>
            </div>
            <a href="{{ route('admin.reports.index') }}" class="polmart-btn polmart-btn-primary">
                <x-icon name="bar-chart-3" class="w-4 h-4" />
                <span>Laporan & Export Excel</span>
            </a>
        </div>

        <!-- Ringkasan -->
        <section class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="polmart-stat-icon blue"><x-icon name="clipboard-list" class="w-5 h-5" /></div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Selesai</p>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['completed'] }}</p>
                </div>
                <div class="polmart-stat-icon green"><x-icon name="check-circle" class="w-5 h-5" /></div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Siap Diambil</p>
                    <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $stats['ready'] }}</p>
                </div>
                <div class="polmart-stat-icon blue"><x-icon name="hash" class="w-5 h-5" /></div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Menunggu</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
                </div>
                <div class="polmart-stat-icon amber"><x-icon name="clock" class="w-5 h-5" /></div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Dibatalkan</p>
                    <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ $stats['cancelled'] }}</p>
                </div>
                <div class="polmart-stat-icon purple"><x-icon name="x-circle" class="w-5 h-5" /></div>
            </div>
        </section>

        <!-- Filter & Tabel -->
        <section class="polmart-card space-y-4">
            <form method="GET" action="{{ route('admin.transactions.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari kode pesanan atau nama pemesan..."
                       class="polmart-input sm:w-72">

                <select name="status" class="polmart-select sm:w-48">
                    <option value="">Semua Status</option>
                    <option value="SELESAI" {{ $statusFilter === 'SELESAI' ? 'selected' : '' }}>SELESAI</option>
                    <option value="SIAP_DIAMBIL" {{ $statusFilter === 'SIAP_DIAMBIL' ? 'selected' : '' }}>SIAP DIAMBIL</option>
                    <option value="MENUNGGU_BAYAR" {{ $statusFilter === 'MENUNGGU_BAYAR' ? 'selected' : '' }}>MENUNGGU BAYAR</option>
                    <option value="DIBATALKAN" {{ $statusFilter === 'DIBATALKAN' ? 'selected' : '' }}>DIBATALKAN</option>
                </select>

                <select name="source" class="polmart-select sm:w-48">
                    <option value="">Semua Sumber</option>
                    <option value="web" {{ $sourceFilter === 'web' ? 'selected' : '' }}>Portal Anggota (web)</option>
                    <option value="manual_admin" {{ $sourceFilter === 'manual_admin' ? 'selected' : '' }}>Kasir Walk-in</option>
                    <option value="import_excel" {{ $sourceFilter === 'import_excel' ? 'selected' : '' }}>Impor Excel</option>
                </select>

                <button type="submit" class="polmart-btn polmart-btn-primary">Filter</button>
                @if ($search !== '' || $statusFilter !== '' || $sourceFilter !== '')
                    <a href="{{ route('admin.transactions.index') }}" class="polmart-btn polmart-btn-secondary">Reset</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="polmart-table">
                    <thead>
                        <tr>
                            <th>Kode Pesanan</th>
                            <th>Pemesan</th>
                            <th>Tanggal Transaksi</th>
                            <th>Sumber</th>
                            <th>Item</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td class="font-mono font-bold text-blue-700">
                                    {{ $order->order_code }}
                                    @if ($order->pickup_code)
                                        <div class="text-xs text-emerald-700">Kode Ambil: #{{ $order->pickup_code }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-900">{{ $order->customer_name }}</div>
                                    @if ($order->user)
                                        <div class="text-xs text-slate-400">{{ $order->user->member_code }}</div>
                                    @endif
                                </td>
                                <td class="text-xs text-slate-600">{{ $order->transaction_date?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="polmart-badge polmart-badge-blue">
                                        {{ match($order->source) {
                                            'web' => 'Web QRIS',
                                            'manual_admin' => 'Walk-in',
                                            'import_excel' => 'Impor Excel',
                                            default => $order->source,
                                        } }}
                                    </span>
                                </td>
                                <td class="text-xs text-slate-600">
                                    {{ $order->items->sum('qty') }} pcs
                                    <div class="text-slate-400">
                                        {{ $order->items->map(fn ($i) => ($i->product?->name ?? 'Item') . ' (×' . $i->qty . ')')->take(2)->implode(', ') }}
                                    </div>
                                </td>
                                <td class="text-right font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="polmart-badge {{ $order->status === 'SELESAI' ? 'polmart-badge-green' : ($order->status === 'SIAP_DIAMBIL' ? 'polmart-badge-blue' : ($order->status === 'MENUNGGU_BAYAR' ? 'polmart-badge-amber' : 'polmart-badge-red')) }}">
                                        {{ str_replace('_', ' ', $order->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-slate-400 py-8">Belum ada riwayat transaksi yang sesuai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $orders->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
