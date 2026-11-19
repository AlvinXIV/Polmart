<x-app-layout>
    @section('title', 'Impor Data Anggota - Polmart')
    @section('page_title', 'Impor Data Anggota Excel')

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Impor Data Anggota Excel</h1>
                <p class="text-sm text-slate-500 mt-1">Tambahkan data anggota koperasi secara massal dari file Excel (.xlsx, .xls, atau .csv).</p>
            </div>
            <a href="{{ route('admin.member-imports.template') }}" class="polmart-btn polmart-btn-secondary whitespace-nowrap">
                <x-icon name="download" class="w-4 h-4" />
                <span>Download Template (.xlsx)</span>
            </a>
        </div>

        @if (session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Form Upload untuk Pratinjau -->
        <section class="polmart-card space-y-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">1. Unggah File Excel / CSV Daftar Anggota</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Kolom yang didukung: <code>kode_anggota</code>, <code>nama</code>, <code>email</code>, <code>no_hp</code> (opsional), dan <code>password</code> (opsional, default: <code>password</code>). Seluruh akun hasil impor otomatis memiliki peran <strong>member</strong>.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.member-imports.preview') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="polmart-input sm:w-auto text-sm">
                <button type="submit" class="polmart-btn polmart-btn-primary">
                    <x-icon name="search" class="w-4 h-4" />
                    <span>Validasi &amp; Pratinjau Data</span>
                </button>
            </form>
        </section>

        <!-- Hasil Pratinjau Validasi -->
        @if ($preview)
            <section class="polmart-card space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-4 pb-3 border-b border-slate-100">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">2. Ringkasan Pratinjau: {{ $preview['original_name'] }}</h2>
                        <div class="flex flex-wrap gap-4 mt-2 text-xs text-slate-600">
                            <span>Total Baris: <strong class="text-slate-900">{{ $preview['total_rows'] }}</strong></span>
                            <span>Valid: <strong class="text-slate-900">{{ $preview['valid_count'] }}</strong></span>
                            <span>Galat: <strong class="{{ $preview['invalid_count'] > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ $preview['invalid_count'] }}</strong></span>
                        </div>
                    </div>

                    @if ($preview['valid_count'] > 0)
                        <form method="POST" action="{{ route('admin.member-imports.store') }}">
                            @csrf
                            <input type="hidden" name="temp_path" value="{{ $preview['temp_path'] }}">
                            <button type="submit" class="polmart-btn polmart-btn-primary">
                                <x-icon name="check" class="w-4 h-4" />
                                <span>Konfirmasi &amp; Impor {{ $preview['valid_count'] }} Anggota Valid</span>
                            </button>
                        </form>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="polmart-table text-xs">
                        <thead>
                            <tr>
                                <th>Baris</th>
                                <th>Status</th>
                                <th>Kode Anggota</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. HP</th>
                                <th>Keterangan Validasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($preview['rows'] as $row)
                                <tr>
                                    <td class="text-slate-500 font-semibold">{{ $row['row_number'] }}</td>
                                    <td>
                                        @if ($row['is_valid'])
                                            <span class="polmart-badge polmart-badge-green">VALID</span>
                                        @else
                                            <span class="polmart-badge polmart-badge-red">GALAT</span>
                                        @endif
                                    </td>
                                    <td class="font-mono font-semibold text-slate-900">{{ $row['member_code'] ?: '—' }}</td>
                                    <td class="font-semibold text-slate-900">{{ $row['name'] ?: '—' }}</td>
                                    <td class="text-slate-600">{{ $row['email'] ?: '—' }}</td>
                                    <td class="text-slate-500">{{ $row['phone'] ?? '—' }}</td>
                                    <td class="{{ $row['is_valid'] ? 'text-slate-500' : 'text-rose-600 font-medium' }}">
                                        {{ $row['is_valid'] ? 'Siap diimpor' : implode('; ', $row['errors']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <!-- Daftar Anggota Terdaftar -->
        <section class="polmart-card space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-extrabold text-slate-900">Daftar Anggota Terdaftar</h2>
                <span class="polmart-badge polmart-badge-slate">Total: {{ $members->total() }} anggota</span>
            </div>

            <div class="overflow-x-auto">
                <table class="polmart-table">
                    <thead>
                        <tr>
                            <th>Kode Anggota</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th class="text-center">Role</th>
                            <th>Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td class="font-mono font-bold text-blue-700">{{ $member->member_code ?? '—' }}</td>
                                <td class="font-semibold text-slate-900">{{ $member->name }}</td>
                                <td class="text-xs text-slate-600">{{ $member->email }}</td>
                                <td class="text-xs text-slate-500">{{ $member->phone ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="polmart-badge polmart-badge-blue">MEMBER</span>
                                </td>
                                <td class="text-xs text-slate-500">{{ $member->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-slate-400 py-8">Belum ada data anggota terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($members->hasPages())
                <div class="pt-2">
                    {{ $members->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
