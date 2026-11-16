<?php

namespace Database\Seeders;

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
    }
}