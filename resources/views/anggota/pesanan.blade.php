<x-app-layout>
    @section('title', 'Pesanan Saya - Polmart')
    @section('page_title', 'Pesanan Saya')

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600">PORTAL ANGGOTA</span>
                <h1 class="text-2xl font-extrabold text-slate-900 mt-1">Riwayat & Status Pesanan</h1>
                <p class="text-sm text-slate-500 mt-1">Pantau status pesanan Anda, selesaikan pembayaran QRIS, atau tunjukkan kode pengambilan 4-digit di loket.</p>
            </div>
            <a href="{{ route('anggota.katalog') }}" class="polmart-btn polmart-btn-primary">
                🛍️ Belanja Lagi
            </a>
        </div>

        @if ($orders->isEmpty())
            <div class="polmart-card text-center py-14 space-y-4">
                <div class="text-5xl">📋</div>
                <h2 class="text-lg font-extrabold text-slate-900">Belum ada pesanan</h2>
                <p class="text-sm text-slate-500">Anda belum pernah melakukan pemesanan di Polmart.</p>
                <div>
                    <a href="{{ route('anggota.katalog') }}" class="polmart-btn polmart-btn-primary">
                        Buka Katalog Belanja
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <!-- Daftar Pesanan -->
                <div class="lg:col-span-1 space-y-3">
                    <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-500">Daftar Pesanan</h2>
                    @foreach ($orders as $order)
                        @php $isSelected = $selectedOrder && $selectedOrder->id === $order->id; @endphp
                        <a href="{{ route('anggota.pesanan', ['order_code' => $order->order_code]) }}"
                           class="block p-4 rounded-xl border transition {{ $isSelected ? 'bg-blue-50 border-blue-500 shadow-sm' : 'bg-white border-slate-200 hover:border-blue-300' }}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-mono text-xs font-bold text-blue-700">{{ $order->order_code }}</span>
                                <span class="polmart-badge {{ $order->status === 'SELESAI' ? 'polmart-badge-green' : ($order->status === 'SIAP_DIAMBIL' ? 'polmart-badge-blue' : ($order->status === 'MENUNGGU_BAYAR' ? 'polmart-badge-amber' : 'polmart-badge-red')) }}">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs text-slate-500">{{ $order->transaction_date?->format('d/m/Y H:i') }}</span>
                                <strong class="text-sm text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                            </div>
                            @if ($order->status === 'SIAP_DIAMBIL' && $order->pickup_code)
                                <div class="mt-2 text-xs font-bold text-emerald-700">
                                    Kode Ambil: <span class="font-mono tracking-widest">#{{ $order->pickup_code }}</span>
                                </div>
                            @endif
                        </a>
                    @endforeach

                    <div class="pt-2">
                        {{ $orders->links() }}
                    </div>
                </div>

                <!-- Detail Pesanan Terpilih -->
                @if ($selectedOrder)
                    <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                        <section class="md:col-span-2 polmart-card space-y-5">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                                <div>
                                    <h2 class="text-lg font-extrabold text-slate-900">Detail Pesanan</h2>
                                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $selectedOrder->order_code }}</p>
                                </div>
                                <span class="polmart-badge {{ $selectedOrder->status === 'SELESAI' ? 'polmart-badge-green' : ($selectedOrder->status === 'SIAP_DIAMBIL' ? 'polmart-badge-blue' : ($selectedOrder->status === 'MENUNGGU_BAYAR' ? 'polmart-badge-amber' : 'polmart-badge-red')) }}">
                                    {{ str_replace('_', ' ', $selectedOrder->status) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm border-b border-slate-100 pb-4">
                                <div>
                                    <p class="text-xs text-slate-400">Tanggal Transaksi</p>
                                    <p class="font-semibold text-slate-900 mt-0.5">{{ $selectedOrder->transaction_date?->format('d/m/Y H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Nama Pemesan</p>
                                    <p class="font-semibold text-slate-900 mt-0.5">{{ $selectedOrder->customer_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Metode</p>
                                    <p class="font-semibold text-slate-900 mt-0.5">
                                        {{ $selectedOrder->source === 'web' ? 'QRIS Dinamis' : str_replace('_', ' ', strtoupper($selectedOrder->source)) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Waktu Bayar</p>
                                    <p class="font-semibold text-slate-900 mt-0.5">{{ $selectedOrder->paid_at?->format('d/m/Y H:i') ?? 'Belum dibayar' }}</p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h3 class="text-sm font-bold text-slate-900">Barang Dipesan</h3>
                                @foreach ($selectedOrder->items as $item)
                                    <div class="flex items-center justify-between text-sm">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $item->product?->name ?? 'Barang' }}</p>
                                            <p class="text-xs text-slate-500">{{ $item->qty }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                                        </div>
                                        <strong class="text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                                    </div>
                                @endforeach
                            </div>

                            <div class="border-t border-slate-200 pt-4 flex items-center justify-between">
                                <span class="font-bold text-slate-900">Total Pembayaran</span>
                                <span class="text-xl font-extrabold text-blue-700">Rp {{ number_format($selectedOrder->total_amount, 0, ',', '.') }}</span>
                            </div>

                            @if ($selectedOrder->status === 'MENUNGGU_BAYAR')
                                <div class="pt-2">
                                    <a href="{{ route('anggota.pembayaran', ['order_code' => $selectedOrder->order_code]) }}"
                                       class="polmart-btn polmart-btn-primary w-full py-3">
                                        Lanjutkan Pembayaran QRIS
                                    </a>
                                </div>
                            @endif
                        </section>

                        <!-- Kotak Kode Pengambilan 4-Digit -->
                        <aside class="polmart-card text-center space-y-4">
                            <h3 class="text-sm font-extrabold text-slate-900">Kode Pengambilan</h3>
                            <p class="text-xs text-slate-500">Tunjukkan kode ini kepada petugas di loket koperasi.</p>

                            <div class="p-6 rounded-2xl border {{ $selectedOrder->pickup_code ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50 border-slate-200' }}">
                                <p class="text-3xl font-extrabold font-mono tracking-[0.25em] {{ $selectedOrder->pickup_code ? 'text-emerald-700' : 'text-slate-400' }}">
                                    {{ $selectedOrder->pickup_code ? ('#' . $selectedOrder->pickup_code) : '----' }}
                                </p>
                                <p class="text-xs mt-2 {{ $selectedOrder->pickup_code ? 'text-emerald-700' : 'text-slate-500' }}">
                                    @if ($selectedOrder->status === 'SIAP_DIAMBIL')
                                        Pesanan siap diambil di loket!
                                    @elseif ($selectedOrder->status === 'SELESAI')
                                        Pesanan telah diserahkan & selesai.
                                    @elseif ($selectedOrder->status === 'DIBATALKAN')
                                        Pesanan dibatalkan / kedaluwarsa.
                                    @else
                                        Muncul otomatis setelah QRIS dibayar.
                                    @endif
                                </p>
                            </div>
                        </aside>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
