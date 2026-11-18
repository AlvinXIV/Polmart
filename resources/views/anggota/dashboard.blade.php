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
                <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-xs font-bold tracking-wider uppercase mb-3">
                    ✦ PORTAL ANGGOTA POLMART
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Halo, {{ Auth::user()->name }}!
                </h1>
                <p class="text-blue-100 text-sm mt-2">
                    Nomor Anggota / NIM / NIK: <strong class="text-white font-mono">{{ Auth::user()->member_code ?? '-' }}</strong>
                    • Pantau pesanan aktif, kode pengambilan 4-digit, dan belanja kebutuhan koperasi langsung dari portal anggota.
                </p>
                <div class="flex flex-wrap gap-3 mt-5">
                    <a href="{{ route('anggota.katalog') }}" class="px-4 py-2.5 bg-white text-blue-700 font-bold text-sm rounded-lg shadow hover:bg-blue-50 transition">
                        🛍️ Belanja di Katalog
                    </a>
                    <a href="{{ route('anggota.keranjang') }}" class="px-4 py-2.5 bg-white/15 border border-white/30 text-white font-semibold text-sm rounded-lg hover:bg-white/25 transition">
                        🧺 Keranjang ({{ $stats['cart_count'] }})
                    </a>
                    <a href="{{ route('anggota.pesanan') }}" class="px-4 py-2.5 bg-white/15 border border-white/30 text-white font-semibold text-sm rounded-lg hover:bg-white/25 transition">
                        📋 Riwayat Pesanan
                    </a>
                </div>
            </div>
            <div class="hidden lg:block text-7xl relative z-10 select-none">🛍️</div>
        </section>

        <!-- Ringkasan Statistik Anggota -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Siap Diambil</p>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['ready_count'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">Tunjukkan kode di loket</p>
                </div>
                <div class="polmart-stat-icon green">🔢</div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Menunggu Bayar</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['pending_count'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">Segera selesaikan QRIS</p>
                </div>
                <div class="polmart-stat-icon amber">⏱</div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Isi Keranjang</p>
                    <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $stats['cart_count'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">Item siap checkout</p>
                </div>
                <div class="polmart-stat-icon blue">🧺</div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Pesanan Selesai</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['completed_count'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">Telah diambil</p>
                </div>
                <div class="polmart-stat-icon purple">✓</div>
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
                                    Lihat Struk →
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
                                Bayar QRIS Sekarang →
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Katalog Produk Unggulan & Pesanan Terakhir -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="polmart-card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Produk Tersedia di Katalog</h2>
                        <p class="text-xs text-slate-500">Barang aktif dengan stok siap dipesan</p>
                    </div>
                    <a href="{{ route('anggota.katalog') }}" class="text-xs font-bold text-blue-600 hover:underline">
                        Lihat Semua →
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @forelse ($featuredProducts as $product)
                        <div class="p-3.5 rounded-xl border border-slate-200 flex flex-col justify-between gap-2">
                            <div>
                                <span class="polmart-badge polmart-badge-blue">{{ $product->category }}</span>
                                <h3 class="font-bold text-sm text-slate-900 mt-2">{{ $product->name }}</h3>
                                <p class="text-xs text-slate-400">Stok: {{ $product->stock }} pcs</p>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                <span class="font-extrabold text-sm text-blue-700">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                <form method="POST" action="{{ route('anggota.keranjang.add') }}">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="qty" value="1">
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-blue-600 text-white text-xs font-bold hover:bg-blue-700">
                                        + Keranjang
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="col-span-2 text-center text-sm text-slate-400 py-6">Belum ada produk tersedia.</p>
                    @endforelse
                </div>
            </div>

            <div class="polmart-card">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">Riwayat Pesanan Terakhir</h2>
                        <p class="text-xs text-slate-500">Pantau status transaksi Anda</p>
                    </div>
                    <a href="{{ route('anggota.pesanan') }}" class="text-xs font-bold text-blue-600 hover:underline">
                        Semua →
                    </a>
                </div>
                <div class="space-y-3">
                    @forelse ($recentOrders as $order)
                        <a href="{{ route('anggota.pesanan', ['order_code' => $order->order_code]) }}"
                           class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-blue-300 transition">
                            <div>
                                <div class="font-mono text-xs font-bold text-blue-700">{{ $order->order_code }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $order->transaction_date?->format('d/m/Y H:i') }} • {{ $order->items->sum('qty') }} item</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-sm text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                <span class="polmart-badge {{ $order->status === 'SELESAI' ? 'polmart-badge-green' : ($order->status === 'SIAP_DIAMBIL' ? 'polmart-badge-blue' : ($order->status === 'MENUNGGU_BAYAR' ? 'polmart-badge-amber' : 'polmart-badge-red')) }}">
                                    {{ $order->status }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <p class="text-center text-sm text-slate-400 py-6">Anda belum memiliki riwayat pesanan.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
