<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'POLMART - Koperasi Digital Modern')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
            --primary-gradient-subtle: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .auth-container {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
        }

        /* Left Hero Banner */
        .auth-hero {
            background: var(--primary-gradient);
            color: #ffffff;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .auth-hero::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            right: -100px;
            top: -100px;
            pointer-events: none;
        }

        .auth-hero::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            left: -80px;
            bottom: -60px;
            pointer-events: none;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: #ffffff;
            z-index: 10;
        }

        .auth-brand-icon {
            width: 46px;
            height: 46px;
            background: #ffffff;
            color: #2563eb;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .auth-brand-text {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .auth-hero-content {
            max-width: 480px;
            z-index: 10;
            margin: 40px 0;
        }

        .auth-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .auth-hero-title {
            font-size: 38px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 16px;
            letter-spacing: -1px;
        }

        .auth-hero-desc {
            font-size: 15px;
            color: #e0edff;
            line-height: 1.6;
        }

        .auth-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-top: 32px;
        }

        .auth-feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            padding: 16px;
        }

        .auth-feature-card h4 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .auth-feature-card p {
            font-size: 12px;
            color: #dbeafe;
            line-height: 1.4;
            margin: 0;
        }

        .auth-hero-footer {
            font-size: 12px;
            color: #bfdbfe;
            z-index: 10;
        }

        /* Right Form Area */
        .auth-form-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #ffffff;
        }

        .auth-card-inner {
            width: 100%;
            max-width: 440px;
        }

        @media (max-width: 1024px) {
            .auth-container {
                grid-template-columns: 1fr;
            }
            .auth-hero {
                padding: 40px 30px;
                min-height: auto;
            }
            .auth-hero-title {
                font-size: 28px;
            }
            .auth-features {
                grid-template-columns: 1fr;
            }
            .auth-form-wrap {
                padding: 40px 24px;
            }
        }
    </style>
</head>
<body>

<div class="auth-container">

    <!-- LEFT HERO PANEL -->
    <section class="auth-hero">
        <a href="{{ route('home') }}" class="auth-brand">
            <div class="auth-brand-icon">P</div>
            <div class="auth-brand-text">POLMART.</div>
        </a>

        <div class="auth-hero-content">
            <div class="auth-badge">
                ★ Smart Cooperative System
            </div>

            <h1 class="auth-hero-title">
                Koperasi Modern,<br>
                Belanja Praktis & Cepat.
            </h1>

            <p class="auth-hero-desc">
                Kelola inventaris barang koperasi, input transaksi loket walk-in, dan nikmati kemudahan katalog belanja online bagi seluruh anggota.
            </p>

            <div class="auth-features">
                <div class="auth-feature-card">
                    <h4>📦 Inventaris Akurat</h4>
                    <p>Pencatatan stok otomatis dan audit mutasi barang.</p>
                </div>
                <div class="auth-feature-card">
                    <h4>💳 Pembayaran QRIS</h4>
                    <p>Dukungan pembayaran digital instan dan verifikasi loket.</p>
                </div>
            </div>
        </div>

        <div class="auth-hero-footer">
            &copy; {{ date('Y') }} Polmart. Sistem Koperasi Terintegrasi.
        </div>
    </section>

    <!-- RIGHT FORM WRAPPER -->
    <main class="auth-form-wrap">
        <div class="auth-card-inner">
            {{ $slot }}
        </div>
    </main>

</div>

</body>
</html>
