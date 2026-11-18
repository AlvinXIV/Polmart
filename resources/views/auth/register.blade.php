<x-guest-layout>
    @section('title', 'Daftar Akun - Polmart')

    <div style="margin-bottom: 24px;">
        <span style="display: inline-block; font-size: 11px; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 4px 12px; border-radius: 20px; border: 1px solid #bfdbfe; margin-bottom: 12px; letter-spacing: 0.5px;">
            REGISTRASI ANGGOTA
        </span>
        <h2 style="font-size: 26px; font-weight: 800; color: #0f172a; margin: 0 0 8px; letter-spacing: -0.5px;">
            Buat Akun Polmart
        </h2>
        <p style="font-size: 13.5px; color: #64748b; margin: 0; line-height: 1.5;">
            Lengkapi formulir di bawah ini untuk menjadi anggota resmi koperasi.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" style="display: flex; flex-direction: column; gap: 16px;">
        @csrf

        <!-- Nomor Anggota / NIM / NIK -->
        <div>
            <x-input-label for="member_code" :value="__('Nomor Anggota / NIM / NIK')" />
            <x-text-input id="member_code" class="block mt-1 w-full" type="text" name="member_code" :value="old('member_code')" required autofocus />
            <x-input-error :messages="$errors->get('member_code')" class="mt-2" />
        </div>

        <!-- Name -->
        <div>
            <label for="name" class="polmart-label">Nama Lengkap</label>
            <input id="name" class="polmart-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Contoh: Budi Santoso" />
            <x-input-error :messages="$errors->get('name')" style="margin-top: 5px; color: #ef4444; font-size: 12px;" />
        </div>

        <!-- Nomor WhatsApp -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Nomor WhatsApp')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" placeholder="08xxxxxxxxxx" required />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="polmart-label">Alamat Email</label>
            <input id="email" class="polmart-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" style="margin-top: 5px; color: #ef4444; font-size: 12px;" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="polmart-label">Kata Sandi</label>
            <input id="password" class="polmart-input" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
            <x-input-error :messages="$errors->get('password')" style="margin-top: 5px; color: #ef4444; font-size: 12px;" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="polmart-label">Konfirmasi Kata Sandi</label>
            <input id="password_confirmation" class="polmart-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" />
            <x-input-error :messages="$errors->get('password_confirmation')" style="margin-top: 5px; color: #ef4444; font-size: 12px;" />
        </div>

        <!-- Submit Button -->
        <button type="submit" class="polmart-btn polmart-btn-primary" style="width: 100%; padding: 12px; font-size: 14.5px; border-radius: 10px; margin-top: 8px;">
            Daftar Sekarang
        </button>

        <!-- Login Link -->
        <div style="text-align: center; font-size: 13.5px; color: #64748b; margin-top: 6px;">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" style="color: #2563eb; font-weight: 700; text-decoration: none; margin-left: 4px;">
                Masuk di sini
            </a>
        </div>
    </form>
</x-guest-layout>