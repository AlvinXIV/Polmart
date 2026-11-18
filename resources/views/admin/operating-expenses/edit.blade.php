<x-app-layout>
    <x-slot name="header">Edit Biaya Operasional</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Biaya Operasional</h1>
                <p class="text-sm text-slate-500 mt-1">Perbarui detail pencatatan biaya <span class="font-mono font-semibold text-blue-700">{{ $operatingExpense->expense_code }}</span>.</p>
            </div>
            <a href="{{ route('admin.operating-expenses.index') }}"
                class="polmart-btn bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs">
                <x-icon name="chevron-left" class="w-4 h-4" />
                Kembali ke Daftar
            </a>
        </div>

        <div class="polmart-card p-6">
            <form method="POST" action="{{ route('admin.operating-expenses.update', $operatingExpense) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="expense_code" class="block text-xs font-bold text-slate-700 mb-1">Kode Pengeluaran</label>
                        <input type="text" id="expense_code" value="{{ $operatingExpense->expense_code }}" disabled
                            class="polmart-input w-full text-sm bg-slate-100 text-slate-500 font-mono">
                    </div>

                    <div>
                        <label for="expense_date" class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pengeluaran <span class="text-rose-500">*</span></label>
                        <input type="date" id="expense_date" name="expense_date"
                            value="{{ old('expense_date', $operatingExpense->expense_date->toDateString()) }}"
                            required class="polmart-input w-full text-sm">
                        @error('expense_date')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Nama / Uraian Pengeluaran <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $operatingExpense->name) }}"
                        required class="polmart-input w-full text-sm">
                    @error('name')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-xs font-bold text-slate-700 mb-1">Kategori OPEX <span class="text-rose-500">*</span></label>
                        <select id="category" name="category" required class="polmart-select w-full text-sm">
                            @foreach ($categories as $key => $label)
                                <option value="{{ $key }}" @selected(old('category', $operatingExpense->category) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="amount" class="block text-xs font-bold text-slate-700 mb-1">Nominal Pengeluaran (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" id="amount" name="amount" value="{{ old('amount', (int) $operatingExpense->amount) }}"
                            min="1" step="1"
                            required class="polmart-input w-full text-sm">
                        @error('amount')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea id="notes" name="notes" rows="3"
                        class="polmart-input w-full text-sm">{{ old('notes', $operatingExpense->notes) }}</textarea>
                    @error('notes')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="receipt" class="block text-xs font-bold text-slate-700">Bukti Nota / Kwitansi</label>

                    @if ($operatingExpense->receipt_path)
                        <div class="flex flex-wrap items-center justify-between gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50">
                            <a href="{{ route('admin.operating-expenses.receipt', $operatingExpense) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-700 hover:underline">
                                <x-icon name="image" class="w-4 h-4" />
                                Lihat Bukti Tersimpan
                            </a>
                            <label class="inline-flex items-center gap-1.5 text-xs text-rose-700 cursor-pointer">
                                <input type="checkbox" name="delete_receipt" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                Hapus bukti lama
                            </label>
                        </div>
                    @endif

                    <input type="file" id="receipt" name="receipt" accept=".jpg,.jpeg,.png,.pdf"
                        class="block w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-[11px] text-slate-400">Upload file baru (JPG/PNG/PDF maks 5 MB) jika ingin mengganti bukti.</p>
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
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
