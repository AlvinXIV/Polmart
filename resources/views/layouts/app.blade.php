<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Polmart - Koperasi Digital Modern')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #60a5fa;
            --primary-gradient: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
            --primary-gradient-subtle: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.05);
            --shadow-md: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.03);
            --shadow-lg: 0 10px 25px -3px rgba(15, 23, 42, 0.08), 0 4px 6px -2px rgba(15, 23, 42, 0.04);
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 18px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        button, input, select, textarea {
            font-family: inherit;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ----------------------------------------------------
           SIDEBAR STYLING
           ---------------------------------------------------- */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid var(--border-color);
            padding: 24px 16px;
            z-index: 100;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 4px 10px 24px;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            flex-shrink: 0;
        }

        .brand-name {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: 10px;
            font-weight: 600;
            color: #2563eb;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .sidebar-menu-wrapper {
            flex: 1;
            padding-top: 16px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .menu-label {
            font-size: 10.5px;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 0 12px;
            margin: 18px 0 8px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            color: #475569;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }

        .menu-link:hover {
            background: #eff6ff;
            color: #2563eb;
        }

        .menu-link.active {
            background: var(--primary-gradient);
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
        }

        .menu-icon {
            font-size: 16px;
            width: 22px;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .menu-badge {
            margin-left: auto;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 9999px;
            background: #dbeafe;
            color: #1d4ed8;
        }

        .menu-link.active .menu-badge {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }

        /* Sidebar Bottom Profile */
        .sidebar-bottom {
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 12px;
        }

        .profile-user-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
        }

        .profile-name {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .profile-role {
            font-size: 11px;
            color: #64748b;
        }

        .btn-logout-sidebar {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-logout-sidebar:hover {
            color: #ef4444;
            background: #fee2e2;
        }

        /* ----------------------------------------------------
           MAIN CONTENT AREA
           ---------------------------------------------------- */
        .main-content {
            margin-left: 260px;
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        /* Topbar */
        .topbar {
            height: 72px;
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 90;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: 1px solid var(--border-color);
            width: 38px;
            height: 38px;
            border-radius: 8px;
            cursor: pointer;
            color: #475569;
            font-size: 18px;
            align-items: center;
            justify-content: center;
        }

        .page-context {
            font-size: 13.5px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .page-context a {
            color: inherit;
            text-decoration: none;
        }

        .page-context a:hover {
            color: #2563eb;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box input {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 20px;
            padding: 8px 36px 8px 34px;
            font-size: 13px;
            color: #0f172a;
            width: 240px;
            transition: all 0.2s ease;
            outline: none;
        }

        .search-box input:focus {
            width: 290px;
            border-color: #2563eb;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .search-box-icon {
            position: absolute;
            left: 12px;
            font-size: 13px;
            color: #94a3b8;
            pointer-events: none;
        }

        .search-box-kbd {
            position: absolute;
            right: 12px;
            font-size: 10px;
            color: #94a3b8;
            background: #edf2f7;
            padding: 2px 5px;
            border-radius: 4px;
            pointer-events: none;
        }

        .portal-switch-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: var(--primary-gradient-subtle);
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .portal-switch-pill:hover {
            background: #dbeafe;
            color: #1e40af;
            transform: translateY(-1px);
        }

        /* Page Container */
        .page-container {
            padding: 32px;
            flex: 1;
        }

        .page-heading-wrapper {
            margin-bottom: 24px;
        }

        .page-heading-wrapper h1 {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
        }

        .page-heading-wrapper p {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
        }

        /* Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(2px);
            z-index: 95;
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .sidebar-overlay.show {
                display: block;
            }
            .main-content {
                margin-left: 0;
            }
            .mobile-menu-btn {
                display: inline-flex;
            }
            .topbar {
                padding: 0 20px;
            }
            .page-container {
                padding: 24px 20px;
            }
        }

        @media (max-width: 640px) {
            .search-box {
                display: none;
            }
            .portal-switch-pill span:not(.icon) {
                display: none;
            }
        }

        .polmart-qty-stepper {
            display: inline-flex;
            align-items: center;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            overflow: hidden;
            background: #ffffff;
        }
        .polmart-qty-stepper button {
            width: 30px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
        }
        .polmart-qty-stepper button:hover:not(:disabled) {
            background: #e2e8f0;
        }
        .polmart-qty-stepper button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .polmart-qty-stepper input {
            width: 38px;
            height: 34px;
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            border: none;
            padding: 0;
            background: #ffffff;
            -moz-appearance: textfield;
        }
        .polmart-qty-stepper input:focus {
            outline: none;
            box-shadow: none;
        }
        .polmart-qty-stepper input::-webkit-outer-spin-button,
        .polmart-qty-stepper input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .polmart-qty-stepper.sm button {
            width: 26px;
            height: 28px;
            font-size: 12px;
        }
        .polmart-qty-stepper.sm input {
            width: 34px;
            height: 28px;
            font-size: 12px;
        }

        .polmart-stats-horizontal {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
        }
        .polmart-stats-horizontal .polmart-stat-card {
            padding: 14px;
            gap: 8px;
        }
        .polmart-stats-horizontal .polmart-stat-icon {
            width: 38px;
            height: 38px;
            font-size: 16px;
            flex-shrink: 0;
        }
        @media (max-width: 1279px) {
            .polmart-stats-horizontal {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 767px) {
            .polmart-stats-horizontal {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 480px) {
            .polmart-stats-horizontal {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="app-wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        @php
            $currentUser = Auth::user();
            $isAdmin = $currentUser?->isAdmin() ?? false;
            $homeRoute = $isAdmin ? route('admin.dashboard') : route('anggota.dashboard');
            $cartItemCount = $isAdmin ? 0 : (int) collect(session('polmart_cart', []))->sum();
        @endphp

        <div>
            <!-- BRAND LOGO -->
            <a href="{{ $homeRoute }}" class="brand">
                <div class="brand-icon">P</div>
                <div>
                    <div class="brand-name">Polmart</div>
                    <div class="brand-subtitle">{{ $isAdmin ? 'Admin & Pengurus' : 'Portal Anggota' }}</div>
                </div>
            </a>

            <!-- NAVIGATION ITEMS -->
            <div class="sidebar-menu-wrapper">

                @if ($isAdmin)
                    <div class="menu-label">Menu Utama</div>

                    <a href="{{ route('admin.dashboard') }}"
                       class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="layout-dashboard" class="w-5 h-5" /></span>
                        <span>Dashboard Analitik</span>
                    </a>

                    <div class="menu-label">Manajemen Koperasi</div>

                    <a href="{{ route('admin.products.index') }}"
                       class="menu-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="package" class="w-5 h-5" /></span>
                        <span>Master Barang</span>
                    </a>

                    <a href="{{ route('admin.stock-mutations.index') }}"
                       class="menu-link {{ request()->routeIs('admin.stock-mutations.*') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="arrow-left-right" class="w-5 h-5" /></span>
                        <span>Log Mutasi Stok</span>
                    </a>

                    <a href="{{ route('admin.manual-orders.index') }}"
                       class="menu-link {{ request()->routeIs('admin.manual-orders.*') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="shopping-cart" class="w-5 h-5" /></span>
                        <span>Transaksi Walk-in</span>
                    </a>

                    <a href="{{ route('admin.pickup.index') }}"
                       class="menu-link {{ request()->routeIs('admin.pickup.*') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="check-circle" class="w-5 h-5" /></span>
                        <span>Validasi Loket Ambil</span>
                    </a>

                    <a href="{{ route('admin.operating-expenses.index') }}"
                       class="menu-link {{ request()->routeIs('admin.operating-expenses.*') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="banknote" class="w-5 h-5" /></span>
                        <span>Biaya Operasional</span>
                    </a>

                    <div class="menu-label">Data & Laporan</div>

                    <a href="{{ route('admin.order-imports.index') }}"
                       class="menu-link {{ request()->routeIs('admin.order-imports.*') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="file-spreadsheet" class="w-5 h-5" /></span>
                        <span>Impor Data Excel</span>
                    </a>

                    <a href="{{ route('admin.transactions.index') }}"
                       class="menu-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="receipt" class="w-5 h-5" /></span>
                        <span>Riwayat Transaksi</span>
                    </a>

                    <a href="{{ route('admin.reports.index') }}"
                       class="menu-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="bar-chart-3" class="w-5 h-5" /></span>
                        <span>Laporan & Export Excel</span>
                    </a>
                @else
                    <div class="menu-label">Portal Anggota</div>

                    <a href="{{ route('anggota.dashboard') }}"
                       class="menu-link {{ request()->routeIs('anggota.dashboard') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="layout-dashboard" class="w-5 h-5" /></span>
                        <span>Dashboard Anggota</span>
                    </a>

                    <a href="{{ route('anggota.katalog') }}"
                       class="menu-link {{ request()->routeIs('anggota.katalog') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="shopping-bag" class="w-5 h-5" /></span>
                        <span>Katalog Belanja</span>
                    </a>

                    <a href="{{ route('anggota.keranjang') }}"
                       class="menu-link {{ request()->routeIs('anggota.keranjang*') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="shopping-basket" class="w-5 h-5" /></span>
                        <span>Keranjang Belanja</span>
                        @if ($cartItemCount > 0)
                            <span class="menu-badge">{{ $cartItemCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('anggota.pesanan') }}"
                       class="menu-link {{ request()->routeIs('anggota.pesanan*', 'anggota.checkout*', 'anggota.pembayaran*') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="clipboard-list" class="w-5 h-5" /></span>
                        <span>Pesanan Saya</span>
                    </a>

                    <div class="menu-label">Akun</div>

                    <a href="{{ route('profile.edit') }}"
                       class="menu-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <span class="menu-icon"><x-icon name="user" class="w-5 h-5" /></span>
                        <span>Profil Akun</span>
                    </a>
                @endif

            </div>
        </div>

        <!-- SIDEBAR USER & LOGOUT -->
        <div class="sidebar-bottom">
            <div class="admin-profile">
                <a href="{{ route('profile.edit') }}" class="profile-user-wrap" style="text-decoration: none;">
                    <div class="avatar">
                        {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
                    </div>
                    <div style="min-width: 0;">
                        <div class="profile-name">
                            {{ $currentUser->name ?? 'Pengguna' }}
                        </div>
                        <div class="profile-role">
                            {{ $isAdmin ? 'Admin / Pengurus' : ('Anggota â€¢ ' . ($currentUser->member_code ?? '-')) }}
                        </div>
                    </div>
                </a>

                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout-sidebar" title="Keluar / Logout" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                        <x-icon name="power" class="w-4 h-4" />
                    </button>
                </form>
            </div>
        </div>

    </aside>

    <!-- Mobile Drawer Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- MAIN WRAPPER -->
    <div class="main-content">

        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="mobile-menu-btn" onclick="toggleSidebar()" aria-label="Toggle Menu">
                    <x-icon name="menu" class="w-5 h-5" />
                </button>

                <div class="page-context">
                    <a href="{{ $homeRoute }}">Polmart</a>
                    <span>/</span>
                    <strong style="color: #1e293b;">@yield('page_title', $isAdmin ? 'Panel Pengurus' : 'Portal Anggota')</strong>
                </div>
            </div>

            @if (!$isAdmin)
                <div class="topbar-right">
                    <a href="{{ route('anggota.keranjang') }}" class="portal-switch-pill">
                        <span class="icon flex items-center"><x-icon name="shopping-basket" class="w-4 h-4" /></span>
                        <span>Keranjang ({{ $cartItemCount }})</span>
                    </a>

                    <!-- User Avatar Pill -->
                    <a href="{{ route('profile.edit') }}" title="Profil Saya" style="display: flex; align-items: center; gap: 8px; text-decoration: none;">
                        <div class="avatar" style="width: 34px; height: 34px; font-size: 13px;">
                            {{ strtoupper(substr($currentUser->name ?? 'U', 0, 1)) }}
                        </div>
                    </a>
                </div>
            @endif
        </header>

        <!-- PAGE CONTAINER -->
        <main class="page-container">
            @isset($header)
                <div class="page-heading-wrapper">
                    {{ $header }}
                </div>
            @endisset

            {{ $slot }}
        </main>

    </div>

</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }

    function handleGlobalSearch(e) {
        if (e.key === 'Enter') {
            const query = e.target.value.trim();
            if (query) {
                const searchBaseUrl = @json($isAdmin ? route('admin.products.index') : route('anggota.katalog'));
                window.location.href = searchBaseUrl + "?search=" + encodeURIComponent(query);
            }
        }
    }

    // Keyboard shortcut Ctrl+K or Cmd+K to focus search
    document.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.getElementById('globalSearchInput');
            if (searchInput) searchInput.focus();
        }
    });
</script>

</body>
</html>
