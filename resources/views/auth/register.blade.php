
<x-guest-layout>

    <div class="mb-7">
        <p class="text-sm font-medium text-green-700 mb-2">
            MEMBER REGISTRATION
        </p>

        <h2 class="text-2xl font-bold text-gray-900">
            Buat Akun POLMART
        </h2>

        <p class="text-sm text-gray-500 mt-2">
            Daftarkan dirimu untuk mulai berbelanja di koperasi.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nama Lengkap -->
        <div>
            <x-input-label for="name" value="Nama Lengkap" />

            <x-text-input
                id="name"
                class="block mt-1.5 w-full rounded-lg border-gray-300
                       focus:border-green-700 focus:ring-green-700"
                type="text"
                name="name"
                :value="old('name')"
                placeholder="Masukkan nama lengkap"
                required
                autofocus
                autocomplete="name"
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Nomor Anggota -->
        <div class="mt-4">
            <x-input-label
                for="member_code"
                value="Nomor Anggota / NIM / NIK"
            />

            <x-text-input
                id="member_code"
                class="block mt-1.5 w-full rounded-lg border-gray-300
                       focus:border-green-700 focus:ring-green-700"
                type="text"
                name="member_code"
                :value="old('member_code')"
                placeholder="Masukkan nomor anggota"
                required
            />

            <x-input-error
                :messages="$errors->get('member_code')"
                class="mt-2"
            />
        </div>

        <!-- Nomor WhatsApp -->
        <div class="mt-4">
            <x-input-label for="phone" value="Nomor WhatsApp" />

            <x-text-input
                id="phone"
                class="block mt-1.5 w-full rounded-lg border-gray-300
                       focus:border-green-700 focus:ring-green-700"
                type="tel"
                name="phone"
                :value="old('phone')"
                placeholder="Contoh: 081234567890"
                required
                autocomplete="tel"
            />

            <x-input-error
                :messages="$errors->get('phone')"
                class="mt-2"
            />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" value="Email" />

            <x-text-input
                id="email"
                class="block mt-1.5 w-full rounded-lg border-gray-300
                       focus:border-green-700 focus:ring-green-700"
                type="email"
                name="email"
                :value="old('email')"
                placeholder="nama@email.com"
                required
                autocomplete="username"
            />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Password" />

            <x-text-input
                id="password"
                class="block mt-1.5 w-full rounded-lg border-gray-300
                       focus:border-green-700 focus:ring-green-700"
                type="password"
                name="password"
                placeholder="Minimal 8 karakter"
                required
                autocomplete="new-password"
            />

            <x-input-error
                :messages="$errors->get('password')"
                class="mt-2"
            />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label
                for="password_confirmation"
                value="Konfirmasi Password"
            />

            <x-text-input
                id="password_confirmation"
                class="block mt-1.5 w-full rounded-lg border-gray-300
                       focus:border-green-700 focus:ring-green-700"
                type="password"
                name="password_confirmation"
                placeholder="Masukkan ulang password"
                required
                autocomplete="new-password"
            />

            <x-input-error
                :messages="$errors->get('password_confirmation')"
                class="mt-2"
            />
        </div>

        <!-- Submit -->
        <div class="mt-7">
            <x-primary-button
                class="w-full justify-center py-3 rounded-lg
                       bg-green-800 hover:bg-green-900
                       focus:bg-green-900 active:bg-green-950
                       text-sm font-semibold tracking-wide"
            >
                Daftar Sekarang
            </x-primary-button>
        </div>

        <!-- Login Link -->
        <div class="text-center mt-6">
            <p class="text-sm text-gray-500">
                Sudah memiliki akun?

                <a
                    href="{{ route('login') }}"
                    class="font-semibold text-green-800 hover:text-green-950
                           hover:underline"
                >
                    Masuk di sini
                </a>
            </p>
        </div>

    </form>

</x-guest-layout>
