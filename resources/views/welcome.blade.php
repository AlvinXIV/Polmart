<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>POLMART - Sistem Koperasi Digital Modern</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-gradient: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
            --primary-gradient-subtle: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #0f172a;
            line-height: 1.6;
        }

        /* Navbar */
        .landing-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .landing-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #0f172a;
        }

        .landing-brand-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            color: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
        }

        .landing-brand-text {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .landing-nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .landing-nav-link {
            text-decoration: none;
            color: #475569;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .landing-nav-link:hover {
            color: #2563eb;
        }

        /* Hero */
        .landing-hero {
            padding: 90px 24px 80px;
            text-align: center;
            background: radial-gradient(circle at 50% 10%, #eff6ff 0%, #f8fafc 70%);
            position: relative;
            overflow: hidden;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 30px;
            color: #1d4ed8;
            font-size: 12.5px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .hero-title {
            font-size: 52px;
            font-weight: 900;
            line-height: 1.15;
            letter-spacing: -1.5px;
            color: #0f172a;
            max-width: 840px;
            margin: 0 auto 20px;
        }

        .hero-title span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-desc {
            font-size: 18px;
            color: #64748b;
            max-width: 660px;
            margin: 0 auto 36px;
            line-height: 1.6;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-gradient);
            color: #ffffff;
            font-weight: 700;
            font-size: 15px;
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
            transition: all 0.2s;
        }

        .btn-hero-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.4);
            color: #ffffff;
        }

        .btn-hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            color: #334155;
            font-weight: 700;
            font-size: 15px;
            padding: 14px 28px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
            transition: all 0.2s;
        }

        .btn-hero-secondary:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            transform: translateY(-2px);
        }

        /* Features Section */
        .features-section {
            max-width: 1140px;
            margin: 0 auto;
            padding: 40px 24px 80px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .section-header p {
            font-size: 15px;
            color: #64748b;
            margin-top: 8px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .feature-card {
            background: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px -4px rgba(15, 23, 42, 0.09);
            border-color: #bfdbfe;
        }

        .feature-icon-box {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .feature-card p {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.6;
        }

        /* Banner CTA */
        .landing-cta-banner {
            max-width: 1140px;
            margin: 0 auto 80px;
            padding: 0 24px;
        }

        .cta-box {
            background: var(--primary-gradient);
            border-radius: 24px;
            padding: 50px 60px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.4);
        }

        .cta-box h3 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .cta-box p {
            font-size: 15px;
            color: #dbeafe;
            max-width: 500px;
        }

        /* Footer */
        .landing-footer {
            border-top: 1px solid #e2e8f0;
            padding: 30px 40px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
            background: #ffffff;
        }

        @media (max-width: 900px) {
            .hero-title {
                font-size: 36px;
            }
            .features-grid {
                grid-template-columns: 1fr;
            }
            .cta-box {
                flex-direction: column;
                text-align: center;
                gap: 24px;
                padding: 40px 24px;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <header class="landing-nav">
        <a href="{{ route('home') }}" class="landing-brand">
            <div class="landing-brand-icon">P</div>
            <div class="landing-brand-text">Polmart.</div>
        </a>

        <div class="landing-nav-links">
            @if (Route::has('login'))
                @auth
                    <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('anggota.dashboard') }}" class="btn-hero-primary" style="padding: 9px 18px; font-size: 13px;">
                        Dashboard Saya →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="landing-nav-link" style="color: #2563eb;">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-hero-primary" style="padding: 9px 18px; font-size: 13px;">
                        Daftar Anggota
                    </a>
                @endauth
            @endif
        </div>
    </header>

    <!-- HERO -->
    <section class="landing-hero">
        <h1 class="hero-title">
            Belanja Kebutuhan Lebih Mudah di <span>Polmart</span>
        </h1>

        <p class="hero-desc">
            Belanja mudah dan praktis di Polman Bandung! Cek ketersediaan stok barang koperasi secara real-time, pesan kebutuhan kuliah dan harian langsung dari perangkat Anda, bayar cepat lewat QRIS dinamis, lalu ambil pesanan di loket Polmart cukup dengan kode 4-digit tanpa antre.
        </p>

        <div class="hero-actions">
            @auth
                <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('anggota.dashboard') }}" class="btn-hero-primary">
                    Buka Dashboard Saya →
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-hero-primary">
                    Masuk Akun
                </a>
                <a href="{{ route('register') }}" class="btn-hero-secondary">
                    Daftar Anggota Baru
                </a>
            @endauth
        </div>
    </section>

    <!-- FEATURES -->
    <section class="features-section">
        <div class="section-header">
            <h2>Keunggulan Belanja di Polmart</h2>
            <p>Kemudahan bertransaksi bagi seluruh anggota koperasi secara cepat dan transparan</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon-box">📦</div>
                <h3>Stok Real-Time</h3>
                <p>Lihat ketersediaan barang dan harga satuan terkini langsung dari inventaris koperasi sebelum memesan.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-box">🛍️</div>
                <h3>Katalog & Keranjang Mandiri</h3>
                <p>Pilih barang kebutuhan Anda, atur kuantitas di keranjang belanja, dan lakukan checkout kapan saja.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-box">📱</div>
                <h3>Pembayaran QRIS Dinamis</h3>
                <p>Setiap pesanan dilengkapi kode QR pembayaran unik dengan validasi status otomatis dalam hitungan detik.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-box">🔢</div>
                <h3>Kode Pengambilan 4-Digit</h3>
                <p>Setelah pembayaran terkonfirmasi, dapatkan kode ambil unik 4-digit untuk penukaran cepat di loket Polmart.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-box">🧾</div>
                <h3>Riwayat & Struk Digital</h3>
                <p>Pantau seluruh status pesanan aktif hingga selesai beserta bukti transaksi digital di portal anggota.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon-box">⏱️</div>
                <h3>Reservasi Stok Otomatis</h3>
                <p>Stok langsung diamankan selama 15 menit saat Anda checkout agar tidak kehabisan barang sebelum pembayaran selesai.</p>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="landing-footer">
        &copy; {{ date('Y') }} Polmart - Smart Cooperative System. Hak Cipta Dilindungi.
    </footer>

</body>
</html>
