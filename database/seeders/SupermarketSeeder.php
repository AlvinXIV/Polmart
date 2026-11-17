<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SupermarketSeeder extends Seeder
{
    private array $columnCache = [];

    private function filterCols(string $table, array $data): array
    {
        if (!isset($this->columnCache[$table])) {
            $this->columnCache[$table] = array_flip(Schema::getColumnListing($table));
        }

        return array_intersect_key($data, $this->columnCache[$table]);
    }

    public function run(): void
    {
        $now = now();
        $defaultPassword = Hash::make('password');

        $usersData = [
            ['name' => 'Admin Pengurus Polmart', 'email' => 'admin@polmart.test', 'role' => 'admin', 'member_number' => 'ADM-001', 'phone' => '081234567890'],
            ['name' => 'Anggota Contoh Polmart', 'email' => 'anggota@polmart.test', 'role' => 'member', 'member_number' => '224443001', 'phone' => '081234567891'],
            ['name' => 'Dzikri Rahman Hafidz', 'email' => 'dzikri@polmart.test', 'role' => 'member', 'member_number' => '224443002', 'phone' => '081220010002'],
            ['name' => 'Luthfi Rustian', 'email' => 'luthfi@polmart.test', 'role' => 'member', 'member_number' => '224443003', 'phone' => '081220010003'],
            ['name' => 'Matahari Dhia Alwafy', 'email' => 'matahari@polmart.test', 'role' => 'member', 'member_number' => '224443004', 'phone' => '081220010004'],
            ['name' => 'Muhammad Faiz Azril', 'email' => 'faiz@polmart.test', 'role' => 'member', 'member_number' => '224443005', 'phone' => '081220010005'],
            ['name' => 'Wiryawan Pratama', 'email' => 'wiryawan@polmart.test', 'role' => 'member', 'member_number' => '224443006', 'phone' => '081220010006'],
            ['name' => 'Najma Aulia', 'email' => 'najma@polmart.test', 'role' => 'member', 'member_number' => '224443007', 'phone' => '081220010007'],
            ['name' => 'Alvin Pratama', 'email' => 'alvin@polmart.test', 'role' => 'member', 'member_number' => '224443008', 'phone' => '081220010008'],
            ['name' => 'Farel Saputra', 'email' => 'farel@polmart.test', 'role' => 'member', 'member_number' => '224443009', 'phone' => '081220010009'],
        ];

        $userIds = [];
        foreach ($usersData as $u) {
            $existing = DB::table('users')->where('email', $u['email'])->first();
            if ($existing) {
                $userIds[] = $existing->id;
                continue;
            }

            $userIds[] = DB::table('users')->insertGetId($this->filterCols('users', [
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => $defaultPassword,
                'role' => $u['role'],
                'member_number' => $u['member_number'],
                'nomor_anggota' => $u['member_number'],
                'phone' => $u['phone'],
                'no_hp' => $u['phone'],
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $adminId = $userIds[0];
        $memberIds = array_slice($userIds, 1);

        $productsData = [
            ['sku' => 'MNM-001', 'name' => 'Aqua Air Mineral 600ml', 'category' => 'Minuman', 'price' => 4000, 'stock' => 120, 'desc' => 'Air mineral botol kemasan 600ml'],
            ['sku' => 'MNM-002', 'name' => 'Le Minerale 600ml', 'category' => 'Minuman', 'price' => 4000, 'stock' => 95, 'desc' => 'Air mineral dengan kandungan mineral alami'],
            ['sku' => 'MNM-003', 'name' => 'Teh Pucuk Harum 350ml', 'category' => 'Minuman', 'price' => 4500, 'stock' => 80, 'desc' => 'Minuman teh melati siap minum'],
            ['sku' => 'MNM-004', 'name' => 'Teh Botol Sosro Kotak 250ml', 'category' => 'Minuman', 'price' => 4000, 'stock' => 72, 'desc' => 'Teh melati kemasan kotak UHT'],
            ['sku' => 'MNM-005', 'name' => 'Good Day Cappuccino 250ml', 'category' => 'Minuman', 'price' => 7000, 'stock' => 54, 'desc' => 'Kopi susu cappuccino siap minum'],
            ['sku' => 'MNM-006', 'name' => 'Ultra Milk Full Cream 250ml', 'category' => 'Minuman', 'price' => 7500, 'stock' => 60, 'desc' => 'Susu UHT segar full cream'],
            ['sku' => 'MNM-007', 'name' => 'Ultra Milk Cokelat 250ml', 'category' => 'Minuman', 'price' => 7500, 'stock' => 65, 'desc' => 'Susu UHT rasa cokelat'],
            ['sku' => 'MNM-008', 'name' => 'Pocari Sweat 500ml', 'category' => 'Minuman', 'price' => 8500, 'stock' => 48, 'desc' => 'Minuman isotonik pengganti ion tubuh'],
            ['sku' => 'MNM-009', 'name' => 'Floridina Orange 350ml', 'category' => 'Minuman', 'price' => 4000, 'stock' => 70, 'desc' => 'Minuman bulir jeruk asli'],
            ['sku' => 'MNM-010', 'name' => 'Bear Brand Susu Steril 189ml', 'category' => 'Minuman', 'price' => 11000, 'stock' => 40, 'desc' => 'Susu steril murni kaleng'],
            ['sku' => 'SNK-001', 'name' => 'Chitato Sapi Panggang 68g', 'category' => 'Makanan Ringan', 'price' => 11500, 'stock' => 45, 'desc' => 'Keripik kentang gelombang rasa sapi panggang'],
            ['sku' => 'SNK-002', 'name' => 'Kusuka Keripik Singkong Keju 60g', 'category' => 'Makanan Ringan', 'price' => 7500, 'stock' => 50, 'desc' => 'Keripik singkong renyah rasa keju bakar'],
            ['sku' => 'SNK-003', 'name' => 'Oreo Original 119.6g', 'category' => 'Makanan Ringan', 'price' => 9500, 'stock' => 55, 'desc' => 'Biskuit cokelat dengan krim vanila'],
            ['sku' => 'SNK-004', 'name' => 'Nabati Richeese Wafer 115g', 'category' => 'Makanan Ringan', 'price' => 8500, 'stock' => 60, 'desc' => 'Wafer renyah berlapis krim keju'],
            ['sku' => 'SNK-005', 'name' => 'Sari Roti Sobek Cokelat Keju', 'category' => 'Roti & Kue', 'price' => 16500, 'stock' => 22, 'desc' => 'Roti lembut isi selai cokelat dan keju'],
            ['sku' => 'SNK-006', 'name' => 'Sari Roti Dorayaki Cokelat', 'category' => 'Roti & Kue', 'price' => 7500, 'stock' => 30, 'desc' => 'Kue dorayaki lembut isi krim cokelat'],
            ['sku' => 'SNK-007', 'name' => 'SilverQueen Chunky Bar 30g', 'category' => 'Makanan Ringan', 'price' => 10500, 'stock' => 38, 'desc' => 'Cokelat susu dengan kacang mede'],
            ['sku' => 'SNK-008', 'name' => 'Beng-Beng Regular 25g', 'category' => 'Makanan Ringan', 'price' => 3000, 'stock' => 100, 'desc' => 'Wafer karamel krispi berlapis cokelat'],
            ['sku' => 'SNK-009', 'name' => 'Pocky Chocolate 47g', 'category' => 'Makanan Ringan', 'price' => 9000, 'stock' => 42, 'desc' => 'Biskuit stik salut krim cokelat'],
            ['sku' => 'MIE-001', 'name' => 'Indomie Goreng Original 85g', 'category' => 'Mie & Sembako', 'price' => 3500, 'stock' => 150, 'desc' => 'Mie instan goreng favorit'],
            ['sku' => 'MIE-002', 'name' => 'Indomie Kuah Soto Mie 70g', 'category' => 'Mie & Sembako', 'price' => 3500, 'stock' => 110, 'desc' => 'Mie instan kuah rasa soto'],
            ['sku' => 'MIE-003', 'name' => 'Pop Mie Rasa Ayam 75g', 'category' => 'Mie & Sembako', 'price' => 6000, 'stock' => 64, 'desc' => 'Mie instan cup praktis siap seduh'],
            ['sku' => 'MIE-004', 'name' => 'Pop Mie Pedas Dower 75g', 'category' => 'Mie & Sembako', 'price' => 6500, 'stock' => 48, 'desc' => 'Mie instan cup rasa ayam pedas'],
            ['sku' => 'MIE-005', 'name' => 'BonCabe Level 15 Botol 45g', 'category' => 'Mie & Sembako', 'price' => 11500, 'stock' => 28, 'desc' => 'Sambal tabur cabai kering pedas'],
            ['sku' => 'ATK-001', 'name' => 'Buku Tulis Campus 50 Lembar', 'category' => 'Alat Tulis', 'price' => 5500, 'stock' => 85, 'desc' => 'Buku tulis ukuran B5 standar kuliah'],
            ['sku' => 'ATK-002', 'name' => 'Pulpen Standard AE7 Hitam', 'category' => 'Alat Tulis', 'price' => 3000, 'stock' => 140, 'desc' => 'Bolpoin tinta hitam 0.5mm'],
            ['sku' => 'ATK-003', 'name' => 'Pensil Faber-Castell 2B', 'category' => 'Alat Tulis', 'price' => 5000, 'stock' => 75, 'desc' => 'Pensil grafit 2B untuk sketsa & ujian'],
            ['sku' => 'ATK-004', 'name' => 'Penghapus Staedtler Rasoplast', 'category' => 'Alat Tulis', 'price' => 4500, 'stock' => 60, 'desc' => 'Penghapus bersih bebas residu'],
            ['sku' => 'ATK-005', 'name' => 'Tip-X Cair Joyko CF-S209', 'category' => 'Alat Tulis', 'price' => 6500, 'stock' => 40, 'desc' => 'Correction fluid mata pena logam'],
            ['sku' => 'ATK-006', 'name' => 'Map Plastik Kancing A4', 'category' => 'Alat Tulis', 'price' => 4000, 'stock' => 90, 'desc' => 'Map dokumen transparan ukuran A4'],
            ['sku' => 'KSH-001', 'name' => 'Tissue Paseo Smart 250 Sheets', 'category' => 'Kebutuhan Harian', 'price' => 13500, 'stock' => 36, 'desc' => 'Tisu wajah lembut 2 ply'],
            ['sku' => 'KSH-002', 'name' => 'Tolak Angin Cair Sido Muncul', 'category' => 'Kesehatan', 'price' => 5000, 'stock' => 80, 'desc' => 'Herbal cair pereda masuk angin 15ml'],
            ['sku' => 'KSH-003', 'name' => 'Panadol Extra Strip 10 Kaplet', 'category' => 'Kesehatan', 'price' => 14500, 'stock' => 25, 'desc' => 'Obat pereda sakit kepala dan demam'],
            ['sku' => 'KSH-004', 'name' => 'Minyak Kayu Putih Cap Lang 30ml', 'category' => 'Kesehatan', 'price' => 13000, 'stock' => 30, 'desc' => 'Minyak kayu putih penghangat tubuh'],
            ['sku' => 'KSH-005', 'name' => 'Sabun Mandi Lifebuoy Total 10', 'category' => 'Kebutuhan Harian', 'price' => 5500, 'stock' => 50, 'desc' => 'Sabun batang antibakteri 70g'],
        ];

        $productRecords = [];
        foreach ($productsData as $p) {
            $existing = DB::table('products')->where('sku', $p['sku'])->first();
            if ($existing) {
                $productRecords[$p['sku']] = $existing;
                continue;
            }

            $productId = DB::table('products')->insertGetId($this->filterCols('products', [
                'sku' => $p['sku'],
                'name' => $p['name'],
                'category' => $p['category'],
                'description' => $p['desc'],
                'price' => $p['price'],
                'stock' => $p['stock'],
                'is_active' => true,
                'created_at' => $now->copy()->subDays(30),
                'updated_at' => $now,
            ]));

            if (Schema::hasTable('stock_mutations')) {
                DB::table('stock_mutations')->insert($this->filterCols('stock_mutations', [
                    'product_id' => $productId,
                    'user_id' => $adminId,
                    'type' => 'in',
                    'qty' => $p['stock'],
                    'quantity' => $p['stock'],
                    'stock_before' => 0,
                    'stock_after' => $p['stock'],
                    'notes' => 'Stok awal inventaris Polmart',
                    'created_at' => $now->copy()->subDays(30),
                    'updated_at' => $now->copy()->subDays(30),
                ]));
            }

            $productRecords[$p['sku']] = (object) [
                'id' => $productId,
                'name' => $p['name'],
                'price' => $p['price'],
            ];
        }

        if (!Schema::hasTable('orders') || !Schema::hasTable('order_items')) {
            return;
        }

        $sampleOrders = [
            ['code' => 'PLM-20261101-001', 'days_ago' => 16, 'source' => 'walk_in', 'method' => 'cash', 'status' => 'SELESAI', 'items' => [['MNM-001', 2], ['SNK-001', 1], ['ATK-002', 2]]],
            ['code' => 'PLM-20261102-002', 'days_ago' => 15, 'source' => 'online', 'method' => 'qris', 'status' => 'SELESAI', 'items' => [['MIE-001', 5], ['MNM-003', 2]]],
            ['code' => 'PLM-20261103-003', 'days_ago' => 14, 'source' => 'walk_in', 'method' => 'cash', 'status' => 'SELESAI', 'items' => [['ATK-001', 3], ['ATK-003', 2], ['ATK-004', 1]]],
            ['code' => 'PLM-20261104-004', 'days_ago' => 13, 'source' => 'online', 'method' => 'qris', 'status' => 'SELESAI', 'items' => [['SNK-005', 1], ['MNM-006', 2]]],
            ['code' => 'PLM-20261105-005', 'days_ago' => 12, 'source' => 'import_excel', 'method' => 'cash', 'status' => 'SELESAI', 'items' => [['MNM-002', 4], ['SNK-008', 6]]],
            ['code' => 'PLM-20261106-006', 'days_ago' => 11, 'source' => 'walk_in', 'method' => 'cash', 'status' => 'SELESAI', 'items' => [['MIE-003', 2], ['MNM-008', 2]]],
            ['code' => 'PLM-20261107-007', 'days_ago' => 10, 'source' => 'online', 'method' => 'qris', 'status' => 'SELESAI', 'items' => [['SNK-007', 2], ['MNM-005', 2], ['KSH-001', 1]]],
            ['code' => 'PLM-20261108-008', 'days_ago' => 9, 'source' => 'walk_in', 'method' => 'cash', 'status' => 'SELESAI', 'items' => [['KSH-002', 3], ['MNM-010', 2]]],
            ['code' => 'PLM-20261109-009', 'days_ago' => 8, 'source' => 'online', 'method' => 'qris', 'status' => 'SELESAI', 'items' => [['SNK-003', 2], ['SNK-004', 2], ['MNM-007', 2]]],
            ['code' => 'PLM-20261110-010', 'days_ago' => 7, 'source' => 'walk_in', 'method' => 'cash', 'status' => 'SELESAI', 'items' => [['ATK-005', 1], ['ATK-006', 4], ['ATK-002', 3]]],
            ['code' => 'PLM-20261111-011', 'days_ago' => 6, 'source' => 'online', 'method' => 'qris', 'status' => 'SELESAI', 'items' => [['MIE-001', 4], ['MIE-002', 4], ['MIE-005', 1]]],
            ['code' => 'PLM-20261112-012', 'days_ago' => 5, 'source' => 'walk_in', 'method' => 'cash', 'status' => 'SELESAI', 'items' => [['SNK-002', 3], ['MNM-009', 3]]],
            ['code' => 'PLM-20261113-013', 'days_ago' => 4, 'source' => 'online', 'method' => 'qris', 'status' => 'SELESAI', 'items' => [['KSH-003', 1], ['MNM-001', 3], ['SNK-006', 2]]],
            ['code' => 'PLM-20261114-014', 'days_ago' => 3, 'source' => 'walk_in', 'method' => 'cash', 'status' => 'SELESAI', 'items' => [['SNK-009', 2], ['MNM-004', 4]]],
            ['code' => 'PLM-20261115-015', 'days_ago' => 2, 'source' => 'online', 'method' => 'qris', 'status' => 'SELESAI', 'items' => [['MIE-004', 3], ['MNM-008', 2], ['KSH-005', 2]]],
            ['code' => 'PLM-20261116-016', 'days_ago' => 1, 'source' => 'walk_in', 'method' => 'cash', 'status' => 'SELESAI', 'items' => [['ATK-001', 2], ['SNK-001', 2], ['MNM-005', 1]]],
            ['code' => 'PLM-20261117-017', 'days_ago' => 0, 'source' => 'online', 'method' => 'qris', 'status' => 'SELESAI', 'items' => [['SNK-005', 1], ['MNM-006', 1], ['SNK-007', 1]]],
            ['code' => 'PLM-20261117-018', 'days_ago' => 0, 'source' => 'online', 'method' => 'qris', 'status' => 'MENUNGGU_PEMBAYARAN', 'items' => [['MNM-003', 2], ['SNK-008', 4]]],
        ];

        $codeColumn = Schema::hasColumn('orders', 'order_code') ? 'order_code' : (Schema::hasColumn('orders', 'invoice_number') ? 'invoice_number' : null);

        foreach ($sampleOrders as $idx => $ord) {
            if ($codeColumn && DB::table('orders')->where($codeColumn, $ord['code'])->exists()) {
                continue;
            }

            $total = 0;
            $preparedItems = [];
            foreach ($ord['items'] as [$sku, $qty]) {
                if (!isset($productRecords[$sku])) {
                    continue;
                }
                $prod = $productRecords[$sku];
                $subtotal = $prod->price * $qty;
                $total += $subtotal;
                $preparedItems[] = [
                    'product_id' => $prod->id,
                    'product_name' => $prod->name,
                    'qty' => $qty,
                    'quantity' => $qty,
                    'unit_price' => $prod->price,
                    'subtotal' => $subtotal,
                ];
            }

            $memberId = $memberIds[$idx % count($memberIds)];
            $memberName = $usersData[($idx % count($memberIds)) + 1]['name'];
            $orderTime = $now->copy()->subDays($ord['days_ago'])->subHours($idx % 6);

            $orderId = DB::table('orders')->insertGetId($this->filterCols('orders', [
                'order_code' => $ord['code'],
                'invoice_number' => $ord['code'],
                'user_id' => $memberId,
                'customer_name' => $memberName,
                'total_amount' => $total,
                'total_price' => $total,
                'status' => $ord['status'],
                'source' => $ord['source'],
                'payment_method' => $ord['method'],
                'payment_status' => $ord['status'] === 'SELESAI' ? 'paid' : 'unpaid',
                'paid_at' => $ord['status'] === 'SELESAI' ? $orderTime : null,
                'ordered_at' => $orderTime,
                'created_at' => $orderTime,
                'updated_at' => $orderTime,
            ]));

            foreach ($preparedItems as $item) {
                DB::table('order_items')->insert($this->filterCols('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'qty' => $item['qty'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'created_at' => $orderTime,
                    'updated_at' => $orderTime,
                ]));
            }
        }
    }
}
