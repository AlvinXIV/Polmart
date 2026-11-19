<?php

namespace App\Imports;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class OrderExcelChunkImport implements ToCollection, WithChunkReading, WithMultipleSheets
{
    private bool $commit;

    /** @var array<string, array{id: int, sku: string, price: float}> */
    private array $skuToProduct = [];

    /** @var array<string, array{id: int, sku: string, price: float}> */
    private array $barcodeToProduct = [];

    /** @var array<string, array{id: int, sku: string, price: float}> */
    private array $nameToProduct = [];

    /** @var array<string, bool> */
    private array $existingOrderCodes = [];

    private int $currentRowNumber = 0;

    private bool $headerDetected = false;

    /** @var array<int, string> */
    private array $headerMap = [];

    private bool $hasOrderCodeCol = false;

    private bool $hasCustomerCol = false;

    public int $totalRows = 0;

    public int $validCount = 0;

    public int $invalidCount = 0;

    public float $validTotalAmount = 0;

    public int $importedOrdersCount = 0;

    /** @var array<int, array<string, mixed>> */
    public array $previewRows = [];

    /** @var array<string, bool> */
    private array $seenNewOrderCodes = [];

    /** @var array<string, array<int, string>> */
    private const HEADER_ALIASES = [
        'tanggal_transaksi' => ['tanggal_transaksi', 'tanggal', 'tgl', 'tgl_transaksi', 'date', 'transaction_date', 'waktu', 'created_at'],
        'kode_pesanan_lama' => ['kode_pesanan_lama', 'kode_pesanan', 'kode_transaksi', 'no_nota', 'nota', 'no_invoice', 'invoice', 'invoice_number', 'order_code', 'id_pesanan', 'no_transaksi'],
        'nama_pemesan' => ['nama_pemesan', 'nama_pembeli', 'pembeli', 'pelanggan', 'customer', 'customer_name', 'nama_pelanggan', 'pemesan', 'nama'],
        'kode_barang' => ['kode_barang', 'sku', 'kode_produk', 'barcode', 'kode', 'product_code', 'id_barang'],
        'nama_barang' => ['nama_barang', 'nama_produk', 'produk', 'barang', 'item', 'product_name', 'deskripsi'],
        'qty' => ['qty', 'jumlah', 'jumlah_beli', 'kuantitas', 'quantity', 'jml'],
        'harga_satuan' => ['harga_satuan', 'harga', 'harga_jual', 'unit_price', 'price', 'tarif'],
        'harga_modal' => ['harga_modal', 'cost_price', 'hpp', 'modal', 'harga_beli', 'beli'],
    ];

    public function __construct(bool $commit = false)
    {
        $this->commit = $commit;

        $products = Product::all();
        foreach ($products as $product) {
            $skuKey = strtoupper(trim((string) $product->sku));
            $entry = [
                'id' => (int) $product->id,
                'sku' => $skuKey,
                'price' => (float) $product->price,
            ];

            if ($skuKey !== '') {
                $this->skuToProduct[$skuKey] = $entry;
            }

            $barcodeKey = strtoupper(trim((string) ($product->barcode ?? '')));
            if ($barcodeKey !== '') {
                $this->barcodeToProduct[$barcodeKey] = $entry;
            }

            $nameKey = $this->normalizeText((string) $product->name);
            if ($nameKey !== '') {
                $this->nameToProduct[$nameKey] = $entry;
            }
        }

        $this->existingOrderCodes = Order::pluck('order_code')
            ->mapWithKeys(fn ($code) => [strtoupper(trim((string) $code)) => true])
            ->all();
    }

