<x-app-layout>
    @section('title', 'Checkout Pesanan - Polmart')
    @section('page_title', 'Checkout Pesanan')

    <div class="space-y-6">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600">PORTAL ANGGOTA</span>
            <h1 class="text-2xl font-extrabold text-slate-900 mt-1">Konfirmasi Checkout</h1>
            <p class="text-sm text-slate-500 mt-1">Periksa detail pesanan Anda sebelum melakukan reservasi stok & pembayaran QRIS.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2 space-y-6">
                <!-- Daftar Barang -->
                <section class="polmart-card">
                    <h2 class="text-base font-extrabold text-slate-900 mb-4">Detail Barang Dipesan</h2>
                    <div class="divide-y divide-slate-100">
                        @foreach ($items as $item)
                            @php $product = $item['product']; @endphp
                            <div class="py-3.5 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center overflow-hidden">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-2xl">📦</span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">{{ $product->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $item['qty'] }} × Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <p class="font-extrabold text-slate-900 text-sm">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- Data Anggota -->
                <section class="polmart-card">
                    <h2 class="text-base font-extrabold text-slate-900 mb-4">Informasi Pemesan</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-slate-400 text-xs">Nama Lengkap</p>
                            <p class="font-bold text-slate-900 mt-0.5">{{ Auth::user()->name }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs">Nomor Anggota / NIM / NIK</p>
                            <p class="font-bold text-slate-900 font-mono mt-0.5">{{ Auth::user()->member_code ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs">Email</p>
                            <p class="font-bold text-slate-900 mt-0.5">{{ Auth::user()->email }}</p>
                        </div>
                        <div>
                            <p class="text-slate-400 text-xs">Nomor WhatsApp</p>
                            <p class="font-bold text-slate-900 mt-0.5">{{ Auth::user()->phone ?? '-' }}</p>
                        </div>
                    </div>
                </section>

                <!-- Metode Pembayaran -->
                <section class="polmart-card">
                    <h2 class="text-base font-extrabold text-slate-900 mb-3">Metode Pembayaran</h2>
                    <div class="border border-blue-500 bg-blue-50 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-white border border-blue-200 flex items-center justify-center font-extrabold text-blue-700">
                            QRIS
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-slate-900 text-sm">QRIS</p>
                            <p class="text-xs text-slate-600 mt-0.5">Kode QR unik dibuat otomatis dengan batas pembayaran 15 menit.</p>
                        </div>
                        <span class="text-blue-700 font-extrabold">✓</span>
                    </div>
                </section>
            </div>

            <!-- Ringkasan Checkout -->
            <aside class="polmart-card space-y-5 sticky top-24">
                <h2 class="text-lg font-extrabold text-slate-900">Ringkasan Pembayaran</h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Total Produk</span>
                        <strong class="text-slate-900">{{ $totalItems }} item</strong>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-3">
                        <span class="font-bold text-slate-900">Total Pembayaran</span>
                        <strong class="text-xl font-extrabold text-blue-700">Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 leading-relaxed">
                    <strong>Perhatian:</strong> Saat pesanan dibuat, stok langsung direservasi dan Anda memiliki waktu <strong>15 menit</strong> untuk menyelesaikan pembayaran QRIS.
                </div>

                <form method="POST" action="{{ route('anggota.checkout.store') }}">
                    @csrf
                    <button type="submit" class="polmart-btn polmart-btn-primary w-full py-3">
                        Buat Pesanan & Bayar QRIS
                    </button>
                </form>

                <a href="{{ route('anggota.keranjang') }}" class="block text-center text-sm font-semibold text-slate-500 hover:text-blue-700">
                    Kembali ke Keranjang
                </a>
            </aside>
        </div>
    </div>
</x-app-layout>
