<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Master Barang & Inventaris</h2>
            <div class="space-x-2">
                <a href="{{ route('admin.stock-mutations.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm">Log Mutasi Stok</a>
                <a href="{{ route('admin.products.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">+ Tambah Barang</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="p-4 bg-red-100 text-red-800 rounded-md">{{ $errors->first() }}</div>
            @endif

            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari SKU, nama, atau kategori..." class="border-gray-300 rounded-md shadow-sm w-80">
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm">Cari</button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Foto</th>
                            <th class="px-4 py-3 text-left">SKU</th>
                            <th class="px-4 py-3 text-left">Nama Barang</th>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-right">Harga</th>
                            <th class="px-4 py-3 text-center">Stok</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left">Restock Cepat</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-4 py-3">
                                    @if ($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded">
                                    @else
                                        <span class="text-xs text-gray-400">No Img</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono">{{ $product->sku }}</td>
                                <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                                <td class="px-4 py-3">{{ $product->category }}</td>
                                <td class="px-4 py-3 text-right">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center font-bold">{{ $product->stock }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs rounded {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.products.restock', $product) }}" class="flex items-center gap-1">
                                        @csrf
                                        <input type="number" name="qty" min="1" placeholder="+Qty" required class="w-20 text-xs border-gray-300 rounded">
                                        <input type="text" name="notes" placeholder="Catatan" class="w-28 text-xs border-gray-300 rounded">
                                        <button type="submit" class="px-2 py-1 bg-emerald-600 text-white text-xs rounded">Tambah</button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-amber-600 hover:underline">
                                            {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-6 text-center text-gray-500">Belum ada data barang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>