<x-app-layout>
    @section('title', 'Catat Biaya Operasional - Polmart')
    @section('page_title', 'Catat Biaya Operasional Baru')

    <div class="py-6">
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Header Navigasi Kembali -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900">Catat Pengeluaran Operasional Baru 💸</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Input bukti pengeluaran beban rutin operasional koperasi</p>
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
                        Pengadaan persediaan barang dagangan dicatat melalui menu <em>Master Barang / Restock</em> agar biaya modal diperhitungkan secara akurat melalui Harga Pokok Penjualan (HPP) saat barang terjual, sehingga tidak terjadi perhitungan ganda (*double counting*).
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
                <form method="POST" action="{{ route('admin.operating-expenses.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="expense_code_preview" value="Kode Kuitansi (Otomatis)" />
                            <input id="expense_code_preview" type="text"
                                   class="mt-1 block w-full polmart-input bg-slate-50 text-slate-500 font-mono text-xs cursor-not-allowed"
                                   value="{{ $previewCode }}" disabled title="Kode dihasilkan secara otomatis oleh sistem">
                            <p class="text-[10px] text-slate-400 mt-1">Dibuat otomatis sesuai tanggal pengeluaran</p>
                        </div>

                        <div>
                            <x-input-label for="expense_date" value="Tanggal Pengeluaran *" />
                            <input id="expense_date" name="expense_date" type="date"
                                   class="mt-1 block w-full polmart-input text-xs"
                                   value="{{ old('expense_date', now()->toDateString()) }}" required>
                            <x-input-error :messages="$errors->get('expense_date')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="name" value="Nama / Uraian Pengeluaran *" />
                        <input id="name" name="name" type="text"
                               class="mt-1 block w-full polmart-input text-xs"
                               value="{{ old('name') }}"
                               placeholder="Contoh: Pembayaran Listrik &amp; Air PLN Toko Bulan November" required>
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="category" value="Kategori Beban Operasional *" />
                            <select id="category" name="category" class="mt-1 block w-full polmart-select text-xs" required>
                                <option value="">-- Pilih Kategori Beban --</option>
                                @foreach ($categories as $catKey => $catLabel)
                                    <option value="{{ $catKey }}" {{ old('category') === $catKey ? 'selected' : '' }}>
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
                                   value="{{ old('amount') }}"
                                   placeholder="Contoh: 750000" required>
                            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Catatan Tambahan / Rincian Bukti (Opsional)" />
                        <textarea id="notes" name="notes" rows="3"
                                  class="mt-1 block w-full polmart-input text-xs py-2"
                                  placeholder="Contoh: Nomor ID Pelanggan PLN 53123456789. Dibayar via transfer rekening kas operasional.">{{ old('notes') }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="receipt" value="Unggah Foto Bukti Struk / Kuitansi (Opsional)" />
                        <input id="receipt" name="receipt" type="file" accept="image/png,image/jpeg,image/jpg"
                               class="mt-1 block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        <p class="text-[10px] text-slate-400 mt-1">Format gambar: JPG, JPEG, PNG (Maksimal 2MB)</p>
                        <x-input-error :messages="$errors->get('receipt')" class="mt-1" />
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                        <a href="{{ route('admin.operating-expenses.index') }}" class="polmart-btn polmart-btn-secondary polmart-btn-sm">
                            Batal
                        </a>
                        <button type="submit" class="polmart-btn polmart-btn-primary polmart-btn-sm font-bold">
                            💾 Simpan Pengeluaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
