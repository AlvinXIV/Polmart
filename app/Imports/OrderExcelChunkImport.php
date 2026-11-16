<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class OrderExcelChunkImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private bool $commit;

    /** @var array<string, int> */
    private array $skuToProductId = [];

    /** @var array<string, bool> */
    private array $existingOrderCodes = [];

    private int $currentRowNumber = 1; // Baris 1 adalah header

    public int $totalRows = 0;

    public int $validCount = 0;

    public int $invalidCount = 0;

    public float $validTotalAmount = 0;

    public int $importedOrdersCount = 0;

    /** @var array<int, array<string, mixed>> */
    public array $previewRows = [];

    /** @var array<string, bool> */
    private array $seenNewOrderCodes = [];

    public function __construct(bool $commit = false)
    {
        $this->commit = $commit;

        // Cache daftar SKU produk aktif/nonaktif ke memori agar tidak N+1 query per baris
        $this->skuToProductId = Product::pluck('id', 'sku')
            ->mapWithKeys(fn ($id, $sku) => [strtoupper(trim((string) $sku)) => (int) $id])
            ->all();

        // Cache kode pesanan yang sudah ada sebelumnya di database
        $this->existingOrderCodes = Order::pluck('order_code')
            ->mapWithKeys(fn ($code) => [strtoupper(trim((string) $code)) => true])
            ->all();
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function collection(Collection $rows): void
    {
        $parsedChunk = [];

        foreach ($rows as $row) {
            $this->currentRowNumber++;

            // Lewati baris yang benar-benar kosong
            if ($row->filter(fn ($val) => $val !== null && trim((string) $val) !== '')->isEmpty()) {
                continue;
            }

            $this->totalRows++;
            $evaluated = $this->evaluateRow($row->toArray(), $this->currentRowNumber);

            if ($evaluated['is_valid']) {
                $this->validCount++;
                $this->validTotalAmount += $evaluated['subtotal'];
                $parsedChunk[] = $evaluated;
            } else {
                $this->invalidCount++;
            }

            // Simpan maksimal 100 baris contoh untuk ditampilkan di tabel pratinjau UI
            if (! $this->commit && count($this->previewRows) < 100) {
                $this->previewRows[] = $evaluated;
            }
        }

        if ($this->commit && ! empty($parsedChunk)) {
            $this->persistChunk($parsedChunk);
        }
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function evaluateRow(array $raw, int $rowNumber): array
    {
        $errors = [];

        $rawDate = $raw['tanggal_transaksi'] ?? null;
        $orderCode = strtoupper(trim((string) ($raw['kode_pesanan_lama'] ?? '')));
        $customerName = trim((string) ($raw['nama_pemesan'] ?? ''));
        $sku = strtoupper(trim((string) ($raw['kode_barang'] ?? '')));
        $productName = trim((string) ($raw['nama_barang'] ?? ''));
        $qty = $raw['qty'] ?? null;
        $unitPrice = $raw['harga_satuan'] ?? null;

        $parsedDate = $this->parseTransactionDate($rawDate);
        if (! $parsedDate) {
            $errors[] = 'Format tanggal_transaksi tidak valid';
        }

        if ($orderCode === '') {
            $errors[] = 'kode_pesanan_lama wajib diisi';
        } elseif (strlen($orderCode) > 35) {
            $errors[] = 'kode_pesanan_lama maksimal 35 karakter';
        } elseif (isset($this->existingOrderCodes[$orderCode])) {
            $errors[] = "Kode pesanan '{$orderCode}' sudah pernah diimpor / terdaftar";
        }

        if ($customerName === '') {
            $errors[] = 'nama_pemesan wajib diisi';
        } elseif (mb_strlen($customerName) > 120) {
            $errors[] = 'nama_pemesan maksimal 120 karakter';
        }

        $productId = null;
        if ($sku === '') {
            $errors[] = 'kode_barang (SKU) wajib diisi';
        } elseif (! isset($this->skuToProductId[$sku])) {
            $errors[] = "SKU '{$sku}' tidak ditemukan di Master Barang";
        } else {
            $productId = $this->skuToProductId[$sku];
        }

        if (! is_numeric($qty) || (int) $qty < 1 || (int) $qty != $qty) {
            $errors[] = 'qty harus bilangan bulat minimal 1';
        }

        if (! is_numeric($unitPrice) || (float) $unitPrice < 0) {
            $errors[] = 'harga_satuan harus angka >= 0';
        }

        $cleanQty = is_numeric($qty) ? (int) $qty : 0;
        $cleanPrice = is_numeric($unitPrice) ? (float) $unitPrice : 0.0;
        $subtotal = $cleanQty * $cleanPrice;

        return [
            'row_number' => $rowNumber,
            'is_valid' => empty($errors),
            'errors' => $errors,
            'transaction_date' => $parsedDate,
            'transaction_date_display' => $parsedDate ? $parsedDate->format('Y-m-d H:i:s') : (string) $rawDate,
            'order_code' => $orderCode,
            'customer_name' => $customerName,
            'sku' => $sku,
            'product_name' => $productName,
            'product_id' => $productId,
            'qty' => $cleanQty,
            'unit_price' => $cleanPrice,
            'subtotal' => $subtotal,
        ];
    }

    private function parseTransactionDate(mixed $value): ?Carbon
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            }

            return Carbon::parse(trim((string) $value));
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $validRows
     */
    private function persistChunk(array $validRows): void
    {
        DB::transaction(function () use ($validRows) {
            foreach ($validRows as $row) {
                $orderCode = $row['order_code'];

                $order = Order::firstOrCreate(
                    ['order_code' => $orderCode],
                    [
                        'user_id' => null,
                        'customer_name' => $row['customer_name'],
                        'source' => 'import_excel',
                        'status' => 'SELESAI',
                        'total_amount' => 0,
                        'paid_at' => $row['transaction_date'],
                        'transaction_date' => $row['transaction_date'],
                    ]
                );

                if (! isset($this->seenNewOrderCodes[$orderCode])) {
                    $this->seenNewOrderCodes[$orderCode] = true;
                    $this->importedOrdersCount++;
                }

                // Simpan detail item pesanan historis tanpa memotong stok fisik gudang (FR-10)
                $order->items()->create([
                    'product_id' => $row['product_id'],
                    'qty' => $row['qty'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['subtotal'],
                ]);

                $order->increment('total_amount', $row['subtotal']);
            }
        });
    }
}