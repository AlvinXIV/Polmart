<x-app-layout>
    @section('title', 'Dashboard Anggota - Polmart')
    @section('page_title', 'Dashboard Anggota')

    <div class="space-y-6">
        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- Banner Sambutan Anggota -->
        <section class="polmart-banner flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="relative z-10 max-w-2xl">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Halo, {{ Auth::user()->name }}!
                </h1>
                <div class="flex flex-wrap gap-3 mt-5">
                    <a href="{{ route('anggota.katalog') }}" class="px-4 py-2.5 bg-white text-blue-700 font-bold text-sm rounded-lg shadow hover:bg-blue-50 transition inline-flex items-center gap-2">
                        <x-icon name="shopping-bag" class="w-4 h-4" />
                        <span>Belanja di Katalog</span>
                    </a>
                    <a href="{{ route('anggota.keranjang') }}" class="px-4 py-2.5 bg-white/15 border border-white/30 text-white font-semibold text-sm rounded-lg hover:bg-white/25 transition inline-flex items-center gap-2">
                        <x-icon name="shopping-basket" class="w-4 h-4" />
                        <span>Keranjang ({{ $stats['cart_count'] }})</span>
                    </a>
                    <a href="{{ route('anggota.pesanan') }}" class="px-4 py-2.5 bg-white/15 border border-white/30 text-white font-semibold text-sm rounded-lg hover:bg-white/25 transition inline-flex items-center gap-2">
                        <x-icon name="clipboard-list" class="w-4 h-4" />
                        <span>Riwayat Pesanan</span>
                    </a>
                </div>
            </div>
            <div class="hidden lg:flex items-center justify-center w-20 h-20 rounded-2xl bg-white/15 text-white relative z-10 select-none">
                <x-icon name="shopping-bag" class="w-10 h-10" />
            </div>
        </section>

        <!-- Ringkasan Statistik Anggota -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Siap Diambil</p>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['ready_count'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">Tunjukkan kode di loket</p>
                </div>
                <div class="polmart-stat-icon green"><x-icon name="hash" class="w-5 h-5" /></div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Menunggu Bayar</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['pending_count'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">Segera selesaikan QRIS</p>
                </div>
                <div class="polmart-stat-icon amber"><x-icon name="clock" class="w-5 h-5" /></div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Isi Keranjang</p>
                    <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $stats['cart_count'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">Item siap checkout</p>
                </div>
                <div class="polmart-stat-icon blue"><x-icon name="shopping-basket" class="w-5 h-5" /></div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Pesanan Selesai</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['completed_count'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">Telah diambil</p>
                </div>
                <div class="polmart-stat-icon purple"><x-icon name="check-circle" class="w-5 h-5" /></div>
            </div>
        </section>

        <!-- Highlight Pesanan Siap Diambil (Kode Pengambilan 4-Digit) -->
        @if ($readyOrders->isNotEmpty())
            <section class="polmart-card border-l-4 border-l-emerald-500 space-y-4">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Pesanan Siap Diambil di Loket</h2>
                    <p class="text-xs text-slate-500">Tunjukkan kode pengambilan 4-digit di bawah ini kepada petugas Polmart.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($readyOrders as $order)
                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between gap-4">
                            <div>
                                <div class="font-mono text-xs font-bold text-emerald-700">{{ $order->order_code }}</div>
                                <div class="text-sm font-bold text-slate-900 mt-1">
                                    {{ $order->items->sum('qty') }} item • Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-slate-500 mt-1">
                                    Lunas: {{ $order->paid_at?->format('d/m/Y H:i') }}
                                </div>
                                <a href="{{ route('anggota.pesanan', ['order_code' => $order->order_code]) }}" class="inline-block mt-2 text-xs font-bold text-emerald-700 hover:underline">
                                    Lihat Struk
                                </a>
                            </div>
                            <div class="text-center bg-white px-4 py-3 rounded-xl border border-emerald-200 shadow-sm">
                                <span class="block text-[10px] font-bold uppercase text-slate-400">Kode Ambil</span>
                                <span class="text-2xl font-extrabold font-mono tracking-widest text-emerald-700">
                                    #{{ $order->pickup_code }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Pesanan Menunggu Pembayaran -->
        @if ($pendingOrders->isNotEmpty())
            <section class="polmart-card border-l-4 border-l-amber-500 space-y-4">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Menunggu Pembayaran QRIS</h2>
                    <p class="text-xs text-slate-500">Selesaikan pembayaran sebelum batas waktu 15 menit berakhir agar stok tidak dibatalkan.</p>
                </div>
                <div class="space-y-3">
                    @foreach ($pendingOrders as $order)
                        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="font-mono text-xs font-bold text-amber-800">{{ $order->order_code }}</div>
                                <div class="text-sm font-bold text-slate-900 mt-0.5">
                                    Total: Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-amber-700 mt-0.5">
                                    Batas bayar: {{ $order->expires_at?->format('d/m/Y H:i:s') }}
                                </div>
                            </div>
                            <a href="{{ route('anggota.pembayaran', ['order_code' => $order->order_code]) }}" class="polmart-btn polmart-btn-primary">
                                Bayar QRIS Sekarang
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Riwayat Pesanan Terakhir -->
        <section class="polmart-card">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900">Riwayat Pesanan Terakhir</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pantau status transaksi, item yang dibeli, dan kode pengambilan Anda</p>
                </div>
                <a href="{{ route('anggota.pesanan') }}" class="text-sm font-bold text-blue-600 hover:underline">
                    Lihat Semua
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($recentOrders as $order)
                    <a href="{{ route('anggota.pesanan', ['order_code' => $order->order_code]) }}"
                       class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-blue-300 transition">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono text-sm font-bold text-blue-700">{{ $order->order_code }}</span>
                                @if ($order->status === 'SIAP_DIAMBIL' && $order->pickup_code)
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-mono text-xs font-extrabold">
                                        Kode Ambil: #{{ $order->pickup_code }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 mt-1">
                                {{ $order->transaction_date?->format('d/m/Y H:i') }} • {{ $order->items->sum('qty') }} item
                                @if ($order->items->isNotEmpty())
                                    ({{ $order->items->map(fn ($i) => ($i->product?->name ?? 'Barang') . ' ×' . $i->qty)->take(3)->implode(', ') }}{{ $order->items->count() > 3 ? ', ...' : '' }})
                                @endif
                            </div>
                        </div>
                        <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2 shrink-0">
                            <div class="font-extrabold text-base text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                            <span class="polmart-badge {{ $order->status === 'SELESAI' ? 'polmart-badge-green' : ($order->status === 'SIAP_DIAMBIL' ? 'polmart-badge-blue' : ($order->status === 'MENUNGGU_BAYAR' ? 'polmart-badge-amber' : 'polmart-badge-red')) }}">
                                {{ str_replace('_', ' ', $order->status) }}
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="text-center text-sm text-slate-400 py-8">Anda belum memiliki riwayat pesanan.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
