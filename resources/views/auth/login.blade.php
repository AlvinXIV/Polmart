<x-guest-layout>
    @section('title', 'Masuk - Polmart')

    <div style="margin-bottom: 28px;">
        <span style="display: inline-block; font-size: 11px; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 4px 12px; border-radius: 20px; border: 1px solid #bfdbfe; margin-bottom: 12px; letter-spacing: 0.5px;">
            PORTAL MASUK
        </span>
        <h2 style="font-size: 26px; font-weight: 800; color: #0f172a; margin: 0 0 8px; letter-spacing: -0.5px;">
            Selamat Datang Kembali
        </h2>
        <p style="font-size: 13.5px; color: #64748b; margin: 0; line-height: 1.5;">
            Silakan masukkan email dan kata sandi untuk mengakses akun Anda.
        </p>
    </div>

    <!-- Quick Credentials Helper for Testing -->
    <div style="background: #f0f7ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 14px 16px; margin-bottom: 24px; font-size: 12.5px; color: #1e40af;">
        <div style="font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <span>💡</span> Akun Demo Pengujian (Seeder):
        </div>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center; background: #ffffff; padding: 8px 12px; border-radius: 8px; border: 1px solid #dbeafe;">
                <div>
                    <div><strong>Anggota:</strong> anggota@polmart.test</div>
                    <div style="font-size: 11px; color: #64748b;">Password: password</div>
                </div>
                <button type="button" onclick="fillDemoCredentials('anggota@polmart.test')" style="background: #16a34a; color: #ffffff; border: none; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer;">
                    Isi Anggota
                </button>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; background: #ffffff; padding: 8px 12px; border-radius: 8px; border: 1px solid #dbeafe;">
                <div>
                    <div><strong>Admin:</strong> admin@polmart.test</div>
                    <div style="font-size: 11px; color: #64748b;">Password: password</div>
                </div>
                <button type="button" onclick="fillDemoCredentials('admin@polmart.test')" style="background: #2563eb; color: #ffffff; border: none; padding: 5px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer;">
                    Isi Admin
                </button>
            </div>
        </div>
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
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="font-size: 12px; color: #2563eb; font-weight: 600; text-decoration: none;">
                        Lupa sandi?
                    </a>
                @endif
            </div>
            <input id="password" class="polmart-input" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
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
            Masuk ke Polmart →
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
        function fillDemoCredentials(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
        }
    </script>
</x-guest-layout>
