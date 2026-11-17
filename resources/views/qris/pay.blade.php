<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Simulasi Pembayaran QRIS - {{ $order->order_code }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    @php
        $remainingSeconds = 0;
        if ($order->status === 'MENUNGGU_BAYAR' && $order->expires_at) {
            $remainingSeconds = max(0, (int) now()->diffInSeconds($order->expires_at, false));
        }
    @endphp

    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-800 to-blue-600 p-6 text-white text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider mb-2">
                SIMULATOR QRIS POLMART
            </div>
            <h1 class="text-xl font-extrabold">{{ $order->order_code }}</h1>
            <p class="text-blue-100 text-xs mt-1">Pemesan: {{ $order->customer_name }}</p>
        </div>

        <div class="p-6 space-y-5">
            <!-- Total Tagihan -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 text-center">
                <span class="text-xs font-semibold text-slate-500 uppercase">Total Tagihan</span>
                <p class="text-3xl font-extrabold text-blue-700 mt-1">
                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                </p>
            </div>

            <!-- Rincian Item -->
            <div class="space-y-2 border-b border-slate-100 pb-4">
                <p class="text-xs font-bold uppercase text-slate-400">Rincian Pembelian</p>
                @foreach ($order->items as $item)
                    <div class="flex justify-between text-xs text-slate-700">
                        <span>{{ $item->qty }}× {{ $item->product?->name }}</span>
                        <span class="font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Status: MENUNGGU_BAYAR -->
            <div id="simWaiting" class="{{ $order->status === 'MENUNGGU_BAYAR' ? '' : 'hidden' }} space-y-4 text-center">
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200">
                    <span class="text-xs font-semibold text-amber-800 block">Batas Waktu Pembayaran</span>
                    <span id="simCountdown" class="text-2xl font-extrabold font-mono text-rose-600">--:--</span>
                </div>

                <div class="inline-flex p-4 bg-white border border-slate-200 rounded-2xl">
                    {!! $qrCodeSvg !!}
                </div>

                <button type="button" id="simPayButton" onclick="confirmQrisSimulation()"
                        class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm shadow transition">
                    Simulasikan Bayar QRIS
                </button>
                <p id="simMessage" class="text-xs text-slate-500"></p>
            </div>

            <!-- Status: SIAP_DIAMBIL / SELESAI -->
            <div id="simPaid" class="{{ in_array($order->status, ['SIAP_DIAMBIL', 'SELESAI'], true) ? '' : 'hidden' }} space-y-4 text-center">
                <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200">
                    <div class="text-3xl mb-1">✓</div>
                    <h2 class="text-lg font-extrabold text-emerald-900">Pembayaran Lunas!</h2>
                    <p class="text-xs text-emerald-700 mt-1">
                        Waktu Bayar: <strong id="simPaidAt">{{ $order->paid_at?->format('d/m/Y H:i:s') }}</strong>
                    </p>
                    <div class="mt-4 bg-white border border-emerald-200 rounded-xl py-4">
                        <span class="block text-[11px] font-bold uppercase text-slate-400">Kode Pengambilan</span>
                        <span id="simPickupCode" class="block text-3xl font-extrabold font-mono tracking-[0.25em] text-emerald-700 mt-1">
                            #{{ $order->pickup_code ?? '----' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Status: DIBATALKAN -->
            <div id="simCancelled" class="{{ $order->status === 'DIBATALKAN' ? '' : 'hidden' }} p-5 rounded-2xl bg-rose-50 border border-rose-200 text-center">
                <h2 class="text-base font-extrabold text-rose-800">Transaksi Kedaluwarsa / Dibatalkan</h2>
                <p class="text-xs text-rose-700 mt-1">Batas waktu 15 menit telah berakhir.</p>
            </div>

            <div class="pt-2">
                <a href="{{ route('dashboard') }}"
                   class="block w-full text-center py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow transition">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const orderCode = @json($order->order_code);
            const callbackUrl = @json(route('api.qris.simulate-callback'));
            let status = @json($order->status);
            let remaining = {{ $remainingSeconds }};

            const countdownEl = document.getElementById('simCountdown');
            const payBtn = document.getElementById('simPayButton');
            const msgEl = document.getElementById('simMessage');
            const waitingEl = document.getElementById('simWaiting');
            const paidEl = document.getElementById('simPaid');
            const cancelledEl = document.getElementById('simCancelled');
            const pickupCodeEl = document.getElementById('simPickupCode');
            const paidAtEl = document.getElementById('simPaidAt');

            let timer = null;

            function fmt(sec) {
                const m = Math.floor(sec / 60);
                const s = sec % 60;
                return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            }

            if (status === 'MENUNGGU_BAYAR' && countdownEl) {
                countdownEl.textContent = fmt(remaining);
                timer = setInterval(() => {
                    if (remaining > 0) {
                        remaining--;
                        countdownEl.textContent = fmt(remaining);
                    } else {
                        clearInterval(timer);
                    }
                }, 1000);
            }

            window.confirmQrisSimulation = async function () {
                payBtn.disabled = true;
                payBtn.textContent = 'Mengonfirmasi pembayaran...';
                msgEl.textContent = '';

                try {
                    const res = await fetch(callbackUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ order_code: orderCode })
                    });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        clearInterval(timer);
                        waitingEl.classList.add('hidden');
                        paidEl.classList.remove('hidden');
                        pickupCodeEl.textContent = '#' + data.pickup_code;
                        paidAtEl.textContent = data.paid_at;
                    } else {
                        msgEl.textContent = data.message || 'Gagal mengonfirmasi pembayaran.';
                        msgEl.className = 'text-xs text-rose-600 font-semibold';
                        if (data.status === 'DIBATALKAN') {
                            clearInterval(timer);
                            waitingEl.classList.add('hidden');
                            cancelledEl.classList.remove('hidden');
                        } else {
                            payBtn.disabled = false;
                            payBtn.textContent = 'Simulasikan Bayar QRIS';
                        }
                    }
                } catch (e) {
                    msgEl.textContent = 'Kesalahan koneksi ke server.';
                    msgEl.className = 'text-xs text-rose-600 font-semibold';
                    payBtn.disabled = false;
                    payBtn.textContent = 'Simulasikan Bayar QRIS';
                }
            };
        })();
    </script>
</body>
</html>

