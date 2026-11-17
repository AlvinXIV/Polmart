<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ProductImageSeeder extends Seeder
{
    /**
     * Exact product-image sources keyed by the SKU from SupermarketSeeder.
     * There is deliberately no text search or generic Wikimedia fallback:
     * an unmatched product must never receive an unrelated photo.
     */
    private const IMAGE_SOURCES = [
        // Minuman kemasan
        'MNM-001' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/98/MTA-3759227/aqua_air-mineral-aqua-600-ml-botol_full02.jpg',
            'https://down-id.img.susercontent.com/file/sg-11134201-8262x-mjy1hynpo5qacb',
        ],
        'MNM-002' => [
            'https://c.alfagift.id/product/1/1_A12460990365_20230801161325324_base.jpg',
            'https://www.shielddrug.com/web/image/product.template/26987/image_1920?unique=03555e9',
        ],
        'MNM-003' => [
            'https://down-id.img.susercontent.com/file/id-11134207-7rasf-m3nq4l2bg76g26',
        ],
        'MNM-004' => [
            'https://arti-assets.sgp1.digitaloceanspaces.com/dutalia/products/0c0496d6-100c-4a87-95ba-7a832039a536.jpg',
        ],
        'MNM-005' => [
            'https://tastysnack.id/cdn/shop/files/4_4396803f-b1fe-4f81-a5c1-6be99e5e4ff0_1200x1200.png?v=1707105297',
        ],
        'MNM-006' => [
            'https://i.gojekapi.com/darkroom/gomart-public-production/v2/images/public/images/6dffe8f4-b028-4414-9be1-5a90bfa7a326_Ultra-Milk-Susu-UHT-Full-Cream-Kotak-250-ml.jpg',
        ],
        'MNM-007' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/93/MTA-2582724/ultra_ultra-milk-choco-slim-coklat-susu-uht--250-ml-_full02.jpg',
        ],
        'MNM-008' => [
            'https://wingonfile01.easesales.com/upload/3302BA3D66D2EDC2/202005/4834603554203765292.png_700x650.ashx',
        ],
        'MNM-009' => [
            'https://down-id.img.susercontent.com/file/id-11134207-7r98o-lkuobly49ceb3a',
        ],
        'MNM-010' => [
            'https://down-id.img.susercontent.com/file/1c64b772ebd4a2e863966e8ce87c0c91',
        ],

        // Makanan ringan & roti
        'SNK-001' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/109/MTA-91895437/chitato_chitato_snack_potato_chips_sapi_panggang_68g_full01_gnydtqnu.jpg',
            'https://asbajayaberkah.com/wp-content/uploads/2025/01/CHITATO-RASA-SAPI-PANGGANG-68G-2-scaled.jpeg',
        ],
        'SNK-002' => [
            'https://image.astronauts.cloud/product-images/2025/4/kusukakejubakar60gr_78fef514-d48d-4a75-81c3-371f88d7c85d_900x900.jpg',
        ],
        'SNK-003' => [
            'https://cdn1.npcdn.net/images/ead61d616837969c6798a76a7af7a25a_1756962834.png?md5id=9248afa3ae57517509334eae846760a2&new_height=1000&new_width=1000&type=9&w=-62170008925',
        ],
        'SNK-004' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full//89/MTA-44741321/brd-44261_richeese-nabati-wafer-pouch-115-gram_full01.jpg',
        ],
        'SNK-005' => [
            'https://down-id.img.susercontent.com/file/23d20c63f096f8ac81a24c77332ddf97',
        ],
        'SNK-006' => [
            'https://img.id.my-best.com/product_images/40854e09628894c6005a59916fc2b061.jpg?fit=clip&h=800&ixlib=rails-4.3.1&lossless=0&q=70&s=e9d0082f41ed9e32374dc444d472beb0&w=800',
        ],
        'SNK-007' => [
            'https://down-id.img.susercontent.com/file/sg-11134201-23020-gx5cxk152cnv29',
        ],
        'SNK-008' => [
            'https://down-id.img.susercontent.com/file/id-11134207-7rase-m48baim9eyt64e',
        ],
        'SNK-009' => [
            'https://www.planet-foods.com/cdn/shop/files/PockyChocolate-47g_2.webp?v=1708529786&width=1214',
        ],

        // Mi instan & makanan siap saji
        'MIE-001' => [
            'https://allmartshop.com/cdn/shop/files/IndomieInstantNoodlesMiGorengOriginal85g.jpg',
            'https://foodbybox.com/wp-content/uploads/2023/03/Mie-Goreng-Special-1-pcs-scaled.jpg',
            'https://www.myfilipinostore.com/cdn/shop/files/NETWT.907g-2025-10-08T013143.703.png',
        ],
        'MIE-002' => [
            'https://coreimages.lottemart.co.id/ord/06/1088134000',
        ],
        'MIE-003' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/catalog-image/108/MTA-178383744/pop_mie_pop_mie_mi_instant_cup_rasa_ayam_75_gr_noodles_full01_c6580a78.jpg',
        ],
        'MIE-004' => [
            'https://down-id.img.susercontent.com/file/sg-11134201-7rcev-lswhcn7xoc2q67',
        ],
        'MIE-005' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/catalog-image/102/MTA-175109942/brd-60517_bon-cabe-rasa-original-level-15-kobe-45g-cabe-bubuk-tabur-chilli-powder-hot-pedas-boncabe_full01-addf8f5f.jpg',
        ],

        // Alat Tulis (ATK)
        'ATK-001' => [
            'https://img.lazcdn.com/g/p/65ff77a5d390635f13adab97fcfcbc86.jpg',
            'https://lzd-img-global.slatic.net/g/ff/kf/S2c51b078f90646bcacfc6d22e5d46274d.jpg',
            'https://filebroker-cdn.lazada.co.id/kf/S76f6a6a66bac41e68db7b384ee609787t.jpg',
        ],
        'ATK-002' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/99/MTA-31516488/standard_pulpen-standard-ae7-hitam-ballpoint_full01.jpg',
            'https://lzd-img-global.slatic.net/g/p/b3f8d228f5f72016c45b596b197c3bd9.jpg',
        ],
        'ATK-003' => [
            'https://id-test-11.slatic.net/p/55a4ebbbaf72fbb911044d1e33dedc21.jpg',
            'https://lzd-img-global.slatic.net/g/ff/kf/S4704cbd33c534697a1d7c86e30e92aa3X.jpg',
        ],
        'ATK-004' => [
            'https://img.mbizmarket.co.id/products/thumbs/800x800/2021/04/30/f721d8c8ec4e555a4b8db854489471d4.jpg',
            'https://img.mbizmarket.co.id/products/thumbs/800x800/2022/04/08/17f8991866c0b22deede058d4b924d4a.jpg',
        ],
        'ATK-005' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/catalog-image/96/MTA-155365106/joyko_correction_fluid_-_tip_ex_-_tip_x_-_tipe_x_joyko_cf-s209_cf_s209_full01_b3bckle6.jpg',
            'https://lzd-img-global.slatic.net/g/ff/kf/S93e5f8e3fcd0492f8abc94fdbac3d130z.jpg',
        ],
        'ATK-006' => [
            'https://img.mbizmarket.co.id/products/thumbs/800x800/2023/06/06/ac9cc3e72978308e585be0b05650458e.jpg',
            'https://img.lazcdn.com/g/p/f4c1055dfa7e00ecd99b57518bf6da40.jpg',
        ],

        // Kebutuhan Harian & Kesehatan
        'KSH-001' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/catalog-image/MTA-72121502/paseo_tissue_tisu_paseo_smart_facial_wajah_250_sheet_2ply_full03_5795e37f.jpg',
            'https://img.lazcdn.com/g/p/2c25a97607999772cbbf11a261fcd30d.jpg',
        ],
        'KSH-002' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/96/MTA-31186572/sidomuncul_tolak_angin_sidomuncul_cair_sido_muncul_1_box-_12_sachet-_15_ml_full01_luhy2tpo.jpg',
            'https://lzd-img-global.slatic.net/g/p/bcdd228a96ea533e24804c5f4ed4963b.jpg',
        ],
        'KSH-003' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/97/MTA-95511832/br-m036969-06639_panadol-extra-strip-10-kaplet_full01.jpg',
            'https://d2qjkwm11akmwu.cloudfront.net/products/32854_29-8-2022_15-21-28-1665822586.png',
        ],
        'KSH-004' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/catalog-image/113/MTA-124679858/cap_lang_minyak_kayu_putih_cap_lang_all_variant_-_bpom_-30ml_60ml_120ml_210ml-_full03_mniajy6e.jpg',
            'https://www.tokoindonesia.co.uk/wp-content/uploads/2023/07/minyak-kayu-putih-30ml-600x602.png',
        ],
        'KSH-005' => [
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/catalog-image/MTA-16722165/lifebuoy_lifebuoy_sabun_mandi_batang_anti_bakteri_total_10_4_x_60_g_full03_bqhzn0zl.jpeg',
            'https://www.static-src.com/wcsstore/Indraprastha/images/catalog/full/MTA-2720071/lifebuoy_lifebuoy-total-10-sabun-batang--80-g-_full04.jpg',
        ],

        // Makanan
        'MKN-001' => [
            'https://image.astronauts.cloud/product-images/2024/10/SariRotiTawarGandum10Lembar400gram_5a795539-f8c1-4378-857c-dce70930ea80_900x900.jpg',
            'https://filebroker-cdn.lazada.co.id/kf/S9611b66901a34baaa605e6ab0a83c0deW.jpg',
        ],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $imageColumn = collect(['image', 'image_path', 'photo', 'gambar'])
            ->first(fn (string $col) => Schema::hasColumn('products', $col));

        if (! $imageColumn) {
            $this->command?->warn('Kolom gambar pada tabel products tidak ditemukan.');
            return;
        }

        $targetDir = storage_path('app/public/products');
        File::ensureDirectoryExists($targetDir);

        $products = DB::table('products')
            ->whereIn('sku', array_keys(self::IMAGE_SOURCES))
            ->get(['id', 'sku', 'name', $imageColumn]);

        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($products as $product) {
            $existingImage = $product->{$imageColumn};
            if ($existingImage && File::exists(storage_path('app/public/' . $existingImage))) {
                continue;
            }

            $urls = self::IMAGE_SOURCES[$product->sku] ?? [];
            $savedPath = null;

            foreach ($urls as $url) {
                $downloaded = $this->downloadImage($product->sku, $url, $targetDir);
                if ($downloaded !== null) {
                    $savedPath = $downloaded;
                    break;
                }
            }

            if ($savedPath !== null) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        $imageColumn => $savedPath,
                        'updated_at' => now(),
                    ]);
                $updatedCount++;
            } else {
                $skippedCount++;
                $this->command?->warn("Gagal mengunduh gambar untuk {$product->sku} ({$product->name}); nilai lama dipertahankan.");
            }
        }

        $this->command?->info("ProductImageSeeder selesai: {$updatedCount} produk diperbarui, {$skippedCount} dilewati.");
    }

    private function downloadImage(string $sku, string $url, string $targetDir): ?string
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; PolmartSeeder/1.0)',
                    'Accept' => 'image/*',
                ])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $body = $response->body();
            if ($body === '' || strlen($body) < 100) {
                return null;
            }

            $contentType = strtolower((string) $response->header('Content-Type'));
            $extension = match (true) {
                str_contains($contentType, 'png') => 'png',
                str_contains($contentType, 'webp') => 'webp',
                str_contains($contentType, 'jpeg'), str_contains($contentType, 'jpg') => 'jpg',
                str_ends_with(strtolower(parse_url($url, PHP_URL_PATH) ?? ''), '.png') => 'png',
                str_ends_with(strtolower(parse_url($url, PHP_URL_PATH) ?? ''), '.webp') => 'webp',
                default => 'jpg',
            };

            if ($contentType !== '' && ! str_contains($contentType, 'image/') && ! str_contains($contentType, 'octet-stream')) {
                return null;
            }

            $filename = strtolower($sku) . '.' . $extension;
            File::put($targetDir . DIRECTORY_SEPARATOR . $filename, $body);

            return 'products/' . $filename;
        } catch (Throwable) {
            return null;
        }
    }
}
