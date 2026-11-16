<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Impor Riwayat Pesanan Excel (FR-10)</h2>
            <a href="{{ route('admin.order-imports.template') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm">
                ↓ Download Template Excel (.xlsx)
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="p-4 bg-red-100 text-red-800 rounded-md">{{ $errors->first() }}</div>
            @endif

            <!-- Form Upload untuk Pratinjau -->
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="font-semibold text-gray-800 mb-2">1. Unggah File Excel / CSV Sistem Lama</h3>
                <p class="text-xs text-gray-500 mb-4">
                    Pastikan kolom <code>kode_barang</code> cocok dengan SKU pada Master Barang. Data hasil impor tercatat dengan status <strong>SELESAI</strong> dan <strong>tidak memotong stok fisik gudang saat ini</strong>.
                </p>

                <form method="POST" action="{{ route('admin.order-imports.preview') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="text-sm border border-gray-300 rounded-md p-1.5">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">
                        Validasi & Pratinjau Data
                    </button>
                </form>
            </div>

            <!-- Hasil Pratinjau Validasi -->
            @if ($preview)
                <div class="bg-white p-6 shadow-sm sm:rounded-lg space-y-4 border-l-4 border-indigo-600">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">2. Ringkasan Pratinjau: {{ $preview['original_name'] }}</h3>
                            <div class="flex gap-4 mt-2 text-sm">
                                <span>Total Baris: <strong>{{ $preview['total_rows'] }}</strong></span>
                                <span class="text-green-700">Valid: <strong>{{ $preview['valid_count'] }}</strong></span>
                                <span class="text-red-700">Galat: <strong>{{ $preview['invalid_count'] }}</strong></span>
                                <span>Estimasi Omzet Valid: <strong>Rp {{ number_format($preview['valid_total_amount'], 0, ',', '.') }}</strong></span>
                            </div>
                        </div>

                        @if ($preview['valid_count'] > 0)
                            <form method="POST" action="{{ route('admin.order-imports.store') }}">
                                @csrf
                                <input type="hidden" name="temp_path" value="{{ $preview['temp_path'] }}">
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold">
                                    ✓ Konfirmasi & Impor {{ $preview['valid_count'] }} Baris Valid
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left">Baris</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                    <th class="px-3 py-2 text-left">Tanggal</th>
                                    <th class="px-3 py-2 text-left">Kode Pesanan</th>
                                    <th class="px-3 py-2 text-left">Pemesan</th>
                                    <th class="px-3 py-2 text-left">SKU</th>
                                    <th class="px-3 py-2 text-center">Qty</th>
                                    <th class="px-3 py-2 text-right">Harga Satuan</th>
                                    <th class="px-3 py-2 text-left">Keterangan Validasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($preview['rows'] as $row)
                                    <tr class="{{ $row['is_valid'] ? '' : 'bg-red-50' }}">
                                        <td class="px-3 py-2">{{ $row['row_number'] }}</td>
                                        <td class="px-3 py-2">
                                            @if ($row['is_valid'])
                                                <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded font-semibold">VALID</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded font-semibold">GALAT</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2">{{ $row['transaction_date_display'] }}</td>
                                        <td class="px-3 py-2 font-mono">{{ $row['order_code'] }}</td>
                                        <td class="px-3 py-2">{{ $row['customer_name'] }}</td>
                                        <td class="px-3 py-2 font-mono">{{ $row['sku'] }}</td>
                                        <td class="px-3 py-2 text-center">{{ $row['qty'] }}</td>
                                        <td class="px-3 py-2 text-right">Rp {{ number_format($row['unit_price'], 0, ',', '.') }}</td>
                                        <td class="px-3 py-2 text-red-700">
                                            {{ $row['is_valid'] ? 'Siap diimpor' : implode('; ', $row['errors']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Riwayat Pesanan Hasil Impor Excel -->
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <div class="px-4 py-3 border-b font-semibold text-gray-800 text-sm">
                    Riwayat Pesanan Hasil Impor Excel (source = import_excel)
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Kode Pesanan</th>
                            <th class="px-4 py-3 text-left">Tanggal Transaksi</th>
                            <th class="px-4 py-3 text-left">Nama Pemesan</th>
                            <th class="px-4 py-3 text-center">Total Item</th>
                            <th class="px-4 py-3 text-right">Total Nominal</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($importedOrders as $order)
                            <tr>
                                <td class="px-4 py-3 font-mono font-semibold">{{ $order->order_code }}</td>
                                <td class="px-4 py-3">{{ $order->transaction_date->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $order->customer_name }}</td>
                                <td class="px-4 py-3 text-center">{{ $order->items->sum('qty') }}</td>
                                <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 font-semibold">{{ $order->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada riwayat pesanan hasil impor Excel.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $importedOrders->links() }}
        </div>
    </div>
</x-app-layout>