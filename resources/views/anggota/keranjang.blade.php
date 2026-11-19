<x-app-layout>
    @section('title', 'Keranjang Belanja - Polmart')
    @section('page_title', 'Keranjang Belanja')

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Keranjang Belanja</h1>
                <p class="text-sm text-slate-500 mt-1">Periksa kembali daftar barang dan kuantitas sebelum melakukan checkout.</p>
            </div>
            @if (! empty($items))
                <form method="POST" action="{{ route('anggota.keranjang.clear') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="polmart-btn polmart-btn-danger polmart-btn-sm"
                            onclick="return confirm('Kosongkan seluruh keranjang belanja?')">
                        <x-icon name="trash-2" class="w-4 h-4" />
                        <span>Kosongkan Keranjang</span>
                    </button>
                </form>
            @endif
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
        @foreach ($warnings as $warning)
            <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm font-medium flex items-center gap-2">
                <x-icon name="alert-triangle" class="w-4 h-4 shrink-0" />
                <span>{{ $warning }}</span>
            </div>
        @endforeach

        @if (empty($items))
            <div class="polmart-card text-center py-14 space-y-4">
                <div class="flex justify-center text-slate-400"><x-icon name="shopping-basket" class="w-12 h-12" /></div>
                <h2 class="text-lg font-extrabold text-slate-900">Keranjang belanja masih kosong</h2>
                <p class="text-sm text-slate-500 max-w-md mx-auto">Jelajahi katalog Polmart untuk memilih produk kebutuhan Anda.</p>
                <div>
                    <a href="{{ route('anggota.katalog') }}" class="polmart-btn polmart-btn-primary">
                        <x-icon name="shopping-bag" class="w-4 h-4" />
                        <span>Belanja di Katalog</span>
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <!-- Daftar Item -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach ($items as $item)
                        @php $product = $item['product']; @endphp
                        <div class="polmart-card flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                            <div class="w-24 h-24 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                                @if ($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-slate-400"><x-icon name="package" class="w-8 h-8" /></span>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-bold text-blue-600 uppercase">{{ $product->category }}</span>
                                <h3 class="font-bold text-slate-900 text-base">{{ $product->name }}</h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Rp {{ number_format($item['unit_price'], 0, ',', '.') }} / pcs • Sisa stok: {{ $product->stock }}
                                </p>

                                <div class="flex flex-wrap items-center gap-3 mt-3">
                                    <form method="POST" action="{{ route('anggota.keranjang.update', $product) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label class="text-xs font-semibold text-slate-500">Qty:</label>
                                        <div class="polmart-qty-stepper">
                                            <button type="button"
                                                    onclick="const i = this.nextElementSibling; i.value = Math.max(1, (parseInt(i.value, 10) || 1) - 1);">
                                                −
                                            </button>
                                            <input type="number" name="qty" value="{{ (int) $item['qty'] }}" min="1" max="{{ $product->stock }}" readonly>
                                            <button type="button"
                                                    onclick="const i = this.previousElementSibling; const m = parseInt(i.max, 10) || 1; i.value = Math.min(m, (parseInt(i.value, 10) || 1) + 1);">
                                                +
                                            </button>
                                        </div>
                                        <button type="submit" class="polmart-btn polmart-btn-secondary polmart-btn-sm">
                                            Perbarui
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('anggota.keranjang.remove', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <p class="text-xs text-slate-400">Subtotal</p>
                                <p class="text-lg font-extrabold text-slate-900">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-2">
                        <a href="{{ route('anggota.katalog') }}" class="text-sm font-bold text-blue-600 hover:underline">
                            Lanjut Belanja di Katalog
                        </a>
                    </div>
                </div>

                <!-- Ringkasan Belanja -->
                <aside class="polmart-card space-y-5 sticky top-24">
                    <h2 class="text-lg font-extrabold text-slate-900">Ringkasan Belanja</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Item</span>
                            <strong class="text-slate-900">{{ $totalItems }} pcs</strong>
                        </div>
                        <div class="flex justify-between border-t border-slate-200 pt-3 text-base">
                            <span class="font-bold text-slate-900">Total Pembayaran</span>
                            <strong class="text-xl font-extrabold text-blue-700">Rp {{ number_format($totalAmount, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <a href="{{ route('anggota.checkout') }}" class="polmart-btn polmart-btn-primary w-full py-3">
                        Lanjut ke Checkout
                    </a>
                </aside>
            </div>
        @endif
    </div>
</x-app-layout>
