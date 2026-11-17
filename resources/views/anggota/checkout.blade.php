
<x-app-layout>

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="mb-8">
                <p class="text-sm font-medium text-green-700">
                    MEMBER PORTAL
                </p>

                <h1 class="text-3xl font-bold text-gray-900 mt-2">
                    Checkout
                </h1>

                <p class="text-gray-500 mt-2">
                    Periksa kembali pesanan sebelum melanjutkan pembayaran.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Order Details -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Products -->
                    <section class="bg-white border border-gray-200 rounded-xl p-6">

                        <h2 class="text-lg font-semibold text-gray-900">
                            Detail Pesanan
                        </h2>

                        <div class="divide-y divide-gray-100 mt-5">

                            <!-- Product 1 -->
                            <div class="py-4 flex items-center gap-4">
                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <span class="text-3xl">🥤</span>
                                </div>

                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900">
                                        Contoh Produk Minuman
                                    </h3>

                                    <p class="text-sm text-gray-500 mt-1">
                                        1 × Rp5.000
                                    </p>
                                </div>

                                <p class="font-semibold text-gray-900">
                                    Rp5.000
                                </p>
                            </div>

                            <!-- Product 2 -->
                            <div class="py-4 flex items-center gap-4">
                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <span class="text-3xl">🍪</span>
                                </div>

                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900">
                                        Contoh Produk Makanan
                                    </h3>

                                    <p class="text-sm text-gray-500 mt-1">
                                        2 × Rp3.000
                                    </p>
                                </div>

                                <p class="font-semibold text-gray-900">
                                    Rp6.000
                                </p>
                            </div>

                        </div>
                    </section>

                    <!-- Customer Information -->
                    <section class="bg-white border border-gray-200 rounded-xl p-6">

                        <h2 class="text-lg font-semibold text-gray-900">
                            Informasi Anggota
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            Informasi akun yang digunakan untuk pemesanan.
                        </p>

                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-5">

                            <div>
                                <p class="text-sm text-gray-500">
                                    Nama Anggota
                                </p>

                                <p class="font-medium text-gray-900 mt-1">
                                    {{ Auth::user()->name }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">
                                    Email
                                </p>

                                <p class="font-medium text-gray-900 mt-1">
                                    {{ Auth::user()->email }}
                                </p>
                            </div>

                        </div>
                    </section>

                    <!-- Payment Method -->
                    <section class="bg-white border border-gray-200 rounded-xl p-6">

                        <h2 class="text-lg font-semibold text-gray-900">
                            Metode Pembayaran
                        </h2>

                        <div class="mt-5 border border-green-700 bg-green-50 rounded-xl p-4 flex items-center gap-4">

                            <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center border border-gray-200">
                                <span class="font-bold text-green-800 text-lg">
                                    QR
                                </span>
                            </div>

                            <div class="flex-1">
                                <p class="font-semibold text-gray-900">
                                    QRIS
                                </p>

                                <p class="text-sm text-gray-500 mt-1">
                                    Pembayaran melalui simulasi QRIS POLMART.
                                </p>
                            </div>

                            <span class="text-green-700 font-semibold">
                                ✓
                            </span>

                        </div>
                    </section>

                </div>

                <!-- Order Summary -->
                <aside>
                    <div class="bg-white border border-gray-200 rounded-xl p-6">

                        <h2 class="text-lg font-semibold text-gray-900">
                            Ringkasan Pembayaran
                        </h2>

                        <div class="space-y-4 mt-6">

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">
                                    Total Produk
                                </span>

                                <span class="font-medium text-gray-900">
                                    3 item
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">
                                    Subtotal
                                </span>

                                <span class="font-medium text-gray-900">
                                    Rp11.000
                                </span>
                            </div>

                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-gray-900">
                                        Total Pembayaran
                                    </span>

                                    <span class="text-xl font-bold text-green-800">
                                        Rp11.000
                                    </span>
                                </div>
                            </div>

                        </div>

                        <!-- Payment Deadline Information -->
                        <div class="mt-6 bg-yellow-50 border border-yellow-100 rounded-lg p-4">

                            <p class="text-sm font-semibold text-yellow-800">
                                Perhatian
                            </p>

                            <p class="text-xs text-yellow-700 mt-2 leading-relaxed">
                                Setelah checkout berhasil diproses, pesanan
                                memiliki batas waktu pembayaran selama
                                15 menit.
                            </p>

                        </div>

                        <!-- Create Order -->
                        <button type="button"
                                disabled
                                class="w-full mt-6 py-3 rounded-lg bg-gray-200 text-gray-500 font-semibold text-sm cursor-not-allowed">
                            Buat Pesanan
                        </button>

                        <p class="text-xs text-gray-400 text-center mt-4">
                            Tombol akan dihubungkan dengan proses checkout backend.
                        </p>

                        <!-- Back to Cart -->
                        <a href="{{ route('anggota.keranjang') }}"
                           class="block text-center text-sm text-green-800 font-medium mt-5 hover:underline">
                            Kembali ke Keranjang
                        </a>

                    </div>
                </aside>

            </div>
        </div>
    </div>

</x-app-layout>
