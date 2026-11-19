<x-app-layout>
    @section('title', 'Edit Barang - Polmart')
    @section('page_title', 'Edit Master Barang')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Barang: {{ $product->name }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="sku" value="Kode Barang (SKU)" />
                        <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full" :value="old('sku', $product->sku)" required />
                        <x-input-error :messages="$errors->get('sku')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="barcode" value="Barcode EAN-13 (Opsional)" />
                        <x-text-input id="barcode" name="barcode" type="text" class="mt-1 block w-full font-mono" :value="old('barcode', $product->barcode)" placeholder="Contoh: 8996001300015" />
                        <x-input-error :messages="$errors->get('barcode')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="name" value="Nama Barang" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $product->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="category" value="Kategori" />
                        <x-text-input id="category" name="category" type="text" class="mt-1 block w-full" :value="old('category', $product->category)" required />
                        <x-input-error :messages="$errors->get('category')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="cost_price" value="Harga Modal (Rp)" />
                            <x-text-input id="cost_price" name="cost_price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('cost_price', $product->cost_price)" required />
                            <x-input-error :messages="$errors->get('cost_price')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="price" value="Harga Jual Satuan (Rp)" />
                            <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('price', $product->price)" required />
                            <x-input-error :messages="$errors->get('price')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="image" value="Ganti Foto Produk (Opsional)" />
                        <input id="image" name="image" type="file" accept="image/*" class="mt-1 block w-full text-sm">
                        <x-input-error :messages="$errors->get('image')" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="text-sm text-gray-700">Tampilkan aktif di katalog anggota</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-4">
                        <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-gray-200 rounded-md text-sm">Batal</a>
                        <x-primary-button>Perbarui Barang</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>