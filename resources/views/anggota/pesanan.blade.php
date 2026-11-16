
<x-app-layout>

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="mb-8">
                <p class="text-sm font-medium text-green-700">
                    MEMBER PORTAL
                </p>

                <h1 class="text-3xl font-bold text-gray-900 mt-2">
                    Pesanan Saya
                </h1>

                <p class="text-gray-500 mt-2">
                    Pantau status pesanan dan informasi pengambilan barang.
                </p>
            </div>

            <!-- Example Status -->
            <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
                <div class="flex items-start gap-4">

                    <div class="w-12 h-12 rounded-full bg-green-100
                                flex items-center justify-center shrink-0">
                        <span class="text-2xl text-green-700">✓</span>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-green-900">
                            Contoh Status Pesanan
                        </h2>

                        <p class="text-sm text-green-800 mt-1">
                            Bagian ini akan menampilkan informasi pembayaran
                            dan status pengambilan setelah terhubung dengan sistem.
                        </p>
                    </div>

                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Receipt -->
                <section class="lg:col-span-2 bg-white border
                                border-gray-200 rounded-xl p-6">

                    <div class="flex items-center justify-between
                                border-b border-gray-100 pb-5">

                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">
                                Detail Pesanan
                            </h2>

                            <p class="text-sm text-gray-500 mt-1">
                                Bukti transaksi POLMART
                            </p>
                        </div>

                        <span class="text-xs font-semibold bg-gray-100
                                     text-gray-600 px-3 py-1 rounded-full">
                            CONTOH STATUS
                        </span>

                    </div>

                    <!-- Order Information -->
                    <div class="grid grid-cols-2 gap-5 py-5
                                border-b border-gray-100">

                        <div>
                            <p class="text-sm text-gray-500">
                                Kode Pesanan
                            </p>

                            <p class="font-semibold text-gray-900 mt-1">
                                Akan dibuat oleh sistem
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Tanggal Transaksi
                            </p>

                            <p class="font-semibold text-gray-900 mt-1">
                                Akan diisi oleh sistem
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Nama Anggota
                            </p>

                            <p class="font-semibold text-gray-900 mt-1">
                                {{ Auth::user()->name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">
                                Metode Pembayaran
                            </p>

                            <p class="font-semibold text-gray-900 mt-1">
                                QRIS
                            </p>
                        </div>

                    </div>

                    <!-- Product List -->
                    <div class="py-5">

                        <h3 class="font-semibold text-gray-900 mb-4">
                            Produk yang Dipesan
                        </h3>

                        <div class="space-y-4">

                            <div class="flex justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        Contoh Produk Minuman
                                    </p>

                                    <p class="text-xs text-gray-500 mt-1">
                                        1 × Rp5.000
                                    </p>
                                </div>

                                <p class="text-sm font-semibold text-gray-900">
                                    Rp5.000
                                </p>
                            </div>

                            <div class="flex justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        Contoh Produk Makanan
                                    </p>

                                    <p class="text-xs text-gray-500 mt-1">
                                        2 × Rp3.000
                                    </p>
                                </div>

                                <p class="text-sm font-semibold text-gray-900">
                                    Rp6.000
                                </p>
                            </div>

                        </div>

                    </div>

                    <!-- Total -->
                    <div class="border-t border-gray-100 pt-5">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-900">
                                Total Pembayaran
                            </span>

                            <span class="text-xl font-bold text-green-800">
                                Rp11.000
                            </span>
                        </div>
                    </div>

                </section>

                <!-- Pickup Code -->
                <aside class="space-y-6">

                    <div class="bg-white border border-gray-200
                                rounded-xl p-6 text-center">

                        <p class="text-sm font-semibold text-gray-700">
                            Kode Pengambilan
                        </p>

                        <p class="text-xs text-gray-500 mt-2">
                            Tunjukkan kode berikut kepada petugas koperasi.
                        </p>

                        <div class="mt-6 bg-green-50 border border-green-100
                                    rounded-xl py-7">

                            <p class="text-4xl font-bold tracking-[0.3em]
                                      text-green-800">
                                ----
                            </p>

                            <p class="text-xs text-green-700 mt-3">
                                Kode akan muncul setelah pembayaran berhasil dikonfirmasi.
                            </p>

                        </div>

                        <div class="mt-5 flex items-start gap-2 text-left">
                            <span class="text-green-700">ⓘ</span>

                            <p class="text-xs text-gray-500 leading-relaxed">
                                Simpan kode ini dan jangan bagikan kepada
                                orang lain. Gunakan saat mengambil pesanan.
                            </p>
                        </div>

                    </div>

                    <div class="bg-white border border-gray-200
                                rounded-xl p-5">

                        <h3 class="font-semibold text-gray-900">
                            Informasi Pengambilan
                        </h3>

                        <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                            Silakan datang ke loket koperasi dan tunjukkan
                            kode pengambilan kepada petugas setelah pesanan
                            berstatus siap diambil.
                        </p>

                    </div>

                </aside>

            </div>

            <!-- Back to Catalog -->
            <div class="mt-8">
                <a href="{{ route('anggota.katalog') }}"
                   class="inline-flex items-center justify-center
                          px-5 py-3 rounded-lg bg-green-800
                          hover:bg-green-900 text-white
                          text-sm font-semibold transition">
                    Kembali ke Katalog
                </a>
            </div>

            <p class="text-xs text-gray-400 text-center mt-8">
                POLMART · Sistem Informasi Koperasi
            </p>

        </div>
    </div>

</x-app-layout>
