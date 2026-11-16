
<x-guest-layout>

    <!-- Heading -->
    <div class="mb-8">
        <p class="text-sm font-medium tracking-wider text-green-700 mb-2">
            MEMBER LOGIN
        </p>

        <h2 class="text-2xl font-bold text-gray-900">
            Selamat Datang Kembali!
        </h2>

        <p class="text-sm text-gray-500 mt-2">
            Masuk ke akun POLMART untuk melanjutkan belanja.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status
        class="mb-4"
        :status="session('status')"
    />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Alamat Email" />

            <x-text-input
                id="email"
                class="block mt-1.5 w-full rounded-lg border-gray-300
                       focus:border-green-700 focus:ring-green-700"
                type="email"
                name="email"
                :value="old('email')"
                placeholder="nama@email.com"
                required
                autofocus
                autocomplete="username"
            />

            <x-input-error
                :messages="$errors->get('email')"
                class="mt-2"
            />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <x-input-label for="password" value="Password" />

            <x-text-input
                id="password"
                class="block mt-1.5 w-full rounded-lg border-gray-300
                       focus:border-green-700 focus:ring-green-700"
                type="password"
                name="password"
                placeholder="Masukkan password"
                required
                autocomplete="current-password"
            />

            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2"
            />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mt-5">

            <label for="remember_me" class="inline-flex items-center">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="rounded border-gray-300 text-green-700
                           shadow-sm focus:ring-green-700"
                >

                <span class="ms-2 text-sm text-gray-600">
                    Ingat saya
                </span>
            </label>

            @if (Route::has('password.request'))
                <a
                    href="{{ route('password.request') }}"
                    class="text-sm font-medium text-green-800
                           hover:text-green-950 hover:underline"
                >
                    Lupa password?
                </a>
            @endif

        </div>

        <!-- Login Button -->
        <div class="mt-7">
            <x-primary-button
                class="w-full justify-center py-3 rounded-lg
                       bg-green-800 hover:bg-green-900
                       focus:bg-green-900 active:bg-green-950
                       text-sm font-semibold tracking-wide"
            >
                Masuk ke POLMART
            </x-primary-button>
        </div>

        <!-- Register Link -->
        <div class="text-center mt-7">
            <p class="text-sm text-gray-500">
                Belum memiliki akun?

                <a
                    href="{{ route('register') }}"
                    class="font-semibold text-green-800
                           hover:text-green-950 hover:underline"
                >
                    Daftar sekarang
                </a>
            </p>
        </div>

    </form>

</x-guest-layout>
