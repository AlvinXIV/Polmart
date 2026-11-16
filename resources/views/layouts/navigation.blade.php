
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('anggota.katalog') }}" class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-green-800 flex items-center justify-center">
                        <span class="text-white font-bold text-lg">P</span>
                    </div>
                    <span class="text-xl font-bold tracking-wide text-green-900">
                        POLMART
                    </span>
                </a>

                <!-- Menu Desktop -->
                <div class="hidden sm:flex sm:items-center sm:ms-10 sm:gap-8">

                    <a href="{{ route('anggota.katalog') }}"
                       class="text-sm font-medium py-5 transition {{ request()->routeIs('anggota.katalog') ? 'text-green-800 border-b-2 border-green-800' : 'text-gray-500 hover:text-green-800' }}">
                        Katalog
                    </a>

                    <a href="{{ route('anggota.keranjang') }}"
                       class="text-sm font-medium py-5 transition {{ request()->routeIs('anggota.keranjang') ? 'text-green-800 border-b-2 border-green-800' : 'text-gray-500 hover:text-green-800' }}">
                        Keranjang
                    </a>

                    <a href="{{ route('anggota.pesanan') }}"
                       class="text-sm font-medium py-5 transition {{ request()->routeIs('anggota.pesanan') ? 'text-green-800 border-b-2 border-green-800' : 'text-gray-500 hover:text-green-800' }}">
                        Pesanan
                    </a>

                </div>
            </div>

            <!-- Profil Desktop -->
            <div class="hidden sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                <span class="text-green-800 font-semibold">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                            </div>

                            <span>{{ Auth::user()->name }}</span>

                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profil Saya
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Keluar
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Tombol Mobile -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open"
                        class="p-2 rounded-md text-gray-500 hover:bg-gray-100">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }"
                              class="inline-flex" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>

                        <path :class="{ 'hidden': !open, 'inline-flex': open }"
                              class="hidden" stroke-linecap="round"
                              stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Navigasi Mobile -->
    <div x-show="open" x-transition class="sm:hidden border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1 px-4">

            <a href="{{ route('anggota.katalog') }}"
               class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('anggota.katalog') ? 'text-green-800 bg-green-50' : 'text-gray-600 hover:bg-gray-50' }}">
                Katalog
            </a>

            <a href="{{ route('anggota.keranjang') }}"
               class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('anggota.keranjang') ? 'text-green-800 bg-green-50' : 'text-gray-600 hover:bg-gray-50' }}">
                Keranjang
            </a>

            <a href="{{ route('anggota.pesanan') }}"
               class="block px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('anggota.pesanan') ? 'text-green-800 bg-green-50' : 'text-gray-600 hover:bg-gray-50' }}">
                Pesanan
            </a>
        </div>

        <!-- Informasi Akun -->
        <div class="border-t border-gray-200 px-4 py-4">
            <div class="font-medium text-sm text-gray-800">
                {{ Auth::user()->name }}
            </div>

            <div class="text-xs text-gray-500 mt-1">
                {{ Auth::user()->email }}
            </div>

            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}"
                   class="block py-2 text-sm text-gray-600">
                    Profil Saya
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block py-2 text-sm text-red-600">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
