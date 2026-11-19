<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class SampleProductBarcodeSeeder extends Seeder
{
    /**
     * Mengisi contoh barcode EAN-13 yang valid pada 5 produk percontohan.
     */
    public function run(): void
    {
        $samples = [
            'MNM-001' => '8996001300015', // Aqua Air Mineral 600ml
            'MNM-003' => '8996001300039', // Teh Pucuk Harum 350ml
            'SNK-001' => '8996001300114', // Chitato Sapi Panggang 68g
            'SNK-008' => '8996001300183', // Beng-Beng Regular 25g
            'MIE-001' => '8996001300213', // Indomie Goreng Original 85g
        ];

        foreach ($samples as $sku => $barcode) {
            Product::where('sku', $sku)->update(['barcode' => $barcode]);
        }
    }
}
