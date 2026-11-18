<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'expense_code',
    'name',
    'category',
    'amount',
    'expense_date',
    'user_id',
    'notes',
    'receipt_path',
])]
class OperatingExpense extends Model
{
    use HasFactory;

    /**
     * Kategori resmi biaya operasional (OPEX) koperasi Polmart.
     * Biaya pembelian/restok barang dagang TIDAK termasuk OPEX (masuk ke HPP saat terjual).
     */
    public const CATEGORIES = [
        'gaji' => 'Gaji & Upah Karyawan',
        'utilitas' => 'Listrik, Air & Internet (Utilitas)',
        'sewa' => 'Sewa Tempat & Gedung',
        'pemeliharaan' => 'Pemeliharaan & Perbaikan',
        'perlengkapan' => 'Perlengkapan Kasir & Toko',
        'transportasi' => 'Transportasi & Logistik',
        'administrasi' => 'Administrasi & Bank',
        'lainnya' => 'Operasional Lainnya',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Generate kode pengeluaran otomatis unik: OPX-YYYYMMDD-XXXX
     * Mendukung offset $attempt untuk menangani collision retry
     */
    public static function generateExpenseCode(?string $date = null, int $attempt = 0): string
    {
        $d = $date ? Carbon::parse($date) : now();
        $prefix = 'OPX-' . $d->format('Ymd') . '-';

        $latest = static::where('expense_code', 'like', $prefix . '%')
            ->orderByDesc('expense_code')
            ->first();

        $nextNumber = 1 + max(0, $attempt);
        if ($latest) {
            $lastSequence = (int) substr($latest->expense_code, strlen($prefix));
            $nextNumber = $lastSequence + 1 + max(0, $attempt);
        }

        return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
