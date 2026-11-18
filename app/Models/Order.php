<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_code',
    'pickup_code',
    'user_id',
    'customer_name',
    'source',
    'status',
    'total_amount',
    'expires_at',
    'paid_at',
    'transaction_date',
])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'transaction_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateOrderCode(): string
    {
        $prefix = 'PLM-' . now()->format('Ymd') . '-';

        $latestOrder = static::where('order_code', 'like', $prefix . '%')
            ->orderByDesc('order_code')
            ->first();

        $nextNumber = 1;
        if ($latestOrder) {
            $lastSequence = (int) substr($latestOrder->order_code, strlen($prefix));
            $nextNumber = $lastSequence + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Membangkitkan 4-digit kode pengambilan unik untuk pesanan SIAP_DIAMBIL.
     */
    public static function generatePickupCode(): string
    {
        do {
            $code = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        } while (
            static::where('status', 'SIAP_DIAMBIL')
                ->where('pickup_code', $code)
                ->exists()
        );

        return $code;
    }
}