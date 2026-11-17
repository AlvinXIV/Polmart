
<x-app-layout>

    <div class="min-h-screen bg-gray-50">

        <!-- Header Katalog -->
        <section class="bg-green-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">

                    <div>
                        <p class="text-green-200 text-sm mb-2">
                            Selamat datang di POLMART
                        </p>

                        <h1 class="text-3xl md:text-4xl font-bold text-white">
                            Belanja Kebutuhanmu
                        </h1>

                        <p class="text-green-100 mt-3 max-w-xl">
                            Temukan berbagai kebutuhan harian dengan mudah
                            melalui katalog produk POLMART.
                        </p>
                    </div>

                    <div class="hidden md:flex w-20 h-20 rounded-2xl bg-white/10 items-center justify-center">
                        <span class="text-5xl">🛒</span>
                    </div>

                </div>
            </div>
        </section>


        <!-- Konten Katalog -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

            <!-- Pencarian -->
            <div class="mb-8">
                <label for="searchProduct"
                       class="block text-sm font-semibold text-gray-700 mb-2">
                    Cari Produk
                </label>

                <div class="relative">
                    <input
                        type="text"
                        id="searchProduct"
                        placeholder="Cari nama produk..."
                        class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-700 focus:border-green-700"
                    >

                    <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7" stroke-width="2"/>
                        <path d="m16 16 4 4" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>


            <!-- Kategori -->
            <div class="mb-8">
                <div class="flex flex-wrap gap-3" id="categoryFilters">

                    <button type="button"
                            data-category="Semua"
                            class="category-btn px-5 py-2 rounded-full bg-green-800 text-white text-sm font-medium">
                        Semua Produk
                    </button>

                    <button type="button"
                            data-category="Makanan"
                            class="category-btn px-5 py-2 rounded-full bg-white border border-gray-200 text-gray-600 text-sm hover:border-green-700">
                        Makanan
                    </button>

                    <button type="button"
                            data-category="Minuman"
                            class="category-btn px-5 py-2 rounded-full bg-white border border-gray-200 text-gray-600 text-sm hover:border-green-700">
                        Minuman
                    </button>

                    <button type="button"
                            data-category="Alat Tulis"
                            class="category-btn px-5 py-2 rounded-full bg-white border border-gray-200 text-gray-600 text-sm hover:border-green-700">
                        Alat Tulis
                    </button>

                    <button type="button"
                            data-category="Lainnya"
                            class="category-btn px-5 py-2 rounded-full bg-white border border-gray-200 text-gray-600 text-sm hover:border-green-700">
                        Lainnya
                    </button>

                </div>
            </div>


            <!-- Judul Produk -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">
                        Katalog Produk
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Pilih produk yang ingin kamu beli.
                    </p>
                </div>

                <span class="text-sm text-gray-500" id="productCount">
                    4 produk
                </span>
            </div>


            <!-- Daftar Produk -->
            <!-- Data berikut masih contoh tampilan -->
            <div id="productList"
                 class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Produk 1 -->
                <div class="product-card bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     data-name="Air Mineral"
                     data-category="Minuman">

                    <div class="h-44 bg-blue-50 flex items-center justify-center">
                        <span class="text-7xl">💧</span>
                    </div>

                    <div class="p-5">
                        <span class="text-xs font-medium text-blue-700 bg-blue-50 px-3 py-1 rounded-full">
                            Minuman
                        </span>

                        <h3 class="font-semibold text-gray-900 mt-3">
                            Air Mineral
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Contoh produk katalog
                        </p>

                        <div class="flex items-center justify-between mt-4">
                            <span class="font-bold text-green-800">
                                Rp3.000
                            </span>

                            <span class="text-xs text-gray-400">
                                Stok: -
                            </span>
                        </div>

                        <button type="button" disabled
                                class="w-full mt-4 py-2.5 rounded-lg bg-gray-200 text-gray-500 text-sm font-medium cursor-not-allowed">
                            Tambah ke Keranjang
                        </button>
                    </div>
                </div>


                <!-- Produk 2 -->
                <div class="product-card bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     data-name="Roti"
                     data-category="Makanan">

                    <div class="h-44 bg-orange-50 flex items-center justify-center">
                        <span class="text-7xl">🍞</span>
                    </div>

                    <div class="p-5">
                        <span class="text-xs font-medium text-orange-700 bg-orange-50 px-3 py-1 rounded-full">
                            Makanan
                        </span>

                        <h3 class="font-semibold text-gray-900 mt-3">
                            Roti
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Contoh produk katalog
                        </p>

                        <div class="flex items-center justify-between mt-4">
                            <span class="font-bold text-green-800">
                                Rp5.000
                            </span>

                            <span class="text-xs text-gray-400">
                                Stok: -
                            </span>
                        </div>

                        <button type="button" disabled
                                class="w-full mt-4 py-2.5 rounded-lg bg-gray-200 text-gray-500 text-sm font-medium cursor-not-allowed">
                            Tambah ke Keranjang
                        </button>
                    </div>
                </div>


                <!-- Produk 3 -->
                <div class="product-card bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     data-name="Buku Tulis"
                     data-category="Alat Tulis">

                    <div class="h-44 bg-yellow-50 flex items-center justify-center">
                        <span class="text-7xl">📒</span>
                    </div>

                    <div class="p-5">
                        <span class="text-xs font-medium text-yellow-700 bg-yellow-50 px-3 py-1 rounded-full">
                            Alat Tulis
                        </span>

                        <h3 class="font-semibold text-gray-900 mt-3">
                            Buku Tulis
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Contoh produk katalog
                        </p>

                        <div class="flex items-center justify-between mt-4">
                            <span class="font-bold text-green-800">
                                Rp4.000
                            </span>

                            <span class="text-xs text-gray-400">
                                Stok: -
                            </span>
                        </div>

                        <button type="button" disabled
                                class="w-full mt-4 py-2.5 rounded-lg bg-gray-200 text-gray-500 text-sm font-medium cursor-not-allowed">
                            Tambah ke Keranjang
                        </button>
                    </div>
                </div>


                <!-- Produk 4 -->
                <div class="product-card bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     data-name="Perlengkapan"
                     data-category="Lainnya">

                    <div class="h-44 bg-purple-50 flex items-center justify-center">
                        <span class="text-7xl">📦</span>
                    </div>

                    <div class="p-5">
                        <span class="text-xs font-medium text-purple-700 bg-purple-50 px-3 py-1 rounded-full">
                            Lainnya
                        </span>

                        <h3 class="font-semibold text-gray-900 mt-3">
                            Perlengkapan
                        </h3>

                        <p class="text-sm text-gray-500 mt-1">
                            Contoh produk katalog
                        </p>

                        <div class="flex items-center justify-between mt-4">
                            <span class="font-bold text-green-800">
                                Rp10.000
                            </span>

                            <span class="text-xs text-gray-400">
                                Stok: -
                            </span>
                        </div>

                        <button type="button" disabled
                                class="w-full mt-4 py-2.5 rounded-lg bg-gray-200 text-gray-500 text-sm font-medium cursor-not-allowed">
                            Tambah ke Keranjang
                        </button>
                    </div>
                </div>

            </div>


            <!-- Pesan Jika Produk Tidak Ditemukan -->
            <div id="emptyMessage"
                 class="hidden text-center py-16">

                <div class="text-5xl mb-4">🔍</div>

                <h3 class="font-semibold text-gray-800">
                    Produk tidak ditemukan
                </h3>

                <p class="text-sm text-gray-500 mt-2">
                    Coba gunakan kata kunci atau kategori lainnya.
                </p>
            </div>


            <!-- Keterangan -->
            <div class="mt-10 p-4 bg-yellow-50 border border-yellow-100 rounded-xl">
                <p class="text-sm text-yellow-800">
                    <strong>Catatan:</strong> Produk dan harga pada halaman ini
                    masih berupa data contoh. Fitur pencarian dan filter sudah
                    dapat digunakan, sedangkan data produk dan keranjang belum
                    terhubung dengan database.
                </p>
            </div>

        </section>
    </div>


    <!-- JavaScript Pencarian dan Filter -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const searchInput = document.getElementById('searchProduct');
            const categoryButtons = document.querySelectorAll('.category-btn');
            const productCards = document.querySelectorAll('.product-card');
            const productCount = document.getElementById('productCount');
            const emptyMessage = document.getElementById('emptyMessage');

            let selectedCategory = 'Semua';

            function filterProducts() {

                const keyword = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;

                productCards.forEach(function (card) {

                    const name = card.dataset.name.toLowerCase();
                    const category = card.dataset.category;

                    const matchesSearch = name.includes(keyword);

                    const matchesCategory =
                        selectedCategory === 'Semua' ||
                        category === selectedCategory;

                    if (matchesSearch && matchesCategory) {
                        card.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('hidden');
                    }

                });

                productCount.textContent = visibleCount + ' produk';

                if (visibleCount === 0) {
                    emptyMessage.classList.remove('hidden');
                } else {
                    emptyMessage.classList.add('hidden');
                }
            }

            searchInput.addEventListener('input', filterProducts);

            categoryButtons.forEach(function (button) {

                button.addEventListener('click', function () {

                    selectedCategory = this.dataset.category;

                    categoryButtons.forEach(function (item) {
                        item.classList.remove(
                            'bg-green-800',
                            'text-white'
                        );

                        item.classList.add(
                            'bg-white',
                            'text-gray-600',
                            'border',
                            'border-gray-200'
                        );
                    });

                    this.classList.remove(
                        'bg-white',
                        'text-gray-600',
                        'border',
                        'border-gray-200'
                    );

                    this.classList.add(
                        'bg-green-800',
                        'text-white'
                    );

                    filterProducts();
                });
            });

        });
    </script>

</x-app-layout>
