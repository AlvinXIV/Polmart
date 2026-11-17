
<x-app-layout>

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Header -->
            <div class="mb-8">
                <p class="text-sm font-medium text-green-700">
                    MEMBER PORTAL
                </p>

                <h1 class="text-3xl font-bold text-gray-900 mt-2">
                    Pembayaran QRIS
                </h1>

                <p class="text-gray-500 mt-2">
                    Selesaikan pembayaran untuk melanjutkan pesananmu.
                </p>
            </div>

            <!-- Payment Status -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                        <span class="text-yellow-700 text-xl">!</span>
                    </div>

                    <div>
                        <p class="font-semibold text-yellow-900">
                            Contoh Status Pembayaran
                        </p>

                        <p class="text-sm text-yellow-800 mt-1">
                            Status menunggu pembayaran akan ditampilkan
                            setelah pesanan berhasil dibuat.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                <!-- QRIS Section -->
                <section class="lg:col-span-3 bg-white border border-gray-200 rounded-xl p-6 sm:p-8">

                    <div class="text-center">
                        <h2 class="text-xl font-semibold text-gray-900">
                            Scan QR Code untuk Membayar
                        </h2>

                        <p class="text-sm text-gray-500 mt-2">
                            Buka aplikasi pembayaran dan pindai kode QRIS.
                        </p>
                    </div>

                    <!-- QR Placeholder -->
                    <div class="flex justify-center mt-7">
                        <div class="w-64 h-64 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 flex flex-col items-center justify-center p-5">

                            <div class="text-5xl mb-3">▦</div>

                            <p class="text-sm font-semibold text-gray-700">
                                QRIS POLMART
                            </p>

                            <p class="text-xs text-gray-400 text-center mt-2">
                                QR pembayaran akan ditampilkan setelah
                                pesanan berhasil dibuat.
                            </p>
                        </div>
                    </div>

                    <div class="text-center mt-5">
                        <p class="text-xs text-gray-500">
                            Metode pembayaran
                        </p>

                        <p class="font-bold text-green-800 text-lg mt-1">
                            POLMART
                        </p>
                    </div>

                    <!-- Countdown -->
                    <div class="mt-8 bg-red-50 border border-red-100 rounded-xl p-5 text-center">

                        <p class="text-sm font-medium text-red-800">
                            Batas Waktu Pembayaran
                        </p>

                        <div class="text-3xl font-bold text-red-700 mt-2">
                            15:00
                        </div>

                        <p class="text-xs text-red-600 mt-2">
                            Contoh tampilan waktu pembayaran.
                            Countdown akan diaktifkan setelah terhubung dengan backend.
                        </p>
                    </div>

                    <!-- Simulation Button -->
                    <button type="button"
                            disabled
                            class="w-full mt-6 py-3 rounded-lg bg-gray-200 text-gray-500 font-semibold text-sm cursor-not-allowed">
                        Simulasikan Bayar QRIS
                    </button>

                    <p class="text-xs text-gray-400 text-center mt-3">
                        Simulasi pembayaran belum terhubung dengan backend.
                    </p>

                </section>

                <!-- Payment Details -->
                <aside class="lg:col-span-2">

                    <div class="bg-white border border-gray-200 rounded-xl p-6">

                        <h2 class="text-lg font-semibold text-gray-900">
                            Rincian Pembayaran
                        </h2>

                        <div class="space-y-5 mt-6">

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
                                    Nama Anggota
                                </p>

                                <p class="font-semibold text-gray-900 mt-1">
                                    {{ Auth::user()->name }}
                                </p>
                            </div>

                            <div class="border-t border-gray-100 pt-5">
                                <p class="text-sm text-gray-500">
                                    Total Tagihan
                                </p>

                                <p class="text-2xl font-bold text-green-800 mt-2">
                                    Rp11.000
                                </p>
                            </div>

                        </div>

                        <!-- Payment Instructions -->
                        <div class="mt-6 bg-green-50 border border-green-100 rounded-lg p-4">

                            <p class="text-sm font-semibold text-green-900">
                                Petunjuk Pembayaran
                            </p>

                            <ol class="list-decimal list-inside text-sm text-green-800 mt-3 space-y-2">
                                <li>Buka aplikasi pembayaran QRIS.</li>
                                <li>Pindai QR Code yang tersedia.</li>
                                <li>Periksa nominal pembayaran.</li>
                                <li>Selesaikan pembayaran.</li>
                            </ol>

                        </div>

                        <!-- Back to Checkout -->
                        <a href="{{ route('anggota.checkout') }}"
                           class="block text-center text-sm text-gray-500 hover:text-green-800 mt-6">
                            Kembali ke Checkout
                        </a>

                    </div>

                </aside>

            </div>
        </div>
    </div>

</x-app-layout>
