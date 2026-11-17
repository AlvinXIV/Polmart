<x-app-layout>
    @section('title', 'Dashboard - Polmart')
    @section('page_title', 'Dashboard')

    <style>
        .dashboard {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        /* WELCOME BANNER */
        .welcome-banner {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
            border-radius: 20px;
            padding: 34px 38px;
            color: white;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 185px;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.35);
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(255,255,255,.09);
            right: 120px;
            top: -100px;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255,255,255,.07);
            right: -40px;
            bottom: -90px;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-tag {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            background: rgba(255,255,255,.18);
            backdrop-filter: blur(4px);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 14px;
            text-transform: uppercase;
        }

        .welcome-content h1 {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 10px;
            letter-spacing: -.6px;
        }

        .welcome-content p {
            color: #e0edff;
            font-size: 13.5px;
            margin: 0 0 22px;
            line-height: 1.6;
            max-width: 520px;
        }

        .welcome-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: white;
            color: #2563eb;
            text-decoration: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            transition: all .2s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .welcome-button:hover {
            background: #eff6ff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }

        .banner-illustration {
            position: relative;
            z-index: 2;
            font-size: 88px;
            margin-right: 30px;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.15));
        }

        /* SECTION TITLE */
        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            gap: 10px;
        }

        .section-heading h3 {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .section-heading p {
            color: #64748b;
            font-size: 12px;
            margin: 4px 0 0;
        }

        .see-all {
            color: #2563eb;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: gap 0.2s;
        }

        .see-all:hover {
            gap: 8px;
            color: #1d4ed8;
        }

        /* QUICK MENU */
        .quick-menu {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .quick-card {
            background: white;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            padding: 22px 18px;
            text-decoration: none;
            transition: all .25s ease;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        .quick-card:hover {
            transform: translateY(-3px);
            border-color: #bfdbfe;
            box-shadow: 0 10px 25px -3px rgba(37, 99, 235, 0.1);
        }

        .quick-icon {
            width: 48px;
            height: 48px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 14px;
        }

        .icon-blue { background: #eff6ff; color: #2563eb; }
        .icon-green { background: #f0fdf4; color: #16a34a; }
        .icon-orange { background: #fff7ed; color: #ea580c; }
        .icon-purple { background: #faf5ff; color: #9333ea; }

        .quick-card h4 {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px;
        }

        .quick-card p {
            font-size: 12px;
            color: #64748b;
            margin: 0;
            line-height: 1.5;
        }

        /* STATISTICS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .stat-card {
            background: white;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s;
        }

        .stat-card:hover {
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }

        .stat-description {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 8px;
        }

        /* CONTENT GRID */
        .content-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 20px;
        }

        .content-card {
            background: white;
            border: 1px solid #edf2f7;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        /* TABLE */
        .product-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 8px;
        }

        .product-table thead th {
            text-align: left;
            padding: 12px 14px;
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            font-size: 11px;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #edf2f7;
        }

        .product-table thead th:first-child { border-radius: 8px 0 0 8px; }
        .product-table thead th:last-child { border-radius: 0 8px 8px 0; }

        .product-table tbody td {
            padding: 13px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .product-table tbody tr:last-child td {
            border-bottom: none;
        }

        .product-table tbody tr:hover {
            background: #f8faff;
        }

        .product-name {
            font-weight: 700;
            color: #0f172a;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .stock-safe { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .stock-warning { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .stock-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        /* ACTIVITY */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-top: 8px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            transition: all 0.2s;
        }

        .activity-item:hover {
            background: #f0f7ff;
            border-color: #dbeafe;
        }

        .activity-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .activity-info {
            flex: 1;
        }

        .activity-info h4 {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .activity-info p {
            font-size: 11.5px;
            color: #64748b;
            margin: 2px 0 0;
        }

        .activity-time {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
        }

        @media (max-width: 1024px) {
            .quick-menu { grid-template-columns: repeat(2, 1fr); }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .content-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .welcome-banner { flex-direction: column; text-align: center; padding: 24px; gap: 20px; }
            .banner-illustration { margin-right: 0; font-size: 60px; }
            .quick-menu { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="dashboard">

        <!-- WELCOME BANNER -->
        <section class="welcome-banner">
            <div class="welcome-content">
                <div class="welcome-tag">
                    ✦ POLMART MANAGEMENT PANEL
                </div>

                <h1>Halo, {{ Auth::user()->name ?? 'Pengurus Polmart' }}! 👋</h1>

                <p>
                    Selamat datang di sistem manajemen koperasi terintegrasi.<br>
                    Kelola stok barang, transaksi loket, validasi pesanan, dan laporan keuangan dalam satu dashboard.
                </p>

                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="{{ route('inventaris') }}" class="welcome-button">
                        📦 Kelola Inventaris <span>→</span>
                    </a>
                    <a href="{{ route('transaksi.walkin') }}" class="welcome-button" style="background: rgba(255,255,255,0.18); color: #ffffff; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(4px);">
                        🛒 Buka Kasir Loket
                    </a>
                </div>
            </div>

            <div class="banner-illustration">
                🛍️
            </div>
        </section>

        <!-- QUICK ACCESS MENU -->
        <section>
            <div class="section-heading">
                <div>
                    <h3>Akses Cepat Fitur Utama</h3>
                    <p>Pilih modul operasional yang ingin Anda kelola</p>
                </div>
            </div>

            <div class="quick-menu">
                <a href="{{ route('inventaris') }}" class="quick-card">
                    <div class="quick-icon icon-blue">📦</div>
                    <h4>Master Inventaris</h4>
                    <p>Kelola stok, harga, dan SKU produk</p>
                </a>

                <a href="{{ route('transaksi.walkin') }}" class="quick-card">
                    <div class="quick-icon icon-green">🛒</div>
                    <h4>Transaksi Walk-in</h4>
                    <p>Input penjualan langsung di kasir loket</p>
                </a>

                <a href="{{ route('validasi.loket') }}" class="quick-card">
                    <div class="quick-icon icon-orange">🔍</div>
                    <h4>Validasi Loket</h4>
                    <p>Verifikasi kode pengambilan barang</p>
                </a>

                <a href="{{ route('impor.excel') }}" class="quick-card">
                    <div class="quick-icon icon-purple">📊</div>
                    <h4>Impor Data Excel</h4>
                    <p>Unggah massal & riwayat transaksi lama</p>
                </a>
            </div>
        </section>

        <!-- SUMMARY STATISTICS -->
        <section>
            <div class="section-heading">
                <div>
                    <h3>Ringkasan Operasional</h3>
                    <p>Status inventaris dan transaksi koperasi terkini</p>
                </div>

                <a href="{{ route('laporan') }}" class="see-all">
                    Lihat Laporan Lengkap →
                </a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-top">
                        <span class="stat-label">Total Barang</span>
                        <div class="stat-icon icon-blue">📦</div>
                    </div>
                    <div class="stat-value">1.240</div>
                    <div class="stat-description">Produk terdaftar di gudang</div>
                </div>

                <div class="stat-card">
                    <div class="stat-top">
                        <span class="stat-label">Barang Aktif</span>
                        <div class="stat-icon icon-green">✓</div>
                    </div>
                    <div class="stat-value">1.180</div>
                    <div class="stat-description">Tersedia di katalog belanja</div>
                </div>

                <div class="stat-card">
                    <div class="stat-top">
                        <span class="stat-label">Stok Menipis</span>
                        <div class="stat-icon icon-orange">⚠</div>
                    </div>
                    <div class="stat-value">24</div>
                    <div class="stat-description">Perlu restock segera</div>
                </div>

                <div class="stat-card">
                    <div class="stat-top">
                        <span class="stat-label">Kategori Produk</span>
                        <div class="stat-icon icon-purple">▦</div>
                    </div>
                    <div class="stat-value">12</div>
                    <div class="stat-description">Kategori barang aktif</div>
                </div>
            </div>
        </section>

        <!-- PRODUCT AND ACTIVITY GRID -->
        <section class="content-grid">

            <!-- PRODUCT MONITOR -->
            <div class="content-card">
                <div class="section-heading">
                    <div>
                        <h3>Monitoring Stok Kritis</h3>
                        <p>Daftar barang dengan kuantitas yang perlu dipantau</p>
                    </div>

                    <a href="{{ route('inventaris') }}" class="see-all">
                        Semua Barang →
                    </a>
                </div>

                <table class="product-table">
                    <thead>
                        <tr>
                            <th>PRODUK</th>
                            <th>KATEGORI</th>
                            <th>STOK</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="product-name">Buku Tulis Sinar Dunia 58</td>
                            <td>Alat Tulis</td>
                            <td>120</td>
                            <td><span class="stock-badge stock-safe">Tersedia</span></td>
                        </tr>
                        <tr>
                            <td class="product-name">Pulpen Gel Faster Hitam</td>
                            <td>Alat Tulis</td>
                            <td>8</td>
                            <td><span class="stock-badge stock-warning">Menipis</span></td>
                        </tr>
                        <tr>
                            <td class="product-name">Air Mineral Le Minerale 600ml</td>
                            <td>Minuman</td>
                            <td>45</td>
                            <td><span class="stock-badge stock-safe">Tersedia</span></td>
                        </tr>
                        <tr>
                            <td class="product-name">Penghapus Joyko B40</td>
                            <td>Alat Tulis</td>
                            <td>3</td>
                            <td><span class="stock-badge stock-danger">Kritis</span></td>
                        </tr>
                        <tr>
                            <td class="product-name">Kertas HVS A4 PaperOne 75gr</td>
                            <td>Alat Tulis</td>
                            <td>14</td>
                            <td><span class="stock-badge stock-warning">Menipis</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- RECENT ACTIVITIES -->
            <div class="content-card">
                <div class="section-heading">
                    <div>
                        <h3>Aktivitas Terkini</h3>
                        <p>Catatan transaksi & perubahan sistem terbaru</p>
                    </div>

                    <a href="{{ route('riwayat.transaksi') }}" class="see-all">
                        Semua →
                    </a>
                </div>

                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon icon-green">🛒</div>
                        <div class="activity-info">
                            <h4>Transaksi Kasir Walk-in</h4>
                            <p>Pesanan #TRX-20261117-001 sukses dicatat</p>
                        </div>
                        <span class="activity-time">Baru saja</span>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon icon-blue">📦</div>
                        <div class="activity-info">
                            <h4>Restock Barang Masuk</h4>
                            <p>Penambahan 50 pcs Buku Tulis</p>
                        </div>
                        <span class="activity-time">10:30</span>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon icon-purple">📊</div>
                        <div class="activity-info">
                            <h4>Impor Data Transaksi</h4>
                            <p>54 baris riwayat Excel berhasil divalidasi</p>
                        </div>
                        <span class="activity-time">09:15</span>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon icon-orange">✓</div>
                        <div class="activity-info">
                            <h4>Pengambilan Loket Selesai</h4>
                            <p>Pesanan QR-9912 telah diserahkan ke anggota</p>
                        </div>
                        <span class="activity-time">08:45</span>
                    </div>
                </div>
            </div>

        </section>

    </div>
</x-app-layout>
