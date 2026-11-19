<x-app-layout>
    <x-slot name="header">Catat Biaya Operasional Baru</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Catat Biaya Operasional</h1>
                <p class="text-sm text-slate-500 mt-1">Tambahkan pengeluaran operasional rutin untuk akurasi Laba Bersih.</p>
            </div>
            <a href="{{ route('admin.operating-expenses.index') }}"
                class="polmart-btn bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs">
                <x-icon name="chevron-left" class="w-4 h-4" />
                Kembali ke Daftar
            </a>
        </div>

        {{-- Reminder Note: OPEX vs HPP --}}
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <x-icon name="alert-triangle" class="w-5 h-5" />
            </div>
            <div class="text-xs text-amber-900 leading-relaxed">
                <p class="font-bold">Jangan Mencampur Pembelian Stok (Kulakan) ke OPEX!</p>
                <p class="mt-0.5">
                    Pembelian stok barang dagangan sudah dihitung melalui <strong>HPP (Harga Pokok Penjualan)</strong> di Stok Barang.
                    Masukkan hanya biaya di luar harga pokok barang seperti gaji, listrik, air, sewa, kemasan/plastik, atau perawatan toko.
                </p>
            </div>
        </div>

        <div class="polmart-card p-6">
            <form method="POST" action="{{ route('admin.operating-expenses.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="expense_code_preview" class="block text-xs font-bold text-slate-700 mb-1">Kode Pengeluaran (Otomatis)</label>
                        <input type="text" id="expense_code_preview" value="{{ $previewCode }}" disabled
                            class="polmart-input w-full text-sm bg-slate-100 text-slate-500 font-mono">
                    </div>

                    <div>
                        <label for="expense_date" class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengeluaran <span class="text-rose-500">*</span></label>
                        <input type="date" id="expense_date" name="expense_date"
                            value="{{ old('expense_date', now()->toDateString()) }}"
                            required class="polmart-input w-full text-sm">
                        @error('expense_date')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Nama / Uraian Pengeluaran <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        placeholder="Contoh: Pembayaran Token Listrik November 2026"
                        required class="polmart-input w-full text-sm">
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-xs font-bold text-slate-700 mb-1">Kategori OPEX <span class="text-rose-500">*</span></label>
                        <select id="category" name="category" required class="polmart-select w-full text-sm">
                            <option value="" disabled @selected(!old('category'))>-- Pilih Kategori --</option>
                            @foreach ($categories as $key => $label)
                                <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="amount" class="block text-xs font-bold text-slate-700 mb-1">Nominal Pengeluaran (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" id="amount" name="amount" value="{{ old('amount') }}"
                            min="1" step="1" placeholder="Contoh: 150000"
                            required class="polmart-input w-full text-sm">
                        @error('amount')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea id="notes" name="notes" rows="3"
                        placeholder="Nomor invoice PLN, nama penerima, atau keterangan tambahan..."
                        class="polmart-input w-full text-sm">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="receipt" class="block text-xs font-bold text-slate-700 mb-1">Upload Bukti Nota / Kwitansi (Opsional)</label>
                    <input type="file" id="receipt" name="receipt" accept=".jpg,.jpeg,.png,.pdf"
                        class="block w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-[11px] text-slate-400 mt-1">Format: JPG, PNG, atau PDF maksimal 5 MB. Disimpan aman di storage privat.</p>
                    @error('receipt')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <a href="{{ route('admin.operating-expenses.index') }}"
                        class="polmart-btn bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs">
                        Batal
                    </a>
                    <button type="submit" class="polmart-btn polmart-btn-primary text-xs">
                        <x-icon name="save" class="w-4 h-4" />
                        Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