    public function sheets(): array
    {
        return [0 => $this];
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
            $rawArr = $row->toArray();

            // Lewati baris yang benar-benar kosong
            $nonEmpty = array_filter($rawArr, fn ($val) => $val !== null && trim((string) $val) !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            $hasStringKeys = count(array_filter(array_keys($rawArr), 'is_string')) > 0;

            if ($hasStringKeys) {
                // Mendukung pemanggilan langsung dengan associative array (mis. unit/feature test)
                $mappedRaw = $this->mapAssociativeRow($rawArr);
                $this->hasOrderCodeCol = array_key_exists('kode_pesanan_lama', $mappedRaw);
                $this->hasCustomerCol = array_key_exists('nama_pemesan', $mappedRaw);
            } else {
                // Membaca baris mentah dari file Excel/CSV (indeks numerik 0, 1, 2, ...)
                $cells = array_values($rawArr);

                if (! $this->headerDetected) {
                    $detectedMap = $this->detectHeaderMap($cells);
                    if (count($detectedMap) >= 2) {
                        $this->headerDetected = true;
                        $this->headerMap = $detectedMap;
                        $this->hasOrderCodeCol = in_array('kode_pesanan_lama', $detectedMap, true);
                        $this->hasCustomerCol = in_array('nama_pemesan', $detectedMap, true);
                        continue;
                    }

                    // Lewati baris judul/kop laporan di bagian atas sebelum header ditemukan
                    if ($this->currentRowNumber <= 15) {
                        continue;
                    }

                    // Fallback positional jika file sama sekali tidak memiliki baris header
                    $this->headerDetected = true;
                    $this->headerMap = [
                        0 => 'tanggal_transaksi',
                        1 => 'kode_pesanan_lama',
                        2 => 'nama_pemesan',
                        3 => 'kode_barang',
                        4 => 'qty',
                        5 => 'harga_satuan',
                        6 => 'harga_modal',
                    ];
                    $this->hasOrderCodeCol = true;
                    $this->hasCustomerCol = true;
                }

                $mappedRaw = $this->mapCellsToFields($cells);
            }

            $this->totalRows++;
            $evaluated = $this->evaluateRow($mappedRaw, $this->currentRowNumber);

            if ($evaluated['is_valid']) {
                $this->validCount++;
                $this->validTotalAmount += $evaluated['subtotal'];
                $parsedChunk[] = $evaluated;
            } else {
                $this->invalidCount++;
            }

            if (! $this->commit && count($this->previewRows) < 100) {
                $this->previewRows[] = $evaluated;
            }
        }

        if ($this->commit && ! empty($parsedChunk)) {
            $this->persistChunk($parsedChunk);
        }
    }

    /**
     * @param  array<string, mixed>  $rawArr
     * @return array<string, mixed>
     */
    private function mapAssociativeRow(array $rawArr): array
    {
        $mapped = [];
        foreach ($rawArr as $key => $val) {
            $slug = $this->normalizeHeaderSlug((string) $key);
            $canonical = $slug;

            foreach (self::HEADER_ALIASES as $target => $aliases) {
                if (in_array($slug, $aliases, true)) {
                    $canonical = $target;
                    break;
                }
            }

            if (! array_key_exists($canonical, $mapped)) {
                $mapped[$canonical] = $val;
            }
        }

        return $mapped;
    }

