<x-app-layout>
    @section('title', 'Biaya Operasional (OPEX) - Polmart')
    @section('page_title', 'Biaya Operasional (OPEX)')

    <div class="space-y-6">
        <!-- Hero Header -->
        <section class="polmart-banner flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="relative z-10">
                <h1 class="text-2xl font-extrabold">Biaya Operasional (OPEX) 💸</h1>
                <p class="text-blue-100 text-sm mt-1">
                    Pencatatan dan pengelolaan beban pengeluaran rutin operasional koperasi Polmart.
                </p>
            </div>
            <div class="relative z-10">
                <a href="{{ route('admin.operating-expenses.create') }}" class="px-4 py-2.5 bg-white text-blue-700 rounded-lg text-sm font-bold shadow hover:bg-blue-50 transition inline-flex items-center gap-2">
                    <span>+</span>
                    <span>Catat Biaya Baru</span>
                </a>
            </div>
        </section>

        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span>✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- 3 Kartu Ringkasan Statistik Beban -->
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Beban Operasional</p>
                    <p class="text-2xl font-extrabold text-rose-600 mt-1">
                        Rp {{ number_format($totalAmount, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Total pengeluaran pada filter aktif</p>
                </div>
                <div class="polmart-stat-icon purple">💸</div>
            </div>

            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jumlah Transaksi Biaya</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">
                        {{ number_format($totalCount) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Kuitansi / bukti kas keluar tercatat</p>
                </div>
                <div class="polmart-stat-icon blue">🧾</div>
            </div>

            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rata-rata Pengeluaran</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">
                        Rp {{ number_format($avgAmount, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Rata-rata per bukti kas keluar</p>
                </div>
                <div class="polmart-stat-icon amber">📊</div>
            </div>
        </section>

        <!-- Filter & Pencarian -->
        <section class="polmart-card">
            <form method="GET" action="{{ route('admin.operating-expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama biaya, kode, catatan..."
                           class="polmart-input w-full text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori Beban</label>
                    <select name="category" class="polmart-select w-full text-xs">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}" {{ request('category') === $catKey ? 'selected' : '' }}>
                                {{ $catLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from', request('start_date')) }}"
                           class="polmart-input w-full text-xs">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to', request('end_date')) }}"
                           class="polmart-input w-full text-xs">
                </div>

                <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-5 pt-1">
                    <button type="submit" class="polmart-btn polmart-btn-primary polmart-btn-sm whitespace-nowrap">
                        🔍 Terapkan Filter
                    </button>
                    <a href="{{ route('admin.operating-expenses.index') }}" class="polmart-btn polmart-btn-secondary polmart-btn-sm whitespace-nowrap">
                        ↺ Reset
                    </a>
                </div>
            </form>
        </section>

        <!-- Tabel Daftar Pengeluaran Operasional -->
        <section class="polmart-card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-extrabold text-slate-900">Daftar Pengeluaran Operasional</h2>
                <span class="px-2.5 py-1 rounded bg-slate-100 text-slate-700 text-xs font-bold font-mono">
                    {{ $expenses->total() }} Data
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="polmart-table text-xs">
                    <thead>
                        <tr>
                            <th class="w-8">#</th>
                            <th>Kode &amp; Tanggal</th>
                            <th>Nama Pengeluaran</th>
                            <th>Kategori</th>
                            <th>Dicatat Oleh</th>
                            <th class="text-center">Bukti Nota</th>
                            <th class="text-right">Nominal (Rp)</th>
                            <th class="text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $idx => $expense)
                            <tr>
                                <td class="font-bold text-slate-400">
                                    {{ $expenses->firstItem() + $idx }}
                                </td>
                                <td>
                                    <div class="font-mono font-bold text-blue-700">{{ $expense->expense_code }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $expense->expense_date?->format('d/m/Y') }}</div>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-900">{{ $expense->name }}</div>
                                    @if ($expense->notes)
                                        <div class="text-[11px] text-slate-500 mt-0.5 line-clamp-1" title="{{ $expense->notes }}">
                                            {{ $expense->notes }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeColor = match ($expense->category) {
                                            'gaji' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'utilitas' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'sewa' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'pemeliharaan' => 'bg-orange-100 text-orange-800 border-orange-200',
                                            'perlengkapan' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'transportasi' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                                            'administrasi' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold border {{ $badgeColor }}">
                                        {{ $expense->category_label }}
                                    </span>
                                </td>
                                <td class="text-slate-600">
                                    {{ $expense->user?->name ?? 'Admin Sistem' }}
                                </td>
                                <td class="text-center">
                                    @if ($expense->receipt_path)
                                        <a href="{{ route('admin.operating-expenses.receipt', $expense) }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 font-semibold text-[11px]"
                                           title="Lihat Bukti Struk">
                                            <span>📷</span>
                                            <span>Bukti</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 font-mono text-[11px]">—</span>
                                    @endif
                                </td>
                                <td class="text-right font-bold text-rose-700">
                                    Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('admin.operating-expenses.edit', $expense) }}"
                                           class="px-2 py-1 rounded bg-slate-100 text-slate-700 hover:bg-blue-50 hover:text-blue-700 font-semibold transition"
                                           title="Edit Pengeluaran">
                                            ✏️ Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.operating-expenses.destroy', $expense) }}"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengeluaran {{ $expense->expense_code }} ({{ $expense->name }})?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-2 py-1 rounded bg-slate-100 text-slate-700 hover:bg-rose-50 hover:text-rose-700 font-semibold transition"
                                                    title="Hapus Pengeluaran">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-slate-400 py-10">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="text-3xl mb-2">💸</span>
                                        <p class="font-semibold text-slate-700">Belum ada catatan biaya operasional.</p>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            Gunakan tombol "+ Catat Biaya Baru" di atas untuk menambahkan pengeluaran operasional.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($expenses->hasPages())
                <div class="pt-4 border-t border-slate-100">
                    {{ $expenses->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
