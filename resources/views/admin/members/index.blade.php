<x-app-layout>
    @section('title', 'Daftar Anggota - Polmart')
    @section('page_title', 'Daftar Anggota')

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Daftar Anggota Koperasi</h1>
                <p class="text-sm text-slate-500 mt-1">Kelola dan pantau seluruh anggota terdaftar di sistem Polmart.</p>
            </div>
            <a href="{{ route('admin.member-imports.index') }}" class="polmart-btn polmart-btn-primary whitespace-nowrap">
                <x-icon name="file-spreadsheet" class="w-4 h-4" />
                <span>Impor Anggota Excel</span>
            </a>
        </div>

        <!-- Ringkasan Statistik Anggota -->
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total Anggota</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="polmart-stat-icon bg-blue-50 text-blue-600"><x-icon name="users" class="w-5 h-5" /></div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Pernah Bertransaksi</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['with_orders']) }}</p>
                </div>
                <div class="polmart-stat-icon bg-slate-100 text-slate-600"><x-icon name="shopping-bag" class="w-5 h-5" /></div>
            </div>
            <div class="polmart-stat-card">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase">Terdaftar Bulan Ini</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['this_month']) }}</p>
                </div>
                <div class="polmart-stat-icon bg-slate-100 text-slate-600"><x-icon name="calendar" class="w-5 h-5" /></div>
            </div>
        </section>

        <!-- Pencarian & Tabel Anggota -->
        <section class="polmart-card space-y-4">
            <form method="GET" action="{{ route('admin.members.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari kode anggota, nama, email, atau no. HP..."
                       class="polmart-input sm:w-80">
                <button type="submit" class="polmart-btn polmart-btn-primary">Cari</button>
                @if ($search !== '')
                    <a href="{{ route('admin.members.index') }}" class="polmart-btn polmart-btn-secondary">Reset</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="polmart-table">
                    <thead>
                        <tr>
                            <th>Kode Anggota</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th class="text-center">Pesanan Selesai</th>
                            <th class="text-center">Status</th>
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
                                <td class="text-center font-semibold text-slate-900">{{ number_format($member->orders_count) }}</td>
                                <td class="text-center">
                                    <span class="polmart-badge polmart-badge-blue">ANGGOTA</span>
                                </td>
                                <td class="text-xs text-slate-500">{{ $member->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-slate-400 py-8">Belum ada data anggota yang sesuai.</td>
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
