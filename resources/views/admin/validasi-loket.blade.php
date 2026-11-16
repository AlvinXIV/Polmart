
<x-app-layout>
    <x-slot name="header">
        <h1>Validasi Loket</h1>
        <p>Periksa dan kelola transaksi yang membutuhkan validasi petugas.</p>
    </x-slot>

    <style>
        .validation-page {
            color: #172033;
        }

        .validation-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 22px;
        }

        .stat-card {
            background: white;
            border: 1px solid #edf0f5;
            border-radius: 14px;
            padding: 20px;
        }

        .stat-label {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 12px;
        }

        .stat-value {
            font-size: 25px;
            font-weight: 800;
        }

        .stat-note {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 7px;
        }

        .validation-card {
            background: white;
            border: 1px solid #edf0f5;
            border-radius: 15px;
            padding: 22px;
        }

        .card-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .card-heading h2 {
            font-size: 16px;
            font-weight: 800;
        }

        .card-heading p {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 5px;
        }

        .filter-control {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            font-size: 12px;
            outline: none;
            background: white;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .validation-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        .validation-table th {
            text-align: left;
            padding: 13px 12px;
            font-size: 10px;
            color: #94a3b8;
            background: #f8fafc;
            border-bottom: 1px solid #edf0f5;
        }

        .validation-table td {
            padding: 16px 12px;
            font-size: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .transaction-id {
            color: #2563eb;
            font-weight: 700;
        }

        .buyer-name {
            font-weight: 700;
        }

        .buyer-detail {
            color: #94a3b8;
            font-size: 10px;
            margin-top: 4px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .status-pending {
            background: #fff7ed;
            color: #ea580c;
        }

        .status-approved {
            background: #dcfce7;
            color: #15803d;
        }

        .status-rejected {
            background: #fee2e2;
            color: #dc2626;
        }

        .action-group {
            display: flex;
            gap: 7px;
        }

        .btn-detail,
        .btn-approve,
        .btn-reject {
            border: none;
            padding: 8px 10px;
            border-radius: 7px;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-detail {
            background: #eff6ff;
            color: #2563eb;
        }

        .btn-approve {
            background: #dcfce7;
            color: #15803d;
        }

        .btn-reject {
            background: #fee2e2;
            color: #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 35px;
            color: #94a3b8;
            font-size: 12px;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            z-index: 200;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-box {
            background: white;
            width: 100%;
            max-width: 440px;
            padding: 25px;
            border-radius: 16px;
        }

        .modal-box h3 {
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .modal-box p {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 18px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
        }

        .detail-row span {
            color: #94a3b8;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 22px;
        }

        .modal-actions button {
            flex: 1;
            padding: 11px;
            border: none;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-close {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-confirm {
            background: #2563eb;
            color: white;
        }

        @media(max-width: 700px) {
            .validation-stats {
                grid-template-columns: 1fr;
            }

            .validation-card {
                padding: 15px;
            }
        }
    </style>

    <div class="validation-page">

        <!-- STATISTIK -->
        <div class="validation-stats">

            <div class="stat-card">
                <div class="stat-label">Menunggu Validasi</div>
                <div class="stat-value" id="pendingCount">3</div>
                <div class="stat-note">Transaksi belum diperiksa</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Berhasil Divalidasi</div>
                <div class="stat-value" id="approvedCount">2</div>
                <div class="stat-note">Transaksi telah disetujui</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Ditolak</div>
                <div class="stat-value" id="rejectedCount">1</div>
                <div class="stat-note">Transaksi tidak disetujui</div>
            </div>

        </div>

        <!-- TABEL VALIDASI -->
        <div class="validation-card">

            <div class="card-heading">
                <div>
                    <h2>Daftar Transaksi</h2>
                    <p>Periksa detail transaksi sebelum memberikan persetujuan.</p>
                </div>

                <select class="filter-control" id="statusFilter"
                        onchange="filterTransactions()">
                    <option value="all">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                </select>
            </div>

            <div class="table-wrapper">
                <table class="validation-table">
                    <thead>
                        <tr>
                            <th>ID TRANSAKSI</th>
                            <th>PEMBELI</th>
                            <th>TANGGAL</th>
                            <th>TOTAL</th>
                            <th>STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>

                    <tbody id="transactionTable"></tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- MODAL DETAIL -->
    <div class="modal-overlay" id="detailModal">
        <div class="modal-box">
            <h3>Detail Transaksi</h3>
            <p>Informasi transaksi yang dipilih.</p>

            <div id="transactionDetail"></div>

            <div class="modal-actions">
                <button class="btn-close" onclick="closeModal()">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        let transactions = [
            {
                id: 'TRX-001',
                buyer: 'Andi Saputra',
                detail: 'Mahasiswa',
                date: '16 Nov 2026',
                total: 25000,
                payment: 'Tunai',
                status: 'pending'
            },
            {
                id: 'TRX-002',
                buyer: 'Siti Rahma',
                detail: 'Mahasiswa',
                date: '16 Nov 2026',
                total: 15000,
                payment: 'QRIS',
                status: 'pending'
            },
            {
                id: 'TRX-003',
                buyer: 'Budi Santoso',
                detail: 'Pengunjung',
                date: '15 Nov 2026',
                total: 40000,
                payment: 'Transfer',
                status: 'pending'
            },
            {
                id: 'TRX-004',
                buyer: 'Dina Putri',
                detail: 'Mahasiswa',
                date: '15 Nov 2026',
                total: 30000,
                payment: 'Tunai',
                status: 'approved'
            },
            {
                id: 'TRX-005',
                buyer: 'Rizky Firmansyah',
                detail: 'Mahasiswa',
                date: '14 Nov 2026',
                total: 12000,
                payment: 'QRIS',
                status: 'approved'
            },
            {
                id: 'TRX-006',
                buyer: 'Rani Amelia',
                detail: 'Pengunjung',
                date: '14 Nov 2026',
                total: 18000,
                payment: 'Tunai',
                status: 'rejected'
            }
        ];

        function rupiah(value) {
            return 'Rp ' + value.toLocaleString('id-ID');
        }

        function statusLabel(status) {
            if (status === 'pending') return 'Menunggu';
            if (status === 'approved') return 'Disetujui';
            return 'Ditolak';
        }

        function renderTransactions() {
            const tbody = document.getElementById('transactionTable');
            const filter = document.getElementById('statusFilter').value;

            const filtered = transactions.filter(item =>
                filter === 'all' || item.status === filter
            );

            if (filtered.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="empty-state">
                            Tidak ada transaksi dengan status ini.
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = filtered.map(item => `
                    <tr>
                        <td class="transaction-id">${item.id}</td>
                        <td>
                            <div class="buyer-name">${item.buyer}</div>
                            <div class="buyer-detail">${item.detail}</div>
                        </td>
                        <td>${item.date}</td>
                        <td><strong>${rupiah(item.total)}</strong></td>
                        <td>
                            <span class="status-badge status-${item.status}">
                                ${statusLabel(item.status)}
                            </span>
                        </td>
                        <td>
                            <div class="action-group">
                                <button class="btn-detail"
                                    onclick="showDetail('${item.id}')">
                                    Detail
                                </button>

                                ${item.status === 'pending' ? `
                                    <button class="btn-approve"
                                        onclick="updateStatus('${item.id}', 'approved')">
                                        ✓
                                    </button>
                                    <button class="btn-reject"
                                        onclick="updateStatus('${item.id}', 'rejected')">
                                        ×
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `).join('');
            }

            updateStats();
        }

        function updateStats() {
            document.getElementById('pendingCount').textContent =
                transactions.filter(item => item.status === 'pending').length;

            document.getElementById('approvedCount').textContent =
                transactions.filter(item => item.status === 'approved').length;

            document.getElementById('rejectedCount').textContent =
                transactions.filter(item => item.status === 'rejected').length;
        }

        function filterTransactions() {
            renderTransactions();
        }

        function updateStatus(id, status) {
            const item = transactions.find(item => item.id === id);

            if (!item) return;

            const message = status === 'approved'
                ? 'Yakin ingin menyetujui transaksi ini?'
                : 'Yakin ingin menolak transaksi ini?';

            if (!confirm(message)) return;

            item.status = status;
            renderTransactions();

            alert('Status transaksi berhasil diperbarui pada simulasi.');
        }

        function showDetail(id) {
            const item = transactions.find(item => item.id === id);

            if (!item) return;

            document.getElementById('transactionDetail').innerHTML = `
                <div class="detail-row">
                    <span>ID Transaksi</span>
                    <strong>${item.id}</strong>
                </div>
                <div class="detail-row">
                    <span>Nama Pembeli</span>
                    <strong>${item.buyer}</strong>
                </div>
                <div class="detail-row">
                    <span>Kategori</span>
                    <strong>${item.detail}</strong>
                </div>
                <div class="detail-row">
                    <span>Tanggal</span>
                    <strong>${item.date}</strong>
                </div>
                <div class="detail-row">
                    <span>Pembayaran</span>
                    <strong>${item.payment}</strong>
                </div>
                <div class="detail-row">
                    <span>Total</span>
                    <strong>${rupiah(item.total)}</strong>
                </div>
                <div class="detail-row">
                    <span>Status</span>
                    <strong>${statusLabel(item.status)}</strong>
                </div>
            `;

            document.getElementById('detailModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('detailModal').classList.remove('show');
        }

        renderTransactions();
    </script>
</x-app-layout>
