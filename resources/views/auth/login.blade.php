<x-guest-layout>
    @section('title', 'Masuk - Polmart')

    <div style="margin-bottom: 28px;">
        <h2 style="font-size: 26px; font-weight: 800; color: #0f172a; margin: 0 0 8px; letter-spacing: -0.5px;">
            Selamat Datang Kembali
        </h2>
        <p style="font-size: 13.5px; color: #64748b; margin: 0; line-height: 1.5;">
            Masukkan email dan kata sandi akun Anda.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" style="display: flex; flex-direction: column; gap: 18px;">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="polmart-label">Alamat Email</label>
            <input id="email" class="polmart-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" style="margin-top: 6px; color: #ef4444; font-size: 12px;" />
        </div>

        <!-- Password -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <label for="password" class="polmart-label" style="margin-bottom: 0;">Kata Sandi</label>
            </div>
            <div style="position: relative;">
                <input id="password" class="polmart-input" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" style="padding-right: 42px;" />
                <button type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()" aria-label="Tampilkan kata sandi"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center;">
                    <svg id="eyeOpenIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg id="eyeClosedIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" style="margin-top: 6px; color: #ef4444; font-size: 12px;" />
        </div>

        <!-- Remember Me -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <input id="remember_me" type="checkbox" name="remember" style="width: 16px; height: 16px; accent-color: #2563eb; cursor: pointer;">
            <label for="remember_me" style="font-size: 13px; color: #64748b; cursor: pointer; user-select: none;">
                Ingat saya di perangkat ini
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="polmart-btn polmart-btn-primary" style="width: 100%; padding: 12px; font-size: 14.5px; border-radius: 10px; margin-top: 4px;">
            Masuk ke Polmart
        </button>

        <!-- Register Link -->
        <div style="text-align: center; font-size: 13.5px; color: #64748b; margin-top: 10px;">
            Belum memiliki akun?
            <a href="{{ route('register') }}" style="color: #2563eb; font-weight: 700; text-decoration: none; margin-left: 4px;">
                Daftar Sekarang
            </a>
        </div>
    </form>

    <script>
        function togglePasswordVisibility() {
            const input = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeOpenIcon');
            const eyeClosed = document.getElementById('eyeClosedIcon');
            const btn = document.getElementById('togglePasswordBtn');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
                btn.setAttribute('aria-label', 'Sembunyikan kata sandi');
            } else {
                input.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
                btn.setAttribute('aria-label', 'Tampilkan kata sandi');
            }
        }
    </script>
</x-guest-layout>