    /**
     * @param  array<int, mixed>  $cells
     * @return array<int, string>
     */
    private function detectHeaderMap(array $cells): array
    {
        $map = [];
        $usedCanonical = [];

        foreach ($cells as $colIndex => $cellValue) {
            $slug = $this->normalizeHeaderSlug((string) $cellValue);
            if ($slug === '') {
                continue;
            }

            foreach (self::HEADER_ALIASES as $canonical => $aliases) {
                if (isset($usedCanonical[$canonical])) {
                    continue;
                }
                if (in_array($slug, $aliases, true)) {
                    $map[(int) $colIndex] = $canonical;
                    $usedCanonical[$canonical] = true;
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * @param  array<int, mixed>  $cells
     * @return array<string, mixed>
     */
    private function mapCellsToFields(array $cells): array
    {
        $result = [];
        foreach ($this->headerMap as $colIndex => $canonical) {
            $result[$canonical] = $cells[$colIndex] ?? null;
        }

        return $result;
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
        $skuInput = strtoupper(trim((string) ($raw['kode_barang'] ?? '')));
        $productName = trim((string) ($raw['nama_barang'] ?? ''));
        $qty = $this->parseCleanNumber($raw['qty'] ?? null);
        $unitPriceRaw = $raw['harga_satuan'] ?? null;
        $unitPrice = $this->parseCleanNumber($unitPriceRaw);

        $parsedDate = $this->parseTransactionDate($rawDate);
        if (! $parsedDate) {
            $errors[] = 'Format tanggal_transaksi tidak valid';
        }

        if ($orderCode === '' && ! $this->hasOrderCodeCol && $parsedDate) {
            $orderCode = 'IMP-' . $parsedDate->format('Ymd') . '-R' . $rowNumber;
        }

        if ($orderCode === '') {
            $errors[] = 'kode_pesanan_lama wajib diisi';
        } elseif (strlen($orderCode) > 35) {
            $errors[] = 'kode_pesanan_lama maksimal 35 karakter';
        } elseif (isset($this->existingOrderCodes[$orderCode])) {
            $errors[] = "Kode pesanan '{$orderCode}' sudah pernah diimpor / terdaftar";
        }

        if ($customerName === '' && ! $this->hasCustomerCol) {
            $customerName = 'Pelanggan Umum';
        }

        if ($customerName === '') {
            $errors[] = 'nama_pemesan wajib diisi';
        } elseif (mb_strlen($customerName) > 120) {
            $errors[] = 'nama_pemesan maksimal 120 karakter';
        }

        $matchedProduct = $this->resolveProduct($skuInput, $productName);
        $productId = $matchedProduct['id'] ?? null;
        $resolvedSku = $matchedProduct['sku'] ?? $skuInput;

        if ($skuInput === '' && $productName === '') {
            $errors[] = 'kode_barang (SKU) wajib diisi';
        } elseif (! $matchedProduct) {
            $label = $skuInput !== '' ? "SKU '{$skuInput}'" : "Barang '{$productName}'";
            $errors[] = "{$label} tidak ditemukan di Master Barang";
        }

        if (! is_numeric($qty) || (int) $qty < 1 || (int) $qty != $qty) {
            $errors[] = 'qty harus bilangan bulat minimal 1';
        }

        $isPriceBlank = $unitPriceRaw === null || trim((string) $unitPriceRaw) === '';
        if ($isPriceBlank && $matchedProduct) {
            $unitPrice = $matchedProduct['price'];
        }

        if (! is_numeric($unitPrice) || (float) $unitPrice < 0) {
            $errors[] = 'harga_satuan harus angka >= 0';
        }

        $cleanQty = is_numeric($qty) ? (int) $qty : 0;
        $cleanPrice = is_numeric($unitPrice) ? (float) $unitPrice : 0.0;
        $subtotal = $cleanQty * $cleanPrice;

        $rawCost = $this->parseCleanNumber($raw['harga_modal'] ?? $raw['cost_price'] ?? $raw['hpp'] ?? null);
        $cleanCost = is_numeric($rawCost) && (float) $rawCost >= 0 ? (float) $rawCost : 0.0;
        $subtotalCost = $cleanQty * $cleanCost;

        return [
            'row_number' => $rowNumber,
            'is_valid' => empty($errors),
            'errors' => $errors,
            'transaction_date' => $parsedDate,
            'transaction_date_display' => $parsedDate ? $parsedDate->format('Y-m-d H:i:s') : (string) $rawDate,
            'order_code' => $orderCode,
            'customer_name' => $customerName,
            'sku' => $resolvedSku,
            'product_name' => $productName,
            'product_id' => $productId,
            'qty' => $cleanQty,
            'unit_price' => $cleanPrice,
            'subtotal' => $subtotal,
            'cost_price' => $cleanCost,
            'subtotal_cost' => $subtotalCost,
        ];
    }

    /**
     * @return array{id: int, sku: string, price: float}|null
     */
    private function resolveProduct(string $skuOrBarcode, string $productName): ?array
    {
        if ($skuOrBarcode !== '') {
            if (isset($this->skuToProduct[$skuOrBarcode])) {
                return $this->skuToProduct[$skuOrBarcode];
            }
            if (isset($this->barcodeToProduct[$skuOrBarcode])) {
                return $this->barcodeToProduct[$skuOrBarcode];
            }
        }

        $normalizedName = $this->normalizeText($productName);
        if ($normalizedName !== '') {
            if (isset($this->nameToProduct[$normalizedName])) {
                return $this->nameToProduct[$normalizedName];
            }

            if (mb_strlen($normalizedName) >= 4) {
                $partialMatches = [];
                foreach ($this->nameToProduct as $catalogName => $entry) {
                    if (str_contains($catalogName, $normalizedName)) {
                        $partialMatches[] = $entry;
                    }
                }
                if (count($partialMatches) === 1) {
                    return $partialMatches[0];
                }
            }
        }

        return null;
    }

    private function parseCleanNumber(mixed $value): mixed
    {
        if ($value === null || is_int($value) || is_float($value)) {
            return $value;
        }

        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }

        $cleaned = preg_replace('/^rp\.?\s*/i', '', $str) ?? $str;
        $cleaned = str_replace(' ', '', $cleaned);

        if (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $cleaned)) {
            $cleaned = str_replace('.', '', $cleaned);
        } elseif (preg_match('/^-?\d{1,3}(,\d{3})+$/', $cleaned)) {
            $cleaned = str_replace(',', '', $cleaned);
        }

        return $cleaned;
    }

    private function normalizeHeaderSlug(string $header): string
    {
        $slug = mb_strtolower(trim($header));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug) ?? '';

        return trim($slug, '_');
    }

    private function normalizeText(string $text): string
    {
        $clean = mb_strtolower(trim($text));
        $clean = preg_replace('/\s+/', ' ', $clean) ?? '';

        return $clean;
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

            $str = trim((string) $value);
            if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})(?:\s+(\d{1,2}:\d{2}(?::\d{2})?))?$/', $str, $m)) {
                $timePart = $m[4] ?? '00:00:00';
                return Carbon::parse("{$m[3]}-{$m[2]}-{$m[1]} {$timePart}");
            }

            return Carbon::parse($str);
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

                $order->items()->create([
                    'product_id' => $row['product_id'],
                    'qty' => $row['qty'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['subtotal'],
                    'cost_price' => $row['cost_price'] ?? 0.0,
                    'subtotal_cost' => $row['subtotal_cost'] ?? 0.0,
                ]);

                $order->increment('total_amount', $row['subtotal']);
            }
        });
    }
}