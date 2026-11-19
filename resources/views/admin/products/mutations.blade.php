<x-app-layout>
    @section('title', 'Mutasi Stok - Polmart')
    @section('page_title', 'Log Mutasi Stok')

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Mutasi Stok</h2>
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm">Kembali ke Stok Barang</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="polmart-card">
                <form method="GET" action="{{ route('admin.stock-mutations.index') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="start_date" class="polmart-label">Dari Tanggal</label>
                        <input id="start_date" type="date" name="start_date" value="{{ $startDateInput ?? '' }}" class="polmart-input sm:w-48">
                    </div>
                    <div>
                        <label for="end_date" class="polmart-label">Sampai Tanggal</label>
                        <input id="end_date" type="date" name="end_date" value="{{ $endDateInput ?? '' }}" class="polmart-input sm:w-48">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="polmart-btn polmart-btn-primary">Filter</button>
                        @if (! empty($startDateInput) || ! empty($endDateInput))
                            <a href="{{ route('admin.stock-mutations.index') }}" class="polmart-btn polmart-btn-secondary">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Waktu</th>
                            <th class="px-4 py-3 text-left">SKU / Barang</th>
                            <th class="px-4 py-3 text-center">Tipe</th>
                            <th class="px-4 py-3 text-center">Qty</th>
                            <th class="px-4 py-3 text-center">Stok Sebelum</th>
                            <th class="px-4 py-3 text-center">Stok Sesudah</th>
                            <th class="px-4 py-3 text-left">Referensi</th>
                            <th class="px-4 py-3 text-left">Oleh / Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($mutations as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-gray-500">{{ $item->product?->sku }}</span><br>
                                    <span class="font-medium">{{ $item->product?->name }}</span>
                                </td>
                                <td class="px-4 py-3 text-center uppercase font-semibold">{{ $item->type }}</td>
                                <td class="px-4 py-3 text-center font-bold">{{ $item->qty }}</td>
                                <td class="px-4 py-3 text-center">{{ $item->stock_before }}</td>
                                <td class="px-4 py-3 text-center">{{ $item->stock_after }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $item->reference ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs text-gray-500">{{ $item->user?->name ?? 'Sistem' }}</span><br>
                                    <span>{{ $item->notes }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-gray-500">Belum ada riwayat mutasi stok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $mutations->links() }}
        </div>
    </div>
</x-app-layout>