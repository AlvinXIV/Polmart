<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Admin / Pengurus Polmart') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                Selamat datang, <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->member_code }}) — Role: <strong>Admin</strong>.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.products.index') }}" class="block bg-white p-6 rounded-lg shadow-sm border hover:border-indigo-500">
                    <h3 class="font-semibold text-lg text-gray-800">Master Barang (FR-03)</h3>
                    <p class="text-sm text-gray-600 mt-1">Kelola SKU, harga satuan, foto produk, status aktif, dan restock.</p>
                </a>
                <a href="{{ route('admin.stock-mutations.index') }}" class="block bg-white p-6 rounded-lg shadow-sm border hover:border-indigo-500">
                    <h3 class="font-semibold text-lg text-gray-800">Mutasi Stok (FR-04)</h3>
                    <p class="text-sm text-gray-600 mt-1">Audit seluruh log stok masuk, keluar, dan penyesuaian.</p>
                </a>
                <a href="{{ route('admin.manual-orders.index') }}" class="block bg-white p-6 rounded-lg shadow-sm border hover:border-indigo-500">
                    <h3 class="font-semibold text-lg text-gray-800">Pesanan Walk-in (FR-09)</h3>
                    <p class="text-sm text-gray-600 mt-1">Input transaksi langsung di loket dengan status SELESAI.</p>
                </a>
                <a href="{{ route('admin.order-imports.index') }}" class="block bg-white p-6 rounded-lg shadow-sm border hover:border-indigo-500">
                    <h3 class="font-semibold text-lg text-gray-800">Impor Excel (FR-10)</h3>
                    <p class="text-sm text-gray-600 mt-1">Konversi riwayat transaksi lama dari Excel tanpa memotong stok.</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>