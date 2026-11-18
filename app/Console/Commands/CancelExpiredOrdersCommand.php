<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class CancelExpiredOrdersCommand extends Command
{
    protected $signature = 'orders:cancel-expired';

    protected $description = 'Membatalkan pesanan MENUNGGU_BAYAR yang melewati batas 15 menit dan mengembalikan stok produk';

    public function handle(OrderService $orderService): int
    {
        $cancelledCount = $orderService->cancelExpiredOrders();

        $this->info("Pesanan kedaluwarsa yang dibatalkan: {$cancelledCount}");

        return self::SUCCESS;
    }
}

