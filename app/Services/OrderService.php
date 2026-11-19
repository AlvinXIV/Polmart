<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    /**
     * Membatalkan seluruh pesanan MENUNGGU_BAYAR yang melewati batas 15 menit
     * dan mengembalikan stok barang ke katalog (FR-08).
     */
    public function cancelExpiredOrders(): int
    {
        $expiredOrderIds = Order::where('status', 'MENUNGGU_BAYAR')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->pluck('id');

        $cancelledCount = 0;

        foreach ($expiredOrderIds as $orderId) {
            $cancelled = DB::transaction(function () use ($orderId) {
                $order = Order::with('items.product')
                    ->whereKey($orderId)
                    ->lockForUpdate()
                    ->first();

                if (! $order || $order->status !== 'MENUNGGU_BAYAR') {
                    return false;
                }

                if (! $order->expires_at || $order->expires_at->isFuture()) {
                    return false;
                }

                $order->update([
                    'status' => 'DIBATALKAN',
                ]);

                foreach ($order->items as $item) {
                    if ($item->product) {
                        $this->stockService->recordMutation(
                            product: $item->product,
                            type: 'return',
                            qty: $item->qty,
                            userId: $order->user_id,
                            reference: $order->order_code,
                            notes: 'Pengembalian stok otomatis karena waktu bayar QRIS 15 menit habis'
                        );
                    }
                }

                return true;
            });

            if ($cancelled) {
                $cancelledCount++;
            }
        }

        return $cancelledCount;
    }

    /**
     * Mengonfirmasi pembayaran simulasi QRIS dan membangkitkan pickup_code 4-digit (FR-07).
     */
    public function confirmQrisPayment(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $lockedOrder = Order::with('items.product')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedOrder->status, ['SIAP_DIAMBIL', 'SELESAI'], true)) {
                return $lockedOrder;
            }

            if ($lockedOrder->status === 'DIBATALKAN') {
                throw new RuntimeException('Pesanan ini sudah dibatalkan karena kedaluwarsa.');
            }

            if ($lockedOrder->status !== 'MENUNGGU_BAYAR') {
                throw new RuntimeException('Status pesanan tidak dapat dibayar.');
            }

            if ($lockedOrder->expires_at && $lockedOrder->expires_at->isPast()) {
                $lockedOrder->update([
                    'status' => 'DIBATALKAN',
                ]);

                foreach ($lockedOrder->items as $item) {
                    if ($item->product) {
                        $this->stockService->recordMutation(
                            product: $item->product,
                            type: 'return',
                            qty: $item->qty,
                            userId: $lockedOrder->user_id,
                            reference: $lockedOrder->order_code,
                            notes: 'Pengembalian stok otomatis karena pembayaran QRIS melewati batas 15 menit'
                        );
                    }
                }

                throw new RuntimeException('Waktu pembayaran pesanan telah habis (kedaluwarsa).');
            }

            $lockedOrder->update([
                'status' => 'SIAP_DIAMBIL',
                'paid_at' => now(),
                'pickup_code' => $lockedOrder->pickup_code ?: Order::generatePickupCode(),
            ]);

            return $lockedOrder->fresh(['items.product', 'user']);
        });
    }
}

