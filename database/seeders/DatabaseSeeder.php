<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@polmart.test'],
            [
                'member_code' => 'ADM-001',
                'name' => 'Admin Pengurus Polmart',
                'phone' => '081200000001',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'anggota@polmart.test'],
            [
                'member_code' => '224443001',
                'name' => 'Anggota Contoh Polmart',
                'phone' => '081200000002',
                'role' => 'member',
                'password' => Hash::make('password'),
            ]
        );

        $defaultProducts = [
            [
                'sku' => 'ATK-001',
                'name' => 'Buku Tulis Campus',
                'category' => 'Alat Tulis',
                'price' => 5000,
                'stock' => 120,
                'is_active' => true,
            ],
            [
                'sku' => 'ATK-002',
                'name' => 'Pulpen Gel Hitam',
                'category' => 'Alat Tulis',
                'price' => 3000,
                'stock' => 75,
                'is_active' => true,
            ],
            [
                'sku' => 'MNM-001',
                'name' => 'Air Mineral 600ml',
                'category' => 'Minuman',
                'price' => 3500,
                'stock' => 60,
                'is_active' => true,
            ],
            [
                'sku' => 'MKN-001',
                'name' => 'Roti Sari Gandum',
                'category' => 'Makanan',
                'price' => 5500,
                'stock' => 40,
                'is_active' => true,
            ],
        ];

        foreach ($defaultProducts as $productData) {
            Product::firstOrCreate(
                ['sku' => $productData['sku']],
                $productData
            );
        }
    }
}