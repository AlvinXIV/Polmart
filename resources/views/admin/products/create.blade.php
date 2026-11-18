<x-app-layout>
    @section('title', 'Tambah Barang - Polmart')
    @section('page_title', 'Tambah Master Barang')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Master Barang Baru</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="sku" value="Kode Barang (SKU)" />
                        <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full" :value="old('sku')" required />
                        <x-input-error :messages="$errors->get('sku')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="name" value="Nama Barang" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="category" value="Kategori" />
                        <x-text-input id="category" name="category" type="text" class="mt-1 block w-full" :value="old('category')" placeholder="Contoh: Alat Tulis, Makanan, Minuman" required />
                        <x-input-error :messages="$errors->get('category')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="cost_price" value="Harga Modal (Rp)" />
                            <x-text-input id="cost_price" name="cost_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('cost_price', 0)" required />
                            <x-input-error :messages="$errors->get('cost_price')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="price" value="Harga Jual Satuan (Rp)" />
                            <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price')" required />
                            <x-input-error :messages="$errors->get('price')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="stock" value="Jumlah Stok Awal" />
                        <x-text-input id="stock" name="stock" type="number" min="0" class="mt-1 block w-full" :value="old('stock', 0)" required />
                        <x-input-error :messages="$errors->get('stock')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="image" value="Foto Produk (Opsional)" />
                        <input id="image" name="image" type="file" accept="image/*" class="mt-1 block w-full text-sm">
                        <x-input-error :messages="$errors->get('image')" class="mt-1" />
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-gray-200 rounded-md text-sm">Batal</a>
                        <x-primary-button>Simpan Barang</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>