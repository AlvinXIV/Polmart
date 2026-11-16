
<x-app-layout>

    <style>
        .dashboard {
            display: flex;
            flex-direction: column;
            gap: 27px;
        }

        /* WELCOME BANNER */
        .welcome-banner {
            background: linear-gradient(115deg, #1764df, #438cf5);
            border-radius: 20px;
            padding: 30px 35px;
            color: white;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 185px;
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            width: 230px;
            height: 230px;
            border-radius: 50%;
            background: rgba(255,255,255,.09);
            right: 100px;
            top: -100px;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            right: -30px;
            bottom: -90px;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-tag {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            background: rgba(255,255,255,.18);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 13px;
        }

        .welcome-content h1 {
            font-size: 27px;
            font-weight: 800;
            margin: 0 0 10px;
            letter-spacing: -.8px;
        }

        .welcome-content p {
            color: #e0edff;
            font-size: 13px;
            margin: 0 0 20px;
            line-height: 1.7;
        }

        .welcome-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 17px;
            background: white;
            color: #2563eb;
            text-decoration: none;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 700;
            transition: .2s;
        }

        .welcome-button:hover {
            background: #eff6ff;
            transform: translateY(-2px);
        }

        .banner-illustration {
            position: relative;
            z-index: 2;
            font-size: 95px;
            margin-right: 35px;
            transform: rotate(-8deg);
        }

        /* SECTION TITLE */
        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            gap: 10px;
        }

        .section-heading h3 {
            font-size: 16px;
            font-weight: 800;
            color: #172033;
            margin: 0;
        }

        .section-heading p {
            color: #94a3b8;
            font-size: 11px;
            margin: 5px 0 0;
        }

        .see-all {
            color: #2563eb;
            text-decoration: none;
            font-size: 11px;
            font-weight: 700;
        }

        /* QUICK MENU */
        .quick-menu {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .quick-card {
            background: white;
            border: 1px solid #edf0f5;
            border-radius: 15px;
            padding: 20px 15px;
            text-decoration: none;
            transition: .25s;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .quick-card:hover {
            border-color: #bfdbfe;
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(37,99,235,.08);
        }

        .quick-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            font-size: 21px;
            margin-bottom: 14px;
        }

        .icon-blue { background: #eaf2ff; }
        .icon-green { background: #e8f9f0; }
        .icon-orange { background: #fff3e6; }
        .icon-purple { background: #f3eefe; }

        .quick-card h4 {
            font-size: 12px;
            font-weight: 700;
            color: #172033;
            margin: 0 0 5px;
        }

        .quick-card p {
            font-size: 10px;
            color: #94a3b8;
            margin: 0;
        }

        /* STATISTICS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .stat-card {
            background: white;
            border: 1px solid #edf0f5;
            border-radius: 15px;
            padding: 19px;
            transition: .2s;
        }

        .stat-card:hover {
            box-shadow: 0 8px 20px rgba(15,23,42,.05);
            transform: translateY(-2px);
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 17px;
        }

        .stat-icon {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            font-size: 18px;
        }

        .stat-label {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
        }

        .stat-value {
            font-size: 25px;
            font-weight: 800;
            color: #172033;
            letter-spacing: -.7px;
            margin-bottom: 7px;
        }

        .stat-description {
            font-size: 10px;
            color: #94a3b8;
        }

        /* CONTENT GRID */
        .content-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 18px;
        }

        .content-card {
            background: white;
            border: 1px solid #edf0f5;
            border-radius: 15px;
            padding: 21px;
            min-width: 0;
        }

        /* TABLE */
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .product-table th {
            text-align: left;
            font-size: 10px;
            color: #94a3b8;
            font-weight: 600;
            padding: 12px 9px;
            background: #f8fafc;
        }

        .product-table td {
            font-size: 11px;
            padding: 14px 9px;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
        }

        .product-name {
            font-weight: 700;
            color: #172033 !important;
        }

        .stock-badge {
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 700;
            display: inline-block;
        }

        .stock-safe {
            background: #e8f9f0;
            color: #16a34a;
        }

        .stock-warning {
            background: #fff4e5;
            color: #d97706;
        }

        .stock-danger {
            background: #feecec;
            color: #dc2626;
        }

        /* ACTIVITY */
        .activity-list {
            display: flex;
            flex-direction: column;
            margin-top: 15px;
        }

        .activity-item {
            display: flex;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .activity-icon {
            width: 37px;
            height: 37px;
            border-radius: 11px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .activity-info {
            flex: 1;
        }

        .activity-info h4 {
            font-size: 11px;
            color: #172033;
            font-weight: 700;
            margin: 2px 0 5px;
        }

        .activity-info p {
            font-size: 10px;
            color: #94a3b8;
            margin: 0;
        }

        .activity-time {
            font-size: 9px;
            color: #94a3b8;
            white-space: nowrap;
        }

        /* RESPONSIVE */
        @media(max-width: 1100px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width: 650px) {
            .welcome-banner {
                padding: 25px 20px;
                min-height: 170px;
            }

            .welcome-content h1 {
                font-size: 21px;
            }

            .banner-illustration {
                font-size: 55px;
                margin-right: 0;
            }

            .quick-menu {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .stats-grid {
                gap: 10px;
            }

            .stat-card {
                padding: 14px;
            }

            .stat-value {
                font-size: 21px;
            }

            .content-card {
                padding: 14px;
                overflow-x: auto;
            }

            .product-table {
                min-width: 420px;
            }
        }

        @media(max-width: 380px) {
            .banner-illustration {
                display: none;
            }
        }
    </style>

    <div class="dashboard">

        <!-- WELCOME BANNER -->
        <section class="welcome-banner">

            <div class="welcome-content">

                <div class="welcome-tag">
                    ✦ POLMART ADMIN PANEL
                </div>

                <h1>Halo, {{ Auth::user()->name ?? 'Pengurus' }}! 👋</h1>

                <p>
                    Selamat datang kembali di Polmart.<br>
                    Kelola inventaris dan aktivitas koperasi<br>
                    dengan lebih mudah dalam satu tempat.
                </p>

                <a href="#" class="welcome-button">
                    Jelajahi Inventaris <span>→</span>
                </a>

            </div>

            <div class="banner-illustration">
                🛍️
            </div>

        </section>


        <!-- QUICK MENU -->
        <section>

            <div class="section-heading">
                <div>
                    <h3>Akses Cepat</h3>
                    <p>Pilih fitur yang ingin kamu kelola</p>
                </div>
            </div>

            <div class="quick-menu">

                <a href="#" class="quick-card">
                    <div class="quick-icon icon-blue">📦</div>
                    <h4>Inventaris Barang</h4>
                    <p>Kelola stok dan produk</p>
                </a>

                <a href="#" class="quick-card">
                    <div class="quick-icon icon-green">🛒</div>
                    <h4>Transaksi Walk-in</h4>
                    <p>Input pesanan langsung</p>
                </a>

                <a href="#" class="quick-card">
                    <div class="quick-icon icon-orange">🔍</div>
                    <h4>Validasi Loket</h4>
                    <p>Periksa kode pengambilan</p>
                </a>

                <a href="#" class="quick-card">
                    <div class="quick-icon icon-purple">📊</div>
                    <h4>Impor Excel</h4>
                    <p>Kelola data transaksi lama</p>
                </a>

            </div>

        </section>


        <!-- STATISTICS -->
        <section>

            <div class="section-heading">
                <div>
                    <h3>Ringkasan Inventaris</h3>
                    <p>Pantau kondisi koperasi hari ini</p>
                </div>

                <span style="font-size:10px;color:#94a3b8;">
                    Overview
                </span>
            </div>

            <div class="stats-grid">

                <div class="stat-card">

                    <div class="stat-top">
                        <span class="stat-label">Total Barang</span>
                        <div class="stat-icon icon-blue">📦</div>
                    </div>

                    <div class="stat-value">1.240</div>

                    <div class="stat-description">
                        Seluruh produk terdaftar
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-top">
                        <span class="stat-label">Barang Aktif</span>
                        <div class="stat-icon icon-green">✓</div>
                    </div>

                    <div class="stat-value">1.180</div>

                    <div class="stat-description">
                        Produk tersedia di katalog
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-top">
                        <span class="stat-label">Stok Menipis</span>
                        <div class="stat-icon icon-orange">⚠</div>
                    </div>

                    <div class="stat-value">24</div>

                    <div class="stat-description">
                        Perlu segera diperiksa
                    </div>

                </div>

                <div class="stat-card">

                    <div class="stat-top">
                        <span class="stat-label">Kategori</span>
                        <div class="stat-icon icon-purple">▦</div>
                    </div>

                    <div class="stat-value">12</div>

                    <div class="stat-description">
                        Kategori barang terdaftar
                    </div>

                </div>

            </div>

        </section>


        <!-- PRODUCT AND ACTIVITY -->
        <section class="content-grid">

            <!-- PRODUCT TABLE -->
            <div class="content-card">

                <div class="section-heading">
                    <div>
                        <h3>Stok Barang</h3>
                        <p>Pantau ketersediaan produk</p>
                    </div>

                    <a href="#" class="see-all">Lihat Semua →</a>
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
                            <td class="product-name">Buku Tulis</td>
                            <td>ATK</td>
                            <td>120</td>
                            <td>
                                <span class="stock-badge stock-safe">
                                    Tersedia
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td class="product-name">Pulpen Hitam</td>
                            <td>ATK</td>
                            <td>8</td>
                            <td>
                                <span class="stock-badge stock-warning">
                                    Menipis
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td class="product-name">Air Mineral</td>
                            <td>Minuman</td>
                            <td>45</td>
                            <td>
                                <span class="stock-badge stock-safe">
                                    Tersedia
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td class="product-name">Penghapus</td>
                            <td>ATK</td>
                            <td>3</td>
                            <td>
                                <span class="stock-badge stock-danger">
                                    Kritis
                                </span>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>


            <!-- RECENT ACTIVITY -->
            <div class="content-card">

                <div class="section-heading">
                    <div>
                        <h3>Aktivitas Terbaru</h3>
                        <p>Ringkasan aktivitas koperasi</p>
                    </div>
                </div>

                <div class="activity-list">

                    <div class="activity-item">

                        <div class="activity-icon icon-green">🛒</div>

                        <div class="activity-info">
                            <h4>Transaksi Walk-in</h4>
                            <p>Pesanan baru berhasil dicatat</p>
                        </div>

                        <span class="activity-time">10:30</span>

                    </div>

                    <div class="activity-item">

                        <div class="activity-icon icon-blue">📦</div>

                        <div class="activity-info">
                            <h4>Stok Ditambahkan</h4>
                            <p>Penambahan stok produk</p>
                        </div>

                        <span class="activity-time">09:45</span>

                    </div>

                    <div class="activity-item">

                        <div class="activity-icon icon-purple">📊</div>

                        <div class="activity-info">
                            <h4>Data Excel</h4>
                            <p>Riwayat transaksi berhasil diimpor</p>
                        </div>

                        <span class="activity-time">09:15</span>

                    </div>

                    <div class="activity-item">

                        <div class="activity-icon icon-orange">✓</div>

                        <div class="activity-info">
                            <h4>Pesanan Selesai</h4>
                            <p>Pengambilan barang divalidasi</p>
                        </div>

                        <span class="activity-time">08:50</span>

                    </div>

                </div>

            </div>

        </section>

    </div>

</x-app-layout>
