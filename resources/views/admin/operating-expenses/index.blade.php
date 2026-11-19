<x-app-layout>
    <x-slot name="header">Biaya Operasional (OPEX)</x-slot>

    <div class="space-y-6">
        {{-- Header Banner --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Biaya Operasional (OPEX)</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Pencatatan biaya operasional harian koperasi (gaji, listrik, sewa, perlengkapan, dll.) terpisah dari HPP.
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.operating-expenses.create') }}" class="polmart-btn polmart-btn-primary text-sm">
                    <x-icon name="plus" class="w-4 h-4" />
                    Catat Biaya Baru
                </a>
            </div>
        </div>

        {{-- Summary Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total OPEX (Filter Aktif)</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">
                        Rp {{ number_format($totalAmount, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-500 mt-1">Akumulasi beban operasional sesuai filter</p>
                </div>
                <div class="polmart-stat-icon bg-blue-50 text-blue-600">
                    <x-icon name="banknote" class="w-5 h-5" />
                </div>
            </div>

            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Jumlah Transaksi OPEX</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">
                        {{ number_format($totalCount, 0, ',', '.') }} <span class="text-sm font-normal text-slate-500">catatan</span>
                    </p>
                    <p class="text-xs text-slate-500 mt-1">Total entri biaya operasional</p>
                </div>
                <div class="polmart-stat-icon bg-slate-100 text-slate-600">
                    <x-icon name="receipt" class="w-5 h-5" />
                </div>
            </div>

            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Rata-rata Per Transaksi</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">
                        Rp {{ number_format($avgAmount, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-500 mt-1">Nilai rata-rata setiap pencatatan</p>
                </div>
                <div class="polmart-stat-icon bg-slate-100 text-slate-600">
                    <x-icon name="bar-chart" class="w-5 h-5" />
                </div>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="polmart-card p-4">
            <form method="GET" action="{{ route('admin.operating-expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <div class="lg:col-span-2">
                    <label for="search" class="block text-xs font-bold text-slate-600 mb-1">Cari Kode / Nama / Catatan</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Contoh: OPX-2026 atau Token Listrik"
                        class="polmart-input w-full text-sm">
                </div>

                <div>
                    <label for="category" class="block text-xs font-bold text-slate-600 mb-1">Kategori</label>
                    <select id="category" name="category" class="polmart-select w-full text-sm">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $key => $label)
                            <option value="{{ $key }}" @selected(request('category') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-xs font-bold text-slate-600 mb-1">Dari Tanggal</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="polmart-input w-full text-sm">
                </div>

                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <label for="date_to" class="block text-xs font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="polmart-input w-full text-sm">
                    </div>
                </div>

                <div class="sm:col-span-2 lg:col-span-5 flex flex-wrap items-center justify-end gap-2 pt-1">
                    <a href="{{ route('admin.operating-expenses.index') }}" class="polmart-btn bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs">
                        Reset
                    </a>
                    <button type="submit" class="polmart-btn polmart-btn-primary text-xs">
                        <x-icon name="search" class="w-4 h-4" />
                        Terapkan Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="polmart-card overflow-hidden">
            @if ($expenses->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <x-icon name="banknote" class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-bold text-slate-800">Belum ada catatan biaya operasional</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Catat pengeluaran rutin seperti listrik, gaji petugas, sewa, atau perlengkapan toko agar perhitungan Laba Bersih lebih akurat.
                    </p>
                    <a href="{{ route('admin.operating-expenses.create') }}" class="polmart-btn polmart-btn-primary text-xs mt-4 inline-flex">
                        <x-icon name="plus" class="w-4 h-4" />
                        Catat Biaya Pertama
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="polmart-table w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th class="w-12">#</th>
                                <th>Kode & Tanggal</th>
                                <th>Nama Pengeluaran</th>
                                <th>Kategori</th>
                                <th>Dicatat Oleh</th>
                                <th class="text-center">Bukti Nota</th>
                                <th class="text-right">Nominal (Rp)</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($expenses as $index => $expense)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="text-xs text-slate-400 font-semibold">
                                        {{ $expenses->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <div class="font-mono text-xs font-bold text-blue-700">{{ $expense->expense_code }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $expense->expense_date->format('d/m/Y') }}</div>
                                    </td>
                                    <td>
                                        <div class="font-bold text-slate-800">{{ $expense->name }}</div>
                                        @if ($expense->notes)
                                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ $expense->notes }}</p>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                            {{ $expense->category_label }}
                                        </span>
                                    </td>
                                    <td class="text-xs text-slate-600">
                                        {{ $expense->user->name ?? 'System' }}
                                    </td>
                                    <td class="text-center">
                                        @if ($expense->receipt_path)
                                            <a href="{{ route('admin.operating-expenses.receipt', $expense) }}" target="_blank"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                                                <x-icon name="image" class="w-4 h-4" />
                                                Lihat Bukti
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400">Tidak ada</span>
                                        @endif
                                    </td>
                                    <td class="text-right font-bold text-slate-900 whitespace-nowrap">
                                        Rp {{ number_format((float) $expense->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">
                                        <div class="inline-flex items-center justify-end gap-1.5">
                                            <a href="{{ route('admin.operating-expenses.edit', $expense) }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors"
                                                title="Edit Biaya">
                                                <x-icon name="pencil" class="w-4 h-4" />
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.operating-expenses.destroy', $expense) }}"
                                                onsubmit="return confirm('Hapus catatan biaya {{ $expense->expense_code }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors"
                                                    title="Hapus Biaya">
                                                    <x-icon name="x" class="w-4 h-4" />
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($expenses->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $expenses->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
