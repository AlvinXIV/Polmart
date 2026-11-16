<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockService
{
    /**
     * Mengubah stok produk secara atomik dan mencatat log mutasi stok (FR-04).
     *
     * @param  string  $type  'in' | 'out' | 'return' | 'adjustment'
     */
    public function recordMutation(
        Product $product,
        string $type,
        int $qty,
        ?int $userId = null,
        ?string $reference = null,
        ?string $notes = null
    ): StockMutation {
        return DB::transaction(function () use ($product, $type, $qty, $userId, $reference, $notes) {
            $lockedProduct = Product::whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $stockBefore = $lockedProduct->stock;

            $delta = match ($type) {
                'in', 'return' => abs($qty),
                'out' => -abs($qty),
                'adjustment' => $qty,
                default => throw new RuntimeException("Tipe mutasi stok tidak valid: {$type}"),
            };

            $stockAfter = $stockBefore + $delta;

            if ($stockAfter < 0) {
                throw new RuntimeException("Stok barang '{$lockedProduct->name}' tidak mencukupi (tersisa {$stockBefore}).");
            }

            $lockedProduct->update([
                'stock' => $stockAfter,
            ]);

            return StockMutation::create([
                'product_id' => $lockedProduct->id,
                'user_id' => $userId,
                'type' => $type,
                'qty' => abs($delta),
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference' => $reference,
                'notes' => $notes,
            ]);
        });
    }
}