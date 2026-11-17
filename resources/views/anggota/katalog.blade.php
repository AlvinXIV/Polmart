<x-app-layout>
    @section('title', 'Katalog Belanja Anggota - Polmart')
    @section('page_title', 'Katalog Belanja')

    <div class="space-y-6">
        <!-- Header Katalog -->
        <section class="polmart-banner flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="relative z-10">
                <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider mb-2">
                    PORTAL BELANJA ANGGOTA
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold">Katalog Produk Polmart 🛍️</h1>
                <p class="text-blue-100 text-sm mt-1">Pilih barang aktif dengan stok tersedia secara real-time dan masukkan ke keranjang belanja Anda.</p>
            </div>
            <div class="relative z-10">
                <a href="{{ route('anggota.keranjang') }}" class="px-5 py-3 bg-white text-blue-700 font-extrabold text-sm rounded-xl shadow hover:bg-blue-50 transition inline-flex items-center gap-2">
                    🧺 Lihat Keranjang ({{ $cartCount }})
                </a>
            </div>
        </section>

        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center justify-between gap-3">
                <span>{{ session('success') }}</span>
                <a href="{{ route('anggota.keranjang') }}" class="font-bold underline">Buka Keranjang →</a>
            </div>
        @endif
        @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Pencarian & Filter Kategori -->
        <section class="polmart-card space-y-4">
            <form method="GET" action="{{ route('anggota.katalog') }}" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari nama barang, SKU, atau kategori..."
                       class="polmart-input sm:w-80">

                <select name="category" class="polmart-select sm:w-52">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" {{ $selectedCategory === $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="polmart-btn polmart-btn-primary">Cari</button>
                @if ($search !== '' || $selectedCategory !== '')
                    <a href="{{ route('anggota.katalog') }}" class="polmart-btn polmart-btn-secondary">Reset</a>
                @endif
            </form>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('anggota.katalog', array_filter(['search' => $search])) }}"
                   class="px-4 py-1.5 rounded-full text-xs font-semibold border {{ $selectedCategory === '' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-400' }}">
                    Semua Produk
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('anggota.katalog', array_filter(['category' => $category, 'search' => $search])) }}"
                       class="px-4 py-1.5 rounded-full text-xs font-semibold border {{ $selectedCategory === $category ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-400' }}">
                        {{ $category }}
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Daftar Produk -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @forelse ($products as $product)
                @php
                    $inCartQty = (int) ($cart[$product->id] ?? 0);
                    $maxAddable = max(0, $product->stock - $inCartQty);
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="h-44 bg-slate-100 flex items-center justify-center overflow-hidden relative">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-6xl">📦</span>
                            @endif
                            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-xs font-bold bg-white/90 text-blue-700 shadow-sm">
                                {{ $product->category }}
                            </span>
                        </div>

                        <div class="p-4">
                            <p class="font-mono text-xs text-slate-400">{{ $product->sku }}</p>
                            <h3 class="font-bold text-slate-900 mt-1 line-clamp-2">{{ $product->name }}</h3>

                            <div class="flex items-center justify-between mt-3">
                                <span class="text-lg font-extrabold text-blue-700">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </span>
                                <span class="text-xs font-semibold {{ $product->stock <= 5 ? 'text-amber-600' : 'text-emerald-600' }}">
                                    Stok: {{ $product->stock }}
                                </span>
                            </div>

                            @if ($inCartQty > 0)
                                <p class="text-xs text-blue-600 font-semibold mt-1">
                                    Di keranjang: {{ $inCartQty }} pcs
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="p-4 pt-0">
                        <form method="POST" action="{{ route('anggota.keranjang.add') }}" class="flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="number" name="qty" value="1" min="1" max="{{ max(1, $maxAddable) }}"
                                   {{ $maxAddable < 1 ? 'disabled' : '' }}
                                   class="w-16 px-2 py-2 text-sm text-center border border-slate-300 rounded-lg">
                            <button type="submit" {{ $maxAddable < 1 ? 'disabled' : '' }}
                                    class="flex-1 py-2 px-3 rounded-lg text-xs font-bold transition {{ $maxAddable < 1 ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 text-white' }}">
                                {{ $maxAddable < 1 ? 'Maks. Stok' : '+ Keranjang' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full polmart-card text-center py-12">
                    <div class="text-4xl mb-3">🔍</div>
                    <h3 class="font-bold text-slate-800">Tidak ada produk tersedia</h3>
                    <p class="text-sm text-slate-500 mt-1">Coba ubah kata kunci pencarian atau kategori.</p>
                </div>
            @endforelse
        </section>
    </div>
</x-app-layout>
