<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

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
            'https://www.toko360.com/product/100009/104625/1672023663/26/6e49560710ff85f49f3c913783af7a21.jpeg',
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