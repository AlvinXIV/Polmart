<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Pesanan Walk-in: {{ $order->order_code }}</h2>
            <a href="{{ route('admin.manual-orders.index') }}" class="px-4 py-2 bg-gray-200 rounded-md text-sm">← Kembali</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg space-y-4">
                @if (session('success'))
                    <div class="p-4 bg-green-100 text-green-800 rounded-md text-sm">{{ session('success') }}</div>
                @endif

                <div class="grid grid-cols-2 gap-2 text-sm border-b pb-4">
                    <div><span class="text-gray-500">Kode Pesanan:</span> <strong class="font-mono">{{ $order->order_code }}</strong></div>
                    <div><span class="text-gray-500">Status:</span> <strong class="text-green-700">{{ $order->status }}</strong></div>
                    <div><span class="text-gray-500">Pemesan:</span> <strong>{{ $order->customer_name }}</strong></div>
                    <div><span class="text-gray-500">Waktu:</span> <strong>{{ $order->transaction_date->format('d/m/Y H:i') }}</strong></div>
                </div>

                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Barang</th>
                            <th class="px-3 py-2 text-center">Qty</th>
                            <th class="px-3 py-2 text-right">Harga Satuan</th>
                            <th class="px-3 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-3 py-2">{{ $item->product?->sku }} - {{ $item->product?->name }}</td>
                                <td class="px-3 py-2 text-center">{{ $item->qty }}</td>
                                <td class="px-3 py-2 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="text-right text-lg font-bold border-t pt-3">
                    Total Dibayar: Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>