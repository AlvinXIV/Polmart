
<x-app-layout>

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="mb-8">
                <p class="text-sm font-medium text-green-700">
                    MEMBER PORTAL
                </p>

                <h1 class="text-3xl font-bold text-gray-900 mt-2">
                    Keranjang Belanja
                </h1>

                <p class="text-gray-500 mt-2">
                    Periksa kembali produk pilihanmu sebelum checkout.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-4">

                    <!-- Product 1 -->
                    <div class="bg-white border border-gray-200 rounded-xl p-5">
                        <div class="flex flex-col sm:flex-row gap-5">

                            <div class="w-full sm:w-28 h-28 bg-gray-100 rounded-lg flex items-center justify-center">
                                <span class="text-5xl">🥤</span>
                            </div>

                            <div class="flex-1">
                                <p class="text-xs text-green-700 font-medium">
                                    MINUMAN
                                </p>

                                <h3 class="font-semibold text-gray-900 mt-1">
                                    Contoh Produk Minuman
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    Harga satuan: Rp5.000
                                </p>

                                <div class="flex flex-wrap items-center justify-between gap-4 mt-4">

                                    <div class="flex items-center border border-gray-300 rounded-lg">
                                        <button type="button" disabled class="px-3 py-1.5 text-gray-400">
                                            −
                                        </button>

                                        <span class="px-4 py-1.5 text-sm">1</span>

                                        <button type="button" disabled class="px-3 py-1.5 text-gray-400">
                                            +
                                        </button>
                                    </div>

                                    <p class="font-semibold text-gray-900">
                                        Rp5.000
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product 2 -->
                    <div class="bg-white border border-gray-200 rounded-xl p-5">
                        <div class="flex flex-col sm:flex-row gap-5">

                            <div class="w-full sm:w-28 h-28 bg-gray-100 rounded-lg flex items-center justify-center">
                                <span class="text-5xl">🍪</span>
                            </div>

                            <div class="flex-1">
                                <p class="text-xs text-green-700 font-medium">
                                    MAKANAN
                                </p>

                                <h3 class="font-semibold text-gray-900 mt-1">
                                    Contoh Produk Makanan
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    Harga satuan: Rp3.000
                                </p>

                                <div class="flex flex-wrap items-center justify-between gap-4 mt-4">

                                    <div class="flex items-center border border-gray-300 rounded-lg">
                                        <button type="button" disabled class="px-3 py-1.5 text-gray-400">
                                            −
                                        </button>

                                        <span class="px-4 py-1.5 text-sm">2</span>

                                        <button type="button" disabled class="px-3 py-1.5 text-gray-400">
                                            +
                                        </button>
                                    </div>

                                    <p class="font-semibold text-gray-900">
                                        Rp6.000
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back to Catalog -->
                    <div class="pt-2">
                        <a href="{{ route('anggota.katalog') }}"
                           class="inline-flex items-center gap-2 text-sm font-medium text-green-800 hover:text-green-950">
                            <span>←</span>
                            Lanjut Belanja
                        </a>
                    </div>

                </div>

                <!-- Order Summary -->
                <aside>
                    <div class="bg-white border border-gray-200 rounded-xl p-6 sticky top-6">

                        <h2 class="text-lg font-semibold text-gray-900">
                            Ringkasan Belanja
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
                                        Total
                                    </span>

                                    <span class="text-xl font-bold text-green-800">
                                        Rp11.000
                                    </span>
                                </div>
                            </div>

                        </div>

                        <button type="button" disabled
                                class="w-full mt-6 py-3 rounded-lg bg-gray-200 text-gray-500 font-semibold text-sm cursor-not-allowed">
                            Lanjut ke Checkout
                        </button>

                        <p class="text-xs text-gray-400 text-center mt-4">
                            Ringkasan ini masih menggunakan data contoh.
                        </p>

                    </div>
                </aside>

            </div>

        </div>
    </div>

</x-app-layout>
