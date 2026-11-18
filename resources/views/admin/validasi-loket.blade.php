<x-app-layout>
    @section('title', 'Validasi Loket Pengambilan - Polmart')
    @section('page_title', 'Validasi Loket Pengambilan')

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Validasi Loket & Pengambilan Barang</h1>
                <p class="text-sm text-slate-500 mt-1">Klik tombol Selesai pada pesanan berstatus SIAP DIAMBIL untuk menandakan barang telah diambil anggota.</p>
            </div>
            <a href="{{ route('admin.transactions.index') }}" class="polmart-btn polmart-btn-secondary">
                <x-icon name="receipt" class="w-4 h-4" />
                <span>Riwayat Transaksi</span>
            </a>
        </div>

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

        <!-- Statistik Status -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Siap Diambil</p>
                    <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $stats['ready_count'] }}</p>
                </div>
                <div class="polmart-stat-icon blue"><x-icon name="hash" class="w-5 h-5" /></div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Selesai</p>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['completed_count'] }}</p>
                </div>
                <div class="polmart-stat-icon green"><x-icon name="check-circle" class="w-5 h-5" /></div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Menunggu Bayar</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['pending_count'] }}</p>
                </div>
                <div class="polmart-stat-icon amber"><x-icon name="clock" class="w-5 h-5" /></div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Dibatalkan</p>
                    <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ $stats['cancelled_count'] }}</p>
                </div>
                <div class="polmart-stat-icon purple"><x-icon name="x-circle" class="w-5 h-5" /></div>
            </div>
        </section>

        <!-- Filter & Daftar Pesanan -->
        <section class="polmart-card space-y-4">
            <form method="GET" action="{{ route('admin.pickup.index') }}" class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3">
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Cari kode pesanan, kode ambil, atau nama..."
                           class="polmart-input sm:w-72">
                    <select name="status" class="polmart-select sm:w-48">
                        <option value="SIAP_DIAMBIL" {{ $statusFilter === 'SIAP_DIAMBIL' ? 'selected' : '' }}>SIAP DIAMBIL (Antrean)</option>
                        <option value="SELESAI" {{ $statusFilter === 'SELESAI' ? 'selected' : '' }}>SELESAI</option>
                        <option value="MENUNGGU_BAYAR" {{ $statusFilter === 'MENUNGGU_BAYAR' ? 'selected' : '' }}>MENUNGGU BAYAR</option>
                        <option value="DIBATALKAN" {{ $statusFilter === 'DIBATALKAN' ? 'selected' : '' }}>DIBATALKAN</option>
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                    </select>
                    <button type="submit" class="polmart-btn polmart-btn-primary">Filter</button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="polmart-table">
                    <thead>
                        <tr>
                            <th>Kode Pesanan</th>
                            <th>Kode Ambil</th>
                            <th>Anggota / Pemesan</th>
                            <th>Daftar Barang</th>
                            <th>Waktu Transaksi</th>
                            <th class="text-right">Total</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi Loket</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td class="font-mono font-bold text-blue-700">{{ $order->order_code }}</td>
                                <td>
                                    @if ($order->pickup_code)
                                        <span class="px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 font-mono font-extrabold tracking-widest">
                                            #{{ $order->pickup_code }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-900">{{ $order->customer_name }}</div>
                                    <div class="text-xs text-slate-400">{{ $order->user?->member_code ?? str_replace('_', ' ', strtoupper($order->source)) }}</div>
                                </td>
                                <td class="text-xs text-slate-600">
                                    @foreach ($order->items as $item)
                                        <div>{{ $item->qty }}× {{ $item->product?->name ?? 'Barang' }}</div>
                                    @endforeach
                                </td>
                                <td class="text-xs text-slate-500">
                                    {{ $order->transaction_date?->format('d/m/Y H:i') }}
                                    @if ($order->paid_at)
                                        <div class="text-emerald-600">Lunas: {{ $order->paid_at->format('H:i') }}</div>
                                    @endif
                                </td>
                                <td class="text-right font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="polmart-badge {{ $order->status === 'SELESAI' ? 'polmart-badge-green' : ($order->status === 'SIAP_DIAMBIL' ? 'polmart-badge-blue' : ($order->status === 'MENUNGGU_BAYAR' ? 'polmart-badge-amber' : 'polmart-badge-red')) }}">
                                        {{ str_replace('_', ' ', $order->status) }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    @if ($order->status === 'SIAP_DIAMBIL')
                                        <form method="POST" action="{{ route('admin.pickup.complete', $order) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="polmart-btn polmart-btn-success polmart-btn-sm">
                                                Selesai
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-slate-400 py-8">
                                    Tidak ada transaksi ditemukan pada filter ini.
                                </td>
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
