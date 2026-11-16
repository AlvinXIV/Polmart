
<x-app-layout>
    <div class="history-page">
        <div class="page-heading">
            <div>
                <span class="eyebrow">TRANSACTION MANAGEMENT</span>
                <h1>Riwayat Transaksi</h1>
                <p>Pantau dan kelola seluruh transaksi yang pernah dilakukan.</p>
            </div>
            <button class="export-btn" onclick="exportHistory()">
                ↓ Export CSV
            </button>
        </div>

        <div class="history-stats">
            <div class="stat-card">
                <span>Total Transaksi</span>
                <strong id="totalCount">128</strong>
                <small>Seluruh transaksi</small>
            </div>
            <div class="stat-card">
                <span>Berhasil</span>
                <strong class="green">112</strong>
                <small>Transaksi selesai</small>
            </div>
            <div class="stat-card">
                <span>Menunggu</span>
                <strong class="orange">10</strong>
                <small>Belum divalidasi</small>
            </div>
            <div class="stat-card">
                <span>Dibatalkan</span>
                <strong class="red">6</strong>
                <small>Transaksi dibatalkan</small>
            </div>
        </div>

        <div class="history-panel">
            <div class="panel-heading">
                <div>
                    <h2>Daftar Transaksi</h2>
                    <p>Informasi transaksi yang tercatat di sistem</p>
                </div>
            </div>

            <div class="history-filters">
                <input type="text" id="searchHistory"
                    placeholder="Cari ID transaksi atau nama pembeli..."
                    oninput="filterHistory()">

                <select id="statusFilter" onchange="filterHistory()">
                    <option value="">Semua Status</option>
                    <option value="Berhasil">Berhasil</option>
                    <option value="Menunggu">Menunggu</option>
                    <option value="Dibatalkan">Dibatalkan</option>
                </select>
            </div>

            <div class="table-wrapper">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>ID TRANSAKSI</th>
                            <th>PEMBELI</th>
                            <th>TANGGAL</th>
                            <th>METODE</th>
                            <th>TOTAL</th>
                            <th>STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody">
                        <tr data-id="TRX-00128" data-name="Andi Saputra" data-status="Berhasil"
                            data-date="16 Nov 2026" data-method="QRIS" data-total="150000">
                            <td><strong>TRX-00128</strong></td>
                            <td>Andi Saputra</td>
                            <td>16 Nov 2026</td>
                            <td>QRIS</td>
                            <td>Rp150.000</td>
                            <td><span class="status success">Berhasil</span></td>
                            <td><button class="detail-btn" onclick="showDetail(this)">Detail</button></td>
                        </tr>
                        <tr data-id="TRX-00127" data-name="Siti Rahma" data-status="Menunggu"
                            data-date="16 Nov 2026" data-method="Tunai" data-total="85000">
                            <td><strong>TRX-00127</strong></td>
                            <td>Siti Rahma</td>
                            <td>16 Nov 2026</td>
                            <td>Tunai</td>
                            <td>Rp85.000</td>
                            <td><span class="status pending">Menunggu</span></td>
                            <td><button class="detail-btn" onclick="showDetail(this)">Detail</button></td>
                        </tr>
                        <tr data-id="TRX-00126" data-name="Budi Santoso" data-status="Berhasil"
                            data-date="15 Nov 2026" data-method="Transfer" data-total="225000">
                            <td><strong>TRX-00126</strong></td>
                            <td>Budi Santoso</td>
                            <td>15 Nov 2026</td>
                            <td>Transfer</td>
                            <td>Rp225.000</td>
                            <td><span class="status success">Berhasil</span></td>
                            <td><button class="detail-btn" onclick="showDetail(this)">Detail</button></td>
                        </tr>
                        <tr data-id="TRX-00125" data-name="Dewi Lestari" data-status="Dibatalkan"
                            data-date="15 Nov 2026" data-method="QRIS" data-total="65000">
                            <td><strong>TRX-00125</strong></td>
                            <td>Dewi Lestari</td>
                            <td>15 Nov 2026</td>
                            <td>QRIS</td>
                            <td>Rp65.000</td>
                            <td><span class="status cancelled">Dibatalkan</span></td>
                            <td><button class="detail-btn" onclick="showDetail(this)">Detail</button></td>
                        </tr>
                        <tr data-id="TRX-00124" data-name="Rizky Pratama" data-status="Berhasil"
                            data-date="14 Nov 2026" data-method="Tunai" data-total="175000">
                            <td><strong>TRX-00124</strong></td>
                            <td>Rizky Pratama</td>
                            <td>14 Nov 2026</td>
                            <td>Tunai</td>
                            <td>Rp175.000</td>
                            <td><span class="status success">Berhasil</span></td>
                            <td><button class="detail-btn" onclick="showDetail(this)">Detail</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="emptyHistory" class="empty-state" style="display:none">
                Tidak ada transaksi yang sesuai dengan pencarian.
            </div>
            <div class="table-footer">
                <span>Menampilkan data transaksi contoh</span>
                <span>5 transaksi</span>
            </div>
        </div>
    </div>

    <div class="detail-overlay" id="detailOverlay" onclick="closeDetail(event)">
        <div class="detail-modal">
            <button class="close-modal" onclick="closeDetail()">×</button>
            <span class="eyebrow">TRANSACTION DETAIL</span>
            <h2>Detail Transaksi</h2>
            <div class="detail-content" id="detailContent"></div>
            <button class="modal-close-btn" onclick="closeDetail()">Tutup</button>
        </div>
    </div>

    <style>
        .history-page{padding:28px;color:#172033}
        .page-heading{display:flex;justify-content:space-between;align-items:center;gap:20px;margin-bottom:26px}
        .eyebrow{font-size:10px;font-weight:800;letter-spacing:1.5px;color:#1685d4}
        .page-heading h1{font-size:27px;font-weight:800;margin:5px 0}
        .page-heading p,.panel-heading p{font-size:13px;color:#8993a5}
        .export-btn{background:#087bc1;color:white;border:0;border-radius:10px;padding:12px 18px;font-weight:700;cursor:pointer}
        .history-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
        .stat-card,.history-panel{background:white;border:1px solid #e9edf3;border-radius:15px;padding:20px}
        .stat-card span{display:block;font-size:12px;color:#7e899b}
        .stat-card strong{display:block;font-size:26px;margin:10px 0 4px}
        .stat-card small{font-size:11px;color:#a0a9b7}
        .green{color:#16a36a}.orange{color:#e89a25}.red{color:#e05252}
        .panel-heading{margin-bottom:20px}
        .panel-heading h2{font-size:17px;font-weight:800}
        .history-filters{display:flex;gap:12px;margin-bottom:18px}
        .history-filters input,.history-filters select{border:1px solid #e1e6ee;border-radius:9px;padding:11px 13px;font-size:12px;outline:none;background:#fff}
        .history-filters input{flex:1}
        .history-filters input:focus,.history-filters select:focus{border-color:#1685d4}
        .table-wrapper{overflow-x:auto}
        .history-table{width:100%;border-collapse:collapse;white-space:nowrap}
        .history-table th{text-align:left;font-size:10px;letter-spacing:.6px;color:#929bad;background:#f8fafc;padding:14px 12px}
        .history-table td{padding:16px 12px;border-bottom:1px solid #edf0f4;font-size:12px;color:#566174}
        .history-table td strong{color:#172033}
        .status{padding:6px 10px;border-radius:20px;font-size:10px;font-weight:700}
        .success{background:#e6f8ef;color:#15965b}.pending{background:#fff4df;color:#c88719}.cancelled{background:#ffeded;color:#d84c4c}
        .detail-btn{border:1px solid #dce5ef;background:white;border-radius:7px;padding:7px 12px;color:#1685d4;font-size:11px;font-weight:700;cursor:pointer}
        .table-footer{display:flex;justify-content:space-between;padding-top:17px;font-size:11px;color:#929bad}
        .empty-state{text-align:center;padding:25px;color:#929bad;font-size:13px}
        .detail-overlay{display:none;position:fixed;inset:0;background:#10182888;z-index:9999;align-items:center;justify-content:center;padding:20px}
        .detail-overlay.show{display:flex}
        .detail-modal{position:relative;background:white;border-radius:18px;padding:28px;width:100%;max-width:430px}
        .detail-modal h2{font-size:21px;margin:7px 0 20px}
        .close-modal{position:absolute;right:18px;top:12px;border:0;background:none;font-size:28px;cursor:pointer;color:#8993a5}
        .detail-row{display:flex;justify-content:space-between;gap:15px;padding:12px 0;border-bottom:1px solid #edf0f4;font-size:12px}
        .detail-row span{color:#8993a5}.detail-row strong{text-align:right}
        .modal-close-btn{width:100%;margin-top:20px;background:#087bc1;color:white;border:0;border-radius:9px;padding:12px;font-weight:700;cursor:pointer}
        @media(max-width:850px){.history-stats{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:600px){.history-page{padding:18px}.page-heading{align-items:flex-start;flex-direction:column}.history-filters{flex-direction:column}.history-stats{gap:10px}.stat-card{padding:15px}}
    </style>

    <script>
        function filterHistory(){
            const keyword=document.getElementById('searchHistory').value.toLowerCase();
            const status=document.getElementById('statusFilter').value;
            const rows=document.querySelectorAll('#historyBody tr');
            let visible=0;
            rows.forEach(row=>{
                const matchText=row.dataset.id.toLowerCase().includes(keyword) ||
                    row.dataset.name.toLowerCase().includes(keyword);
                const matchStatus=!status || row.dataset.status===status;
                const show=matchText && matchStatus;
                row.style.display=show?'':'none';
                if(show) visible++;
            });
            document.getElementById('emptyHistory').style.display=visible?'none':'block';
        }

        function showDetail(button){
            const row=button.closest('tr');
            const fields=[
                ['ID Transaksi',row.dataset.id],
                ['Nama Pembeli',row.dataset.name],
                ['Tanggal',row.dataset.date],
                ['Metode Pembayaran',row.dataset.method],
                ['Total Pembayaran','Rp'+Number(row.dataset.total).toLocaleString('id-ID')],
                ['Status',row.dataset.status]
            ];
            document.getElementById('detailContent').innerHTML=fields.map(item=>
                `<div class="detail-row"><span>${item[0]}</span><strong>${item[1]}</strong></div>`
            ).join('');
            document.getElementById('detailOverlay').classList.add('show');
        }

        function closeDetail(event){
            if(!event || event.target.id==='detailOverlay'){
                document.getElementById('detailOverlay').classList.remove('show');
            }
        }

        function exportHistory(){
            const rows=[...document.querySelectorAll('#historyBody tr')].filter(row=>row.style.display!=='none');
            const csv=[['ID Transaksi','Pembeli','Tanggal','Metode','Total','Status']];
            rows.forEach(row=>csv.push([
                row.dataset.id,row.dataset.name,row.dataset.date,row.dataset.method,row.dataset.total,row.dataset.status
            ]));
            const content=csv.map(row=>row.map(value=>'"'+String(value).replaceAll('"','""')+'"').join(',')).join('\n');
            const blob=new Blob(['\ufeff'+content],{type:'text/csv;charset=utf-8;'});
            const url=URL.createObjectURL(blob);
            const a=document.createElement('a');
            a.href=url;a.download='riwayat-transaksi.csv';a.click();
            URL.revokeObjectURL(url);
        }
    </script>
</x-app-layout>
