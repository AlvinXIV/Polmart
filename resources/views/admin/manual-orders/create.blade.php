<x-app-layout>
    @section('title', 'Kasir Walk-in - Polmart')
    @section('page_title', 'Kasir Transaksi Walk-in')

    <div class="space-y-6" x-data="walkinCashier({{ $products->toJson() }}, {{ $members->toJson() }})">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Kasir Transaksi Walk-in</h1>
                <p class="text-sm text-slate-500 mt-1">Input pembelian langsung di loket koperasi. Status otomatis SELESAI dan stok fisik langsung berkurang.</p>
            </div>
            <a href="{{ route('admin.manual-orders.index') }}" class="polmart-btn polmart-btn-secondary">
                <x-icon name="clipboard-list" class="w-4 h-4" />
                <span>Riwayat Walk-in</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.manual-orders.store') }}">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Kiri: Data Pembeli & Katalog Produk -->
                <div class="lg:col-span-7 space-y-6">
                    <section class="polmart-card space-y-4">
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900">1. Informasi Pembeli</h2>
                            <p class="text-xs text-slate-500">Cari dan pilih anggota terdaftar, atau kosongkan untuk pelanggan walk-in.</p>
                        </div>

                        <div class="relative" @click.away="memberDropdownOpen = false">
                            <label for="member_search" class="polmart-label">Anggota Terdaftar</label>
                            <input type="hidden" name="user_id" :value="selectedUserId">
                            <div class="relative">
                                <input id="member_search" type="text" x-model="memberSearch"
                                       @focus="memberDropdownOpen = true"
                                       @input="memberDropdownOpen = true; if (!memberSearch.trim()) { selectedUserId = ''; }"
                                       placeholder="Cari nama atau nomor anggota (kosongkan untuk Walk-in)..."
                                       autocomplete="off"
                                       class="polmart-input pr-9">
                                <button type="button" x-show="selectedUserId || memberSearch"
                                        @click="clearMember()"
                                        title="Reset ke Walk-in / Non-Akun"
                                        class="absolute inset-y-0 right-0 px-3 text-slate-400 hover:text-rose-600 flex items-center">
                                    <x-icon name="x" class="w-4 h-4" />
                                </button>
                            </div>

                            <div x-show="memberDropdownOpen" x-transition
                                 class="absolute z-30 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-y-auto divide-y divide-slate-100">
                                <button type="button" @click="clearMember(); memberDropdownOpen = false"
                                        class="w-full text-left px-3.5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 flex items-center justify-between">
                                    <span>-- Walk-in / Non-Akun --</span>
                                    <span x-show="!selectedUserId" class="text-blue-600"><x-icon name="check" class="w-4 h-4" /></span>
                                </button>
                                <template x-for="member in filteredMembers()" :key="member.id">
                                    <button type="button" @click="selectMember(member)"
                                            class="w-full text-left px-3.5 py-2.5 text-sm hover:bg-blue-50 flex items-center justify-between gap-2 transition">
                                        <div>
                                            <span class="font-semibold text-slate-900" x-text="member.name"></span>
                                            <span class="font-mono text-xs text-slate-500 ml-1.5" x-text="'(' + member.member_code + ')'"></span>
                                        </div>
                                        <span x-show="String(selectedUserId) === String(member.id)" class="text-blue-600"><x-icon name="check" class="w-4 h-4" /></span>
                                    </button>
                                </template>
                                <div x-show="filteredMembers().length === 0" class="px-3.5 py-3 text-xs text-slate-400 text-center">
                                    Anggota tidak ditemukan.
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="polmart-card space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900">2. Pilih Barang Aktif</h2>
                                <p class="text-xs text-slate-500">Klik barang untuk menambahkan ke daftar transaksi.</p>
                            </div>
                            <input type="text" x-model="searchQuery" placeholder="Cari SKU atau nama barang..."
                                   class="polmart-input sm:w-64">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[420px] overflow-y-auto pr-1">
                            <template x-for="product in filteredProducts()" :key="product.id">
                                <div class="p-3.5 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50/40 transition flex flex-col justify-between gap-2">
                                    <div>
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-mono text-xs font-bold text-blue-600" x-text="product.sku"></span>
                                            <span class="text-xs text-slate-500" x-text="'Stok: ' + product.stock"></span>
                                        </div>
                                        <h3 class="font-bold text-sm text-slate-900 mt-1" x-text="product.name"></h3>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                        <span class="font-extrabold text-sm text-slate-900" x-text="formatRupiah(product.price)"></span>
                                        <button type="button" @click="addProduct(product)"
                                                class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold">
                                            + Tambah
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <div x-show="filteredProducts().length === 0" class="col-span-2 text-center py-6 text-sm text-slate-400">
                                Tidak ada produk tersedia yang cocok dengan pencarian.
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Kanan: Keranjang Kasir & Ringkasan -->
                <div class="lg:col-span-5 space-y-6">
                    <section class="polmart-card space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900">Daftar Barang Dibeli</h2>
                                <p class="text-xs text-slate-500">Item akan langsung dicatat sebagai transaksi SELESAI.</p>
                            </div>
                            <button type="button" @click="rows = []" x-show="rows.length > 0"
                                    class="text-xs font-semibold text-rose-600 hover:underline">
                                Kosongkan
                            </button>
                        </div>

                        <div x-show="rows.length === 0" class="text-center py-8 border border-dashed border-slate-200 rounded-xl text-sm text-slate-400">
                            Belum ada barang dipilih. Klik tombol <strong>+ Tambah</strong> di katalog sebelah kiri.
                        </div>

                        <div class="space-y-3" x-show="rows.length > 0">
                            <template x-for="(row, index) in rows" :key="row.product_id">
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                                    <input type="hidden" :name="`items[${index}][product_id]`" :value="row.product_id">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <div class="font-bold text-sm text-slate-900" x-text="row.name"></div>
                                            <div class="text-xs text-slate-500" x-text="row.sku + ' • ' + formatRupiah(row.price) + ' (Max: ' + row.stock + ')'"></div>
                                        </div>
                                        <button type="button" @click="removeRow(index)" class="text-rose-600 p-1 rounded hover:bg-rose-50 inline-flex items-center">
                                            <x-icon name="x" class="w-4 h-4" />
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs font-semibold text-slate-500">Qty:</label>
                                            <div class="polmart-qty-stepper sm">
                                                <button type="button"
                                                        @click="row.qty = Math.max(1, (Number(row.qty) || 1) - 1)">
                                                    −
                                                </button>
                                                <input type="number" :name="`items[${index}][qty]`" x-model.number="row.qty"
                                                       min="1" :max="row.stock" required readonly>
                                                <button type="button"
                                                        @click="row.qty = Math.min(row.stock, (Number(row.qty) || 1) + 1)">
                                                    +
                                                </button>
                                            </div>
                                        </div>
                                        <div class="font-extrabold text-sm text-blue-700" x-text="formatRupiah(row.price * (row.qty || 0))"></div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="border-t border-slate-200 pt-4 space-y-2">
                            <div class="flex justify-between text-sm text-slate-600">
                                <span>Total Item</span>
                                <strong x-text="totalQty() + ' pcs'"></strong>
                            </div>
                            <div class="flex justify-between items-center text-lg font-extrabold text-slate-900">
                                <span>Total Transaksi</span>
                                <span class="text-blue-700" x-text="formatRupiah(grandTotal())"></span>
                            </div>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <a href="{{ route('admin.manual-orders.index') }}" class="polmart-btn polmart-btn-secondary flex-1">Batal</a>
                            <button type="submit" :disabled="rows.length === 0"
                                    class="polmart-btn polmart-btn-primary flex-1 disabled:opacity-50 disabled:cursor-not-allowed">
                                Simpan & Selesaikan
                            </button>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </div>

    <script>
        function walkinCashier(products, members) {
            return {
                products,
                members,
                selectedUserId: @json(old('user_id', '')),
                memberSearch: '',
                memberDropdownOpen: false,
                searchQuery: '',
                rows: [],
                init() {
                    if (this.selectedUserId) {
                        const m = this.members.find(item => String(item.id) === String(this.selectedUserId));
                        if (m) {
                            this.memberSearch = m.member_code + ' - ' + m.name;
                        }
                    }
                },
                filteredMembers() {
                    const q = this.memberSearch.toLowerCase().trim();
                    if (!q) return this.members;
                    return this.members.filter(m =>
                        m.name.toLowerCase().includes(q) || (m.member_code && m.member_code.toLowerCase().includes(q))
                    );
                },
                selectMember(member) {
                    this.selectedUserId = member.id;
                    this.memberSearch = member.member_code + ' - ' + member.name;
                    this.memberDropdownOpen = false;
                },
                clearMember() {
                    this.selectedUserId = '';
                    this.memberSearch = '';
                },
                filteredProducts() {
                    const q = this.searchQuery.toLowerCase().trim();
                    if (!q) return this.products;
                    return this.products.filter(p =>
                        p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
                    );
                },
                addProduct(product) {
                    const existing = this.rows.find(r => r.product_id === product.id);
                    if (existing) {
                        if (existing.qty >= product.stock) {
                            alert('Kuantitas mencapai batas stok tersedia (' + product.stock + ').');
                            return;
                        }
                        existing.qty++;
                    } else {
                        this.rows.push({
                            product_id: product.id,
                            sku: product.sku,
                            name: product.name,
                            price: Number(product.price),
                            stock: Number(product.stock),
                            qty: 1,
                        });
                    }
                },
                removeRow(index) {
                    this.rows.splice(index, 1);
                },
                totalQty() {
                    return this.rows.reduce((sum, r) => sum + (Number(r.qty) || 0), 0);
                },
                grandTotal() {
                    return this.rows.reduce((sum, r) => sum + (Number(r.price) * (Number(r.qty) || 0)), 0);
                },
                formatRupiah(value) {
                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                }
            };
        }
    </script>
</x-app-layout>
