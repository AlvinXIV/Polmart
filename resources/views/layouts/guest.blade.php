
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'POLMART - Koperasi Digital')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-800 antialiased">

    <main class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

        <!-- LEFT PANEL -->
        <section class="relative overflow-hidden bg-[#173f32] text-white
                        px-7 py-10 sm:px-12 lg:px-16 lg:py-14
                        flex flex-col justify-between min-h-[300px] lg:min-h-screen">

            <!-- Brand -->
            <a href="/" class="inline-flex items-center gap-3 w-fit">
                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center">
                    <span class="text-[#173f32] font-bold text-xl">P</span>
                </div>

                <span class="text-2xl font-bold tracking-wide">POLMART</span>
            </a>

            <!-- Introduction -->
            <div class="relative z-10 max-w-lg mt-12 lg:mt-0">

                <p class="text-sm font-medium tracking-widest text-green-200 uppercase">
                    Koperasi Digital
                </p>

                <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight">
                    Belanja kebutuhanmu,
                    <span class="text-green-300">lebih mudah.</span>
                </h1>

                <p class="mt-5 text-green-100 leading-relaxed max-w-md">
                    Temukan berbagai kebutuhan dengan mudah melalui
                    layanan pemesanan online POLMART.
                </p>

                <!-- Shopping Illustration -->
                <div class="mt-9 bg-white/10 border border-white/10 rounded-2xl p-5">

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-green-100">Belanja praktis</p>
                            <p class="font-semibold text-lg mt-1">
                                Semua dalam satu tempat
                            </p>
                        </div>

                        <div class="text-4xl">🛍️</div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mt-5">

                        <div class="bg-white/10 rounded-xl p-3 text-center">
                            <div class="text-2xl">🥤</div>
                            <p class="text-xs mt-2 text-green-100">Minuman</p>
                        </div>

                        <div class="bg-white/10 rounded-xl p-3 text-center">
                            <div class="text-2xl">🍪</div>
                            <p class="text-xs mt-2 text-green-100">Makanan</p>
                        </div>

                        <div class="bg-white/10 rounded-xl p-3 text-center">
                            <div class="text-2xl">📦</div>
                            <p class="text-xs mt-2 text-green-100">Kebutuhan</p>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Footer -->
            <p class="relative z-10 mt-10 text-xs text-green-200">
                © {{ date('Y') }} POLMART. Sistem Informasi Koperasi.
            </p>

            <!-- Decorative Shapes -->
            <div class="absolute -right-24 -top-24 w-72 h-72 rounded-full
                        border border-white/10"></div>

            <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full
                        border border-white/10"></div>

        </section>

        <!-- RIGHT PANEL -->
        <section class="bg-[#f8faf8] flex items-center justify-center
                        px-5 py-10 sm:px-10 lg:px-16">

            <div class="w-full max-w-md">

                <!-- Mobile Brand -->
                <div class="lg:hidden mb-8 text-center">
                    <a href="/" class="text-2xl font-bold text-[#173f32]">
                        POLMART
                    </a>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100
                            shadow-sm p-6 sm:p-9">

                    {{ $slot }}

                </div>

                <p class="text-center text-xs text-gray-400 mt-6">
                    POLMART · Koperasi Digital
                </p>

            </div>
        </section>

    </main>

</body>
</html>
