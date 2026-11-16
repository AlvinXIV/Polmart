<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Input Pesanan Manual / Walk-in</h2>
    </x-slot>

    <div class="py-8" x-data="manualOrderForm({{ $products->toJson() }})">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg space-y-6">
                @if ($errors->any())
                    <div class="p-4 bg-red-100 text-red-800 rounded-md text-sm">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.manual-orders.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="user_id" value="Pilih Anggota Terdaftar (Opsional)" />
                            <select id="user_id" name="user_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">-- Walk-in / Non-Akun --</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}" {{ old('user_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->member_code }} - {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="customer_name" value="Nama Pemesan (Otomatis terisi jika pilih anggota)" />
                            <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full" :value="old('customer_name')" placeholder="Isi jika tidak memilih akun anggota" />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">Daftar Barang Dibeli</h3>
                            <button type="button" @click="addRow()" class="px-3 py-1.5 bg-emerald-600 text-white text-xs rounded-md">+ Tambah Baris</button>
                        </div>

                        <template x-for="(row, index) in rows" :key="index">
                            <div class="grid grid-cols-12 gap-2 items-center bg-gray-50 p-3 rounded-md">
                                <div class="col-span-6">
                                    <select :name="`items[${index}][product_id]`" x-model="row.product_id" required class="w-full border-gray-300 rounded-md text-sm">
                                        <option value="">-- Pilih Barang --</option>
                                        <template x-for="product in products" :key="product.id">
                                            <option :value="product.id" x-text="`${product.sku} - ${product.name} (Stok: ${product.stock} | Rp ${Number(product.price).toLocaleString('id-ID')})`"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <input type="number" :name="`items[${index}][qty]`" x-model.number="row.qty" min="1" required class="w-full border-gray-300 rounded-md text-sm" placeholder="Qty">
                                </div>
                                <div class="col-span-3 text-right text-sm font-medium">
                                    Rp <span x-text="rowSubtotal(row).toLocaleString('id-ID')"></span>
                                </div>
                                <div class="col-span-1 text-right">
                                    <button type="button" @click="removeRow(index)" x-show="rows.length > 1" class="text-red-600 text-xs font-bold">✕</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-between border-t pt-4">
                        <div class="text-lg font-bold text-gray-900">
                            Total Transaksi: Rp <span x-text="grandTotal().toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.manual-orders.index') }}" class="px-4 py-2 bg-gray-200 rounded-md text-sm">Batal</a>
                            <x-primary-button>Simpan & Selesaikan Transaksi</x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function manualOrderForm(products) {
            return {
                products,
                rows: [{ product_id: '', qty: 1 }],
                addRow() {
                    this.rows.push({ product_id: '', qty: 1 });
                },
                removeRow(index) {
                    this.rows.splice(index, 1);
                },
                findProduct(id) {
                    return this.products.find(p => String(p.id) === String(id));
                },
                rowSubtotal(row) {
                    const product = this.findProduct(row.product_id);
                    if (!product || !row.qty) return 0;
                    return Number(product.price) * Number(row.qty);
                },
                grandTotal() {
                    return this.rows.reduce((sum, row) => sum + this.rowSubtotal(row), 0);
                }
            };
        }
    </script>
</x-app-layout>