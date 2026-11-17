<x-app-layout>
    @section('title', 'Pesanan Walk-in - Polmart')
    @section('page_title', 'Riwayat Pesanan Walk-in')

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Pesanan Manual / Walk-in (FR-09)</h2>
            <a href="{{ route('admin.manual-orders.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold">+ Input Pesanan Walk-in</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('success') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Kode Pesanan</th>
                            <th class="px-4 py-3 text-left">Waktu Transaksi</th>
                            <th class="px-4 py-3 text-left">Pemesan</th>
                            <th class="px-4 py-3 text-center">Jumlah Item</th>
                            <th class="px-4 py-3 text-right">Total Nominal</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-4 py-3 font-mono font-semibold">{{ $order->order_code }}</td>
                                <td class="px-4 py-3">{{ $order->transaction_date->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    {{ $order->customer_name }}
                                    @if ($order->user)
                                        <span class="text-xs text-gray-500">({{ $order->user->member_code }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">{{ $order->items->sum('qty') }}</td>
                                <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 font-semibold">{{ $order->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.manual-orders.show', $order) }}" class="text-indigo-600 hover:underline">Struk</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada transaksi manual walk-in.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>