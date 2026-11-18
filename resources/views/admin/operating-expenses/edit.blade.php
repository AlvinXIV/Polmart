<x-app-layout>
    @section('title', 'Ubah Biaya Operasional - Polmart')
    @section('page_title', 'Ubah Catatan Biaya Operasional')

    <div class="py-6">
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header Navigasi Kembali -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900">Ubah Catatan Pengeluaran Operasional ✏️</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Perbarui rincian pengeluaran {{ $operatingExpense->expense_code }}</p>
                </div>
                <a href="{{ route('admin.operating-expenses.index') }}" class="polmart-btn polmart-btn-secondary polmart-btn-sm">
                    ← Kembali ke Daftar
                </a>
            </div>

            <!-- Peringatan Batasan OPEX vs HPP (Wajib) -->
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-3 shadow-sm">
                <span class="text-lg select-none">⚠️</span>
                <div class="space-y-1 leading-relaxed">
                    <p class="font-bold text-amber-950">
                        Perhatian Batasan Biaya Operasional:
                    </p>
                    <p>
                        <strong>Biaya pembelian atau restok barang dagang bukan biaya operasional dan tidak boleh dicatat di sini.</strong>
                    </p>
                    <p class="text-amber-800">
                        Pengadaan persediaan barang dagangan dicatat melalui menu <em>Master Barang / Restock</em> agar biaya modal diperhitungkan secara akurat melalui Harga Pokok Penjualan (HPP) saat barang terjual.
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-medium">
                    <p class="font-bold mb-1">Terjadi kesalahan pada data yang Anda masukkan:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <form method="POST" action="{{ route('admin.operating-expenses.update', $operatingExpense) }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="expense_code" value="Kode Kuitansi" />
                            <input id="expense_code" type="text"
                                   class="mt-1 block w-full polmart-input bg-slate-50 text-slate-500 font-mono text-xs cursor-not-allowed"
                                   value="{{ $operatingExpense->expense_code }}" disabled>
                            <p class="text-[10px] text-slate-400 mt-1">Kode unik transaksi tidak dapat diubah</p>
                        </div>

                        <div>
                            <x-input-label for="expense_date" value="Tanggal Pengeluaran *" />
                            <input id="expense_date" name="expense_date" type="date"
                                   class="mt-1 block w-full polmart-input text-xs"
                                   value="{{ old('expense_date', $operatingExpense->expense_date?->format('Y-m-d')) }}" required>
                            <x-input-error :messages="$errors->get('expense_date')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="name" value="Nama / Uraian Pengeluaran *" />
                        <input id="name" name="name" type="text"
                               class="mt-1 block w-full polmart-input text-xs"
                               value="{{ old('name', $operatingExpense->name) }}" required>
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="category" value="Kategori Beban Operasional *" />
                            <select id="category" name="category" class="mt-1 block w-full polmart-select text-xs" required>
                                <option value="">-- Pilih Kategori Beban --</option>
                                @foreach ($categories as $catKey => $catLabel)
                                    <option value="{{ $catKey }}" {{ old('category', $operatingExpense->category) === $catKey ? 'selected' : '' }}>
                                        {{ $catLabel }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="amount" value="Nominal Pengeluaran (Rp) *" />
                            <input id="amount" name="amount" type="number" step="0.01" min="1"
                                   class="mt-1 block w-full polmart-input text-xs"
                                   value="{{ old('amount', $operatingExpense->amount) }}" required>
                            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Catatan Tambahan / Rincian Bukti (Opsional)" />
                        <textarea id="notes" name="notes" rows="3"
                                  class="mt-1 block w-full polmart-input text-xs py-2">{{ old('notes', $operatingExpense->notes) }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="receipt" value="Ganti Foto Bukti Struk / Kuitansi (Opsional)" />
                        @if ($operatingExpense->receipt_path)
                            <div class="mb-3 p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-600 font-medium">Bukti tersimpan saat ini:</span>
                                    <a href="{{ route('admin.operating-expenses.receipt', $operatingExpense) }}" target="_blank"
                                       class="font-semibold text-blue-700 hover:underline inline-flex items-center gap-1">
                                        <span>📷</span>
                                        <span>Buka Bukti Tersimpan</span>
                                    </a>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer pt-2 border-t border-slate-200/60 text-xs text-rose-700 font-medium">
                                    <input type="checkbox" name="delete_receipt" value="1" {{ old('delete_receipt') ? 'checked' : '' }}
                                           class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                    <span>Hapus bukti nota tersimpan (tanpa mengganti file baru)</span>
                                </label>
                            </div>
                        @endif
                        <input id="receipt" name="receipt" type="file" accept="image/png,image/jpeg,image/jpg"
                               class="mt-1 block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika tidak ingin mengganti file. Format: JPG, JPEG, PNG (Maks 2MB)</p>
                        <x-input-error :messages="$errors->get('receipt')" class="mt-1" />
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                        <a href="{{ route('admin.operating-expenses.index') }}" class="polmart-btn polmart-btn-secondary polmart-btn-sm">
                            Batal
                        </a>
                        <button type="submit" class="polmart-btn polmart-btn-primary polmart-btn-sm font-bold">
                            💾 Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
