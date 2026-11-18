<x-app-layout>
    @section('title', 'Master Barang & Inventaris - Polmart')
    @section('page_title', 'Master Barang & Inventaris')

    <div class="space-y-6">
        <!-- Hero Header -->
        <section class="polmart-banner flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="relative z-10">
                <h1 class="text-2xl font-extrabold">Master Barang & Inventaris 📦</h1>
                <p class="text-blue-100 text-sm mt-1">Kelola katalog master barang, SKU, harga satuan, status aktif, restock, dan mutasi stok (FR-03 & FR-04).</p>
            </div>
            <div class="relative z-10 flex flex-wrap gap-2">
                <a href="{{ route('admin.stock-mutations.index') }}" class="px-4 py-2.5 bg-white/15 border border-white/30 text-white rounded-lg text-sm font-semibold hover:bg-white/25">
                    ⇄ Log Mutasi Stok
                </a>
                <a href="{{ route('admin.products.create') }}" class="px-4 py-2.5 bg-white text-blue-700 rounded-lg text-sm font-bold shadow hover:bg-blue-50">
                    + Tambah Barang
                </a>
            </div>
        </section>

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

        <!-- Ringkasan Statistik Inventaris -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total Produk</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="polmart-stat-icon blue">📦</div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Stok Aman (&gt;10)</p>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['available'] }}</p>
                </div>
                <div class="polmart-stat-icon green">✓</div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Stok Menipis (1–10)</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['low'] }}</p>
                </div>
                <div class="polmart-stat-icon amber">⚠</div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Stok Habis (0)</p>
                    <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ $stats['empty'] }}</p>
                </div>
                <div class="polmart-stat-icon purple">!</div>
            </div>
        </section>

        <!-- Filter & Pencarian -->
        <section class="polmart-card">
            <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari SKU, nama barang, atau kategori..."
                       class="polmart-input sm:w-80">

                <select name="category" class="polmart-select sm:w-48">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>

                <select name="stock_status" class="polmart-select sm:w-44">
                    <option value="">Semua Kondisi Stok</option>
                    <option value="available" {{ request('stock_status') === 'available' ? 'selected' : '' }}>Stok Aman (&gt;10)</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Menipis (1–10)</option>
                    <option value="empty" {{ request('stock_status') === 'empty' ? 'selected' : '' }}>Habis (0)</option>
                </select>

                <button type="submit" class="polmart-btn polmart-btn-primary">Filter</button>
                @if (request()->hasAny(['search', 'category', 'stock_status']))
                    <a href="{{ route('admin.products.index') }}" class="polmart-btn polmart-btn-secondary">Reset</a>
                @endif
            </form>
        </section>

        <!-- Tabel Master Barang -->
        <div class="polmart-table-wrapper overflow-x-auto">
            <table class="polmart-table">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>SKU</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th class="text-right">Harga Modal</th>
                        <th class="text-right">Harga Jual</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Status</th>
                        <th>Restock Cepat (FR-04)</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                @if ($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded-lg border border-slate-200">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 text-xs font-semibold">
                                        📦
                                    </div>
                                @endif
                            </td>
                            <td class="font-mono text-xs font-bold text-blue-700">{{ $product->sku }}</td>
                            <td class="font-semibold text-slate-900">{{ $product->name }}</td>
                            <td>
                                <span class="polmart-badge polmart-badge-blue">{{ $product->category }}</span>
                            </td>
                            <td class="text-right font-medium text-slate-600">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                            <td class="text-right font-semibold text-slate-900">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if ($product->stock > 10)
                                    <span class="polmart-badge polmart-badge-green">{{ $product->stock }}</span>
                                @elseif ($product->stock > 0)
                                    <span class="polmart-badge polmart-badge-amber">{{ $product->stock }}</span>
                                @else
                                    <span class="polmart-badge polmart-badge-red">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="polmart-badge {{ $product->is_active ? 'polmart-badge-green' : 'polmart-badge-red' }}">
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.products.restock', $product) }}" class="flex items-center gap-1.5">
                                    @csrf
                                    <input type="number" name="qty" min="1" placeholder="+Qty" required class="w-20 px-2 py-1 text-xs border border-slate-300 rounded-md">
                                    <input type="text" name="notes" placeholder="Catatan" class="w-28 px-2 py-1 text-xs border border-slate-300 rounded-md">
                                    <button type="submit" class="polmart-btn polmart-btn-success polmart-btn-sm">Tambah</button>
                                </form>
                            </td>
                            <td class="text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-600 font-semibold hover:underline text-xs">Edit</a>
                                <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-amber-600 font-semibold hover:underline text-xs">
                                        {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-400">Belum ada data barang yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>
