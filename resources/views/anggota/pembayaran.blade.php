<x-app-layout>
    @section('title', 'Pembayaran QRIS - Polmart')
    @section('page_title', 'Pembayaran QRIS')

    @php
        $remainingSeconds = 0;
        if ($order->status === 'MENUNGGU_BAYAR' && $order->expires_at) {
            $remainingSeconds = max(0, (int) now()->diffInSeconds($order->expires_at, false));
        }
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600">PORTAL ANGGOTA</span>
                <h1 class="text-2xl font-extrabold text-slate-900 mt-1">Pembayaran QRIS Dinamis</h1>
                <p class="text-sm text-slate-500 mt-1">Pesanan <strong class="font-mono text-slate-900">{{ $order->order_code }}</strong></p>
            </div>
            <a href="{{ route('anggota.pesanan', ['order_code' => $order->order_code]) }}" class="polmart-btn polmart-btn-secondary">
                <x-icon name="clipboard-list" class="w-4 h-4" />
                <span>Daftar Pesanan Saya</span>
            </a>
        </div>

        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- SECTION: MENUNGGU BAYAR -->
        <div id="waitingSection" class="{{ $order->status === 'MENUNGGU_BAYAR' ? '' : 'hidden' }} space-y-6">
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700">
                        <x-icon name="clock" class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="font-bold text-amber-900 text-sm">Menunggu Pembayaran QRIS</p>
                        <p class="text-xs text-amber-800 mt-0.5">Halaman akan otomatis beralih ke Struk Lunas & Kode Pengambilan 4-digit begitu pembayaran dikonfirmasi.</p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-[11px] font-semibold text-amber-700 block">Sisa Waktu</span>
                    <span id="countdownDisplay" class="text-2xl font-extrabold font-mono text-rose-600">--:--</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">
                <section class="lg:col-span-3 polmart-card text-center space-y-5">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Pindai QR Code untuk Membayar</h2>
                        <p class="text-xs text-slate-500 mt-1">QR mengarah ke URL simulasi: <a href="{{ $qrisUrl }}" target="_blank" class="text-blue-600 underline break-all">{{ $qrisUrl }}</a></p>
                    </div>

                    <div class="inline-flex p-5 bg-white border-2 border-slate-200 rounded-2xl shadow-sm">
                        {!! $qrCodeSvg !!}
                    </div>

                    <div class="space-y-3">
                        <button type="button" id="simulatePayBtn" onclick="simulatePayment()"
                                class="polmart-btn polmart-btn-primary w-full py-3 text-sm">
                            <x-icon name="check-circle" class="w-4 h-4" />
                            <span id="simulatePayBtnLabel">Simulasikan Bayar QRIS</span>
                        </button>
                        <a href="{{ $qrisUrl }}" target="_blank"
                           class="polmart-btn polmart-btn-secondary w-full py-2.5 text-xs">
                            <span>Buka Halaman Simulasi QRIS (/qris/pay/{{ $order->order_code }})</span>
                            <x-icon name="external-link" class="w-3.5 h-3.5" />
                        </a>
                        <form method="POST" action="{{ route('anggota.pembayaran.cancel', ['order_code' => $order->order_code]) }}"
                              onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan {{ $order->order_code }}?')">
                            @csrf
                            <button type="submit"
                                    class="polmart-btn w-full py-2.5 text-xs bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100">
                                <x-icon name="x-circle" class="w-4 h-4" />
                                <span>Batalkan Pesanan</span>
                            </button>
                        </form>
                        <p id="paymentFeedback" class="text-xs text-slate-500"></p>
                    </div>
                </section>

                <aside class="lg:col-span-2 polmart-card space-y-4">
                    <h2 class="text-base font-extrabold text-slate-900">Rincian Tagihan</h2>
                    <div class="space-y-2 text-sm border-b border-slate-100 pb-4">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kode Pesanan</span>
                            <strong class="font-mono">{{ $order->order_code }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Pemesan</span>
                            <strong>{{ $order->customer_name }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Tagihan</span>
                            <strong class="text-lg text-blue-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-xs font-bold uppercase text-slate-400">Item Pesanan</p>
                        @foreach ($order->items as $item)
                            <div class="flex justify-between text-xs text-slate-700">
                                <span>{{ $item->qty }}× {{ $item->product?->name }}</span>
                                <span class="font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </aside>
            </div>
        </div>

        <!-- SECTION: STRUK LUNAS & KODE PENGAMBILAN 4-DIGIT (SIAP_DIAMBIL / SELESAI) -->
        <div id="paidSection" class="{{ in_array($order->status, ['SIAP_DIAMBIL', 'SELESAI'], true) ? '' : 'hidden' }} space-y-6">
            <div class="p-6 bg-emerald-50 border border-emerald-200 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                        <x-icon name="check-circle" class="w-6 h-6" />
                    </div>
                    <div>
                        <span class="polmart-badge polmart-badge-green mb-1" id="paidStatusBadge">{{ str_replace('_', ' ', $order->status) }}</span>
                        <h2 class="text-xl font-extrabold text-emerald-950">Pembayaran QRIS Berhasil Dikonfirmasi!</h2>
                        <p class="text-sm text-emerald-800 mt-1">
                            Tunjukkan <strong>Kode Pengambilan 4-Digit</strong> di samping kepada petugas loket Polmart untuk mengambil pesanan Anda.
                        </p>
                        <p class="text-xs text-emerald-700 mt-2">
                            Waktu Bayar: <strong id="paidAtDisplay">{{ $order->paid_at?->format('d/m/Y H:i:s') ?? '-' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="bg-white border-2 border-emerald-300 rounded-2xl px-8 py-5 text-center shadow-sm shrink-0">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Kode Pengambilan</span>
                    <span id="pickupCodeDisplay" class="block text-4xl font-extrabold font-mono tracking-[0.25em] text-emerald-700 mt-1">
                        #{{ $order->pickup_code ?? '----' }}
                    </span>
                </div>
            </div>

            <div class="polmart-card space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Struk Lunas Polmart</h3>
                        <p class="text-xs text-slate-500">Kode Pesanan: <strong class="font-mono">{{ $order->order_code }}</strong></p>
                    </div>
                    <span class="text-sm font-extrabold text-blue-700">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="divide-y divide-slate-100 text-sm">
                    @foreach ($order->items as $item)
                        <div class="py-2.5 flex justify-between">
                            <span>{{ $item->qty }}× {{ $item->product?->name }}</span>
                            <strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                        </div>
                    @endforeach
                </div>
                <div class="pt-3 flex flex-wrap gap-3">
                    <a href="{{ route('anggota.pesanan', ['order_code' => $order->order_code]) }}" class="polmart-btn polmart-btn-primary">
                        Lihat di Pesanan Saya
                    </a>
                    <a href="{{ route('anggota.katalog') }}" class="polmart-btn polmart-btn-secondary">
                        Kembali ke Katalog
                    </a>
                </div>
            </div>
        </div>

        <!-- SECTION: DIBATALKAN / KEDALUWARSA -->
        <div id="cancelledSection" class="{{ $order->status === 'DIBATALKAN' ? '' : 'hidden' }} polmart-card border-l-4 border-l-rose-500 space-y-4">
            <h2 class="text-lg font-extrabold text-rose-700">Pesanan Dibatalkan</h2>
            <p class="text-sm text-slate-600">
                Pesanan <strong class="font-mono">{{ $order->order_code }}</strong> telah dibatalkan atau melewati batas waktu pembayaran QRIS 15 menit, dan stok barang telah dikembalikan ke katalog.
            </p>
            <div>
                <a href="{{ route('anggota.katalog') }}" class="polmart-btn polmart-btn-primary">
                    Belanja Ulang di Katalog
                </a>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const orderCode = @json($order->order_code);
            let currentStatus = @json($order->status);
            let remaining = {{ $remainingSeconds }};
            const statusUrl = @json(route('api.qris.status', ['order_code' => $order->order_code]));
            const callbackUrl = @json(route('api.qris.simulate-callback'));

            const countdownEl = document.getElementById('countdownDisplay');
            const waitingSection = document.getElementById('waitingSection');
            const paidSection = document.getElementById('paidSection');
            const cancelledSection = document.getElementById('cancelledSection');
            const pickupCodeEl = document.getElementById('pickupCodeDisplay');
            const paidAtEl = document.getElementById('paidAtDisplay');
            const paidStatusBadge = document.getElementById('paidStatusBadge');
            const feedbackEl = document.getElementById('paymentFeedback');
            const payBtn = document.getElementById('simulatePayBtn');

            let countdownTimer = null;
            let pollingTimer = null;

            function formatCountdown(seconds) {
                const m = Math.floor(seconds / 60);
                const s = seconds % 60;
                return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            }

            function showPaidState(data) {
                currentStatus = data.status;
                waitingSection.classList.add('hidden');
                cancelledSection.classList.add('hidden');
                paidSection.classList.remove('hidden');

                if (data.pickup_code) {
                    pickupCodeEl.textContent = '#' + data.pickup_code;
                }
                if (data.paid_at) {
                    paidAtEl.textContent = data.paid_at;
                }
                if (data.status) {
                    paidStatusBadge.textContent = data.status.replace(/_/g, ' ');
                }

                clearInterval(countdownTimer);
                clearInterval(pollingTimer);
            }

            function showCancelledState() {
                currentStatus = 'DIBATALKAN';
                waitingSection.classList.add('hidden');
                paidSection.classList.add('hidden');
                cancelledSection.classList.remove('hidden');

                clearInterval(countdownTimer);
                clearInterval(pollingTimer);
            }

            async function pollStatus() {
                if (currentStatus !== 'MENUNGGU_BAYAR') return;

                try {
                    const response = await fetch(statusUrl, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) return;
                    const data = await response.json();

                    if (data.status === 'SIAP_DIAMBIL' || data.status === 'SELESAI') {
                        showPaidState(data);
                    } else if (data.status === 'DIBATALKAN') {
                        showCancelledState();
                    } else if (typeof data.remaining_seconds === 'number') {
                        remaining = data.remaining_seconds;
                    }
                } catch (e) {
                    // Abaikan gangguan jaringan sementara, coba lagi pada interval berikutnya
                }
            }

            window.simulatePayment = async function () {
                if (payBtn) {
                    payBtn.disabled = true;
                    payBtn.textContent = 'Memproses pembayaran QRIS...';
                }
                if (feedbackEl) {
                    feedbackEl.textContent = '';
                }

                try {
                    const response = await fetch(callbackUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ order_code: orderCode })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        showPaidState(data);
                    } else {
                        if (feedbackEl) {
                            feedbackEl.textContent = data.message || 'Gagal mengonfirmasi pembayaran.';
                            feedbackEl.className = 'text-xs text-rose-600 font-semibold';
                        }
                        if (data.status === 'DIBATALKAN') {
                            showCancelledState();
                        } else if (payBtn) {
                            payBtn.disabled = false;
                            const lbl = document.getElementById('simulatePayBtnLabel');
                            if (lbl) lbl.textContent = 'Simulasikan Bayar QRIS';
                        }
                    }
                } catch (error) {
                    if (feedbackEl) {
                        feedbackEl.textContent = 'Terjadi kesalahan koneksi saat menghubungi endpoint QRIS.';
                        feedbackEl.className = 'text-xs text-rose-600 font-semibold';
                    }
                    if (payBtn) {
                        payBtn.disabled = false;
                        const lbl = document.getElementById('simulatePayBtnLabel');
                        if (lbl) lbl.textContent = 'Simulasikan Bayar QRIS';
                    }
                }
            };

            if (currentStatus === 'MENUNGGU_BAYAR') {
                if (countdownEl) {
                    countdownEl.textContent = formatCountdown(remaining);
                }

                countdownTimer = setInterval(() => {
                    if (remaining > 0) {
                        remaining--;
                        if (countdownEl) {
                            countdownEl.textContent = formatCountdown(remaining);
                        }
                    } else {
                        pollStatus();
                    }
                }, 1000);

                // Auto-polling setiap 3 detik sesuai FR-07
                pollingTimer = setInterval(pollStatus, 3000);
            }
        })();
    </script>
</x-app-layout>
