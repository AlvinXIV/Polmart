POLMART - PRODUCT IMAGE SEEDER

Isi:
- ProductImageSeeder.php: seeder dengan mapping gambar langsung per SKU (35 SKU dari SupermarketSeeder).

Cara pakai di project WSL:
1. Salin ProductImageSeeder.php ke database/seeders/ProductImageSeeder.php
2. Pastikan PHP cURL aktif di container aplikasi.
3. Pastikan kolom gambar products adalah salah satu dari: image, image_path, photo, gambar.
4. Jalankan:
   docker compose exec app php artisan storage:link
   docker compose exec app php artisan db:seed --class=ProductImageSeeder

Catatan:
- Seeder hanya mengubah gambar untuk SKU yang memiliki mapping.
- Jika URL gagal diunduh, nilai image lama di database tidak ditimpa.
- Gambar disimpan lokal ke storage/app/public/products dan database diisi dengan products/nama-file.ext.
- Pastikan tampilan Blade/frontend menggunakan Storage::url($product->image) atau asset('storage/'.$product->image), sesuai nama kolom yang dipakai.
- URL gambar berasal dari halaman produk/penjual yang dicantumkan pada source_page di dalam seeder. Untuk penggunaan produksi/komersial, periksa ketentuan penggunaan gambar dan minta izin atau gunakan aset resmi yang berlisensi.
- Image URL dari penjual dapat berubah sewaktu-waktu atau membatasi hotlink/download; jika ada yang gagal, seeder akan melewatinya dan menampilkan warning.
