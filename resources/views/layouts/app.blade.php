
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Polmart - Admin Panel</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f7f9fc;
            color: #172033;
        }

        button, input {
            font-family: inherit;
        }

        .app-wrapper {
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 255px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #edf0f5;
            padding: 27px 18px;
            z-index: 100;
            transition: transform .3s ease;
            overflow-y: auto;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px;
            margin-bottom: 42px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 13px;
            background: #2563eb;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 800;
        }

        .brand-name {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -1px;
            color: #172554;
        }

        .brand-subtitle {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 3px;
            letter-spacing: 1px;
        }

        .menu-label {
            font-size: 10px;
            font-weight: 700;
            color: #a0a9b8;
            letter-spacing: 1.2px;
            padding: 0 12px;
            margin: 25px 0 12px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 12px 13px;
            margin-bottom: 5px;
            border-radius: 10px;
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: .2s;
        }

        .menu-link:hover {
            background: #eff6ff;
            color: #2563eb;
        }

        .menu-link.active {
            background: #eaf2ff;
            color: #2563eb;
            font-weight: 700;
        }

        .menu-icon {
            width: 19px;
            height: 19px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
        }

        .sidebar-bottom {
            margin-top: 45px;
            padding: 15px 12px;
            border-top: 1px solid #edf0f5;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #dbeafe;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .profile-name {
            font-size: 12px;
            font-weight: 700;
        }

        .profile-role {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* MAIN CONTENT */

        .main-content {
            margin-left: 255px;
            min-height: 100vh;
        }

        /* TOPBAR */

        .topbar {
            height: 82px;
            background: #ffffff;
            border-bottom: 1px solid #edf0f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 38px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-context {
            font-size: 13px;
            color: #94a3b8;
        }

        .search-box {
            width: 300px;
            height: 41px;
            background: #f7f9fc;
            border: 1px solid #edf0f5;
            border-radius: 10px;
            display: flex;
            align-items: center;
            padding: 0 13px;
            gap: 9px;
        }

        .search-box input {
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            font-size: 12px;
            color: #334155;
        }

        .search-box input::placeholder {
            color: #a0a9b8;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification {
            width: 38px;
            height: 38px;
            border: 1px solid #edf0f5;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            cursor: pointer;
        }

        .topbar-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-profile-name {
            font-size: 12px;
            font-weight: 700;
        }

        .topbar-profile-role {
            color: #94a3b8;
            font-size: 10px;
            margin-top: 3px;
        }

        /* PAGE CONTENT */

        .page-container {
            padding: 35px 38px;
            max-width: 1600px;
            margin: auto;
        }

        .page-heading {
            margin-bottom: 25px;
        }

        .page-heading h1 {
            font-size: 25px;
            font-weight: 800;
            color: #172033;
            letter-spacing: -.8px;
            margin: 0 0 8px;
        }

        .page-heading p {
            font-size: 13px;
            color: #94a3b8;
            margin: 0;
        }

        /* MOBILE */

        .mobile-menu {
            display: none;
            width: 38px;
            height: 38px;
            border: none;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 9px;
            font-size: 20px;
            cursor: pointer;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .35);
            z-index: 90;
        }

        @media (max-width: 900px) {
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

            .mobile-menu {
                display: block;
            }

            .topbar {
                padding: 0 20px;
            }

            .page-container {
                padding: 25px 20px;
            }
        }

        @media (max-width: 600px) {
            .search-box {
                display: none;
            }

            .topbar {
                height: 70px;
                padding: 0 15px;
            }

            .topbar-profile > div:last-child {
                display: none;
            }

            .page-container {
                padding: 23px 15px;
            }

            .page-heading h1 {
                font-size: 21px;
            }
        }
    </style>
</head>

<body>

<div class="app-wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">

        <div class="brand">
            <div class="brand-icon">P</div>

            <div>
                <div class="brand-name">Polmart.</div>
                <div class="brand-subtitle">SMART COOPERATIVE</div>
            </div>
        </div>

        <div class="menu-label">MENU UTAMA</div>

        <!-- DASHBOARD -->
        <a href="{{ route('dashboard') }}"
           class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="menu-icon">▦</span>
            Dashboard
        </a>

        <div class="menu-label">MANAJEMEN KOPERASI</div>

        <!-- INVENTARIS BARANG -->
        <a href="{{ route('inventaris') }}"
           class="menu-link {{ request()->routeIs('inventaris') ? 'active' : '' }}">
            <span class="menu-icon">▣</span>
            Inventaris Barang
        </a>

       <a href="{{ route('transaksi.walkin') }}"
   class="menu-link {{ request()->routeIs('transaksi.walkin') ? 'active' : '' }}">
    <span class="menu-icon">🛒</span>
    Transaksi Walk-in
</a>

        <a href="{{ route('validasi.loket') }}"
   class="menu-link {{ request()->routeIs('validasi.loket') ? 'active' : '' }}">
    <span class="menu-icon">⌕</span>
    Validasi Loket
</a>

        <div class="menu-label">DATA & LAPORAN</div>

     <a href="{{ route('impor.excel') }}"
   class="menu-link {{ request()->routeIs('impor.excel') ? 'active' : '' }}">
    <span class="menu-icon">▤</span>
    Impor Excel
</a>

        <a href="{{ route('riwayat.transaksi') }}"
   class="menu-link {{ request()->routeIs('riwayat.transaksi') ? 'active' : '' }}">
    <span class="menu-icon">▧</span>
    Riwayat Transaksi
</a>

        <a href="{{ route('laporan') }}"
   class="menu-link {{ request()->routeIs('laporan') ? 'active' : '' }}">
    <span class="menu-icon">▥</span>
    Laporan
</a>

        <div class="sidebar-bottom">
            <div class="admin-profile">
                <div class="avatar">A</div>

                <div>
                    <div class="profile-name">
                        {{ Auth::user()->name ?? 'Administrator' }}
                    </div>

                    <div class="profile-role">Pengurus Polmart</div>
                </div>
            </div>
        </div>

    </aside>

    <div class="sidebar-overlay"
         id="sidebarOverlay"
         onclick="toggleSidebar()">
    </div>

    <!-- MAIN -->
    <div class="main-content">

        <!-- TOPBAR -->
        <header class="topbar">

            <div class="topbar-left">

                <button class="mobile-menu" onclick="toggleSidebar()">
                    ☰
                </button>

                <div class="page-context">
                    Polmart / <strong style="color:#334155;">Admin Panel</strong>
                </div>

            </div>

            <div class="topbar-right">

                <div class="search-box">
                    <span>⌕</span>
                    <input type="text" placeholder="Cari sesuatu...">
                    <span style="font-size:10px;color:#94a3b8;">⌘ K</span>
                </div>

                <button class="notification" type="button">
                    ♧
                </button>

                <div class="topbar-profile">

                    <div class="avatar">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>

                    <div>
                        <div class="topbar-profile-name">
                            {{ Auth::user()->name ?? 'Administrator' }}
                        </div>

                        <div class="topbar-profile-role">
                            Administrator
                        </div>
                    </div>

                </div>

            </div>

        </header>

        <!-- CONTENT -->
        <main class="page-container">

            @isset($header)
                <div class="page-heading">
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
</script>

</body>
</html>
