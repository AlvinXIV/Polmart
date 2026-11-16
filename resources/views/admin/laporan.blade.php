
<x-app-layout>
    <div class="report-page">
        <div class="report-heading">
            <div>
                <span class="eyebrow">BUSINESS OVERVIEW</span>
                <h1>Laporan</h1>
                <p>Ringkasan aktivitas dan performa transaksi Polmart.</p>
            </div>
            <button class="report-export" onclick="exportReport()">↓ Export Laporan</button>
        </div>

        <div class="report-filter">
            <div>
                <label>Periode Laporan</label>
                <select id="periodSelect" onchange="changePeriod()">
                    <option value="November 2026">November 2026</option>
                    <option value="Oktober 2026">Oktober 2026</option>
                    <option value="September 2026">September 2026</option>
                </select>
            </div>
            <span class="report-date" id="periodLabel">Periode: November 2026</span>
        </div>

        <div class="report-summary">
            <div class="summary-card">
                <div class="summary-icon blue-icon">▤</div>
                <span>Total Pendapatan</span>
                <h2>Rp24.850.000</h2>
                <small>Akumulasi pendapatan contoh</small>
            </div>
            <div class="summary-card">
                <div class="summary-icon green-icon">✓</div>
                <span>Transaksi Berhasil</span>
                <h2>1.248</h2>
                <small>Transaksi selesai</small>
            </div>
            <div class="summary-card">
                <div class="summary-icon orange-icon">◷</div>
                <span>Rata-rata Transaksi</span>
                <h2>Rp19.912</h2>
                <small>Nilai rata-rata pembelian</small>
            </div>
            <div class="summary-card">
                <div class="summary-icon purple-icon">▦</div>
                <span>Produk Terjual</span>
                <h2>3.560</h2>
                <small>Total unit produk</small>
            </div>
        </div>

        <div class="report-grid">
            <div class="report-card revenue-card">
                <div class="report-card-heading">
                    <div>
                        <h3>Ringkasan Pendapatan</h3>
                        <p>Perbandingan pendapatan selama satu periode</p>
                    </div>
                    <span class="chart-label">Pendapatan</span>
                </div>
                <div class="revenue-chart">
                    <div class="chart-y-labels">
                        <span>5 jt</span><span>4 jt</span><span>3 jt</span><span>2 jt</span><span>1 jt</span><span>0</span>
                    </div>
                    <div class="chart-area">
                        <div class="chart-grid-lines">
                            <i></i><i></i><i></i><i></i><i></i><i></i>
                        </div>
                        <div class="chart-bars" id="revenueBars">
                            <div class="bar-column"><div class="bar" style="height:48%"></div><span>1</span></div>
                            <div class="bar-column"><div class="bar" style="height:65%"></div><span>2</span></div>
                            <div class="bar-column"><div class="bar" style="height:55%"></div><span>3</span></div>
                            <div class="bar-column"><div class="bar" style="height:82%"></div><span>4</span></div>
                            <div class="bar-column"><div class="bar" style="height:70%"></div><span>5</span></div>
                            <div class="bar-column"><div class="bar" style="height:92%"></div><span>6</span></div>
                            <div class="bar-column"><div class="bar" style="height:62%"></div><span>7</span></div>
                        </div>
                    </div>
                </div>
                <div class="chart-caption">Grafik ilustrasi pendapatan mingguan</div>
            </div>

            <div class="report-card payment-card">
                <div class="report-card-heading">
                    <div>
                        <h3>Metode Pembayaran</h3>
                        <p>Distribusi metode pembayaran</p>
                    </div>
                </div>
                <div class="payment-list">
                    <div class="payment-item">
                        <div class="payment-info"><span>QRIS</span><strong>48%</strong></div>
                        <div class="progress"><i style="width:48%"></i></div>
                    </div>
                    <div class="payment-item">
                        <div class="payment-info"><span>Tunai</span><strong>32%</strong></div>
                        <div class="progress"><i class="cash-progress" style="width:32%"></i></div>
                    </div>
                    <div class="payment-item">
                        <div class="payment-info"><span>Transfer</span><strong>20%</strong></div>
                        <div class="progress"><i class="transfer-progress" style="width:20%"></i></div>
                    </div>
                </div>
                <div class="payment-total">
                    <span>Total Transaksi</span>
                    <strong>1.248</strong>
                </div>
            </div>
        </div>

        <div class="report-card product-report">
            <div class="report-card-heading">
                <div>
                    <h3>Produk Terlaris</h3>
                    <p>Produk dengan jumlah penjualan tertinggi</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="product-table">
                    <thead>
                        <tr><th>PRODUK</th><th>KATEGORI</th><th>TERJUAL</th><th>PENDAPATAN</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>Buku Tulis</strong></td><td>Alat Tulis</td><td>850 unit</td><td>Rp4.250.000</td></tr>
                        <tr><td><strong>Pulpen Hitam</strong></td><td>Alat Tulis</td><td>720 unit</td><td>Rp2.160.000</td></tr>
                        <tr><td><strong>Air Mineral</strong></td><td>Minuman</td><td>640 unit</td><td>Rp1.920.000</td></tr>
                        <tr><td><strong>Penghapus</strong></td><td>Alat Tulis</td><td>510 unit</td><td>Rp1.275.000</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="report-note">* Seluruh angka dan grafik pada halaman ini merupakan data simulasi untuk tampilan frontend.</p>
    </div>

    <style>
        .report-page{padding:28px;color:#172033}
        .report-heading{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:25px}
        .eyebrow{font-size:10px;letter-spacing:1.5px;font-weight:800;color:#1685d4}
        .report-heading h1{font-size:27px;font-weight:800;margin:5px 0}
        .report-heading p{font-size:13px;color:#8993a5}
        .report-export{background:#087bc1;color:white;border:0;border-radius:10px;padding:12px 17px;font-weight:700;cursor:pointer}
        .report-filter{display:flex;justify-content:space-between;align-items:end;background:white;border:1px solid #e9edf3;border-radius:13px;padding:17px 20px;margin-bottom:20px}
        .report-filter label{display:block;font-size:11px;color:#8993a5;margin-bottom:7px}
        .report-filter select{border:1px solid #e1e6ee;border-radius:8px;padding:10px 13px;min-width:190px;color:#344054;background:white}
        .report-date{font-size:11px;color:#8993a5}
        .report-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:20px}
        .summary-card,.report-card{background:white;border:1px solid #e9edf3;border-radius:14px;padding:20px}
        .summary-icon{width:35px;height:35px;display:grid;place-items:center;border-radius:10px;font-weight:800;margin-bottom:15px}
        .blue-icon{background:#e8f3ff;color:#1685d4}.green-icon{background:#e6f8ef;color:#15965b}
        .orange-icon{background:#fff3df;color:#d58a1c}.purple-icon{background:#f1eaff;color:#8758c7}
        .summary-card span{font-size:12px;color:#8993a5}
        .summary-card h2{font-size:22px;font-weight:800;margin:7px 0}
        .summary-card small{font-size:10px;color:#a0a9b7}
        .report-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:18px;margin-bottom:20px}
        .report-card-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px}
        .report-card-heading h3{font-size:15px;font-weight:800}
        .report-card-heading p{font-size:11px;color:#8993a5;margin-top:5px}
        .chart-label{font-size:10px;color:#1685d4;background:#eaf5ff;padding:7px 10px;border-radius:7px}
        .revenue-chart{height:220px;display:flex;gap:12px}
        .chart-y-labels{display:flex;flex-direction:column;justify-content:space-between;font-size:9px;color:#a0a9b7;padding-bottom:20px}
        .chart-area{position:relative;flex:1}
        .chart-grid-lines{position:absolute;inset:0 0 20px;display:flex;flex-direction:column;justify-content:space-between}
        .chart-grid-lines i{border-top:1px dashed #e8edf3;width:100%}
        .chart-bars{position:absolute;inset:0 0 20px;display:flex;align-items:end;justify-content:space-around;gap:8px}
        .bar-column{height:100%;display:flex;flex-direction:column;justify-content:end;align-items:center;gap:7px;flex:1}
        .bar{width:min(35px,70%);background:linear-gradient(180deg,#39a7ed,#087bc1);border-radius:6px 6px 2px 2px;min-height:5px}
        .bar-column span{font-size:9px;color:#8993a5}
        .chart-caption{text-align:center;color:#a0a9b7;font-size:10px;margin-top:10px}
        .payment-list{padding-top:5px}
        .payment-item{margin-bottom:22px}
        .payment-info{display:flex;justify-content:space-between;font-size:12px;margin-bottom:9px}
        .payment-info span{color:#697586}.payment-info strong{color:#172033}
        .progress{height:9px;background:#edf1f6;border-radius:10px;overflow:hidden}
        .progress i{display:block;height:100%;background:#1685d4;border-radius:10px}
        .progress .cash-progress{background:#19a56b}.progress .transfer-progress{background:#a17ad6}
        .payment-total{border-top:1px solid #edf0f4;padding-top:16px;display:flex;justify-content:space-between;font-size:12px}
        .payment-total span{color:#8993a5}
        .product-report{margin-bottom:15px}
        .table-responsive{overflow-x:auto}
        .product-table{width:100%;border-collapse:collapse;white-space:nowrap}
        .product-table th{text-align:left;background:#f8fafc;padding:13px;font-size:10px;color:#929bad;letter-spacing:.5px}
        .product-table td{padding:15px 13px;border-bottom:1px solid #edf0f4;font-size:12px;color:#697586}
        .product-table td strong{color:#172033}
        .report-note{font-size:10px;color:#a0a9b7;margin-top:12px}
        @media(max-width:950px){.report-summary{grid-template-columns:repeat(2,1fr)}.report-grid{grid-template-columns:1fr}}
        @media(max-width:600px){.report-page{padding:18px}.report-heading{align-items:flex-start;flex-direction:column}.report-filter{align-items:flex-start;flex-direction:column;gap:12px}.report-summary{gap:10px}.summary-card{padding:14px}.summary-card h2{font-size:18px}}
    </style>

    <script>
        function changePeriod(){
            const period=document.getElementById('periodSelect').value;
            document.getElementById('periodLabel').textContent='Periode: '+period;
            // Angka laporan masih data simulasi, belum mengambil data berdasarkan periode.
        }

        function exportReport(){
            const rows=[
                ['LAPORAN POLMART'],
                ['Periode',document.getElementById('periodSelect').value],
                [],
                ['Ringkasan','Nilai'],
                ['Total Pendapatan','Rp24.850.000'],
                ['Transaksi Berhasil','1248'],
                ['Rata-rata Transaksi','Rp19.912'],
                ['Produk Terjual','3560'],
                [],
                ['Produk','Kategori','Terjual','Pendapatan'],
                ['Buku Tulis','Alat Tulis','850','Rp4.250.000'],
                ['Pulpen Hitam','Alat Tulis','720','Rp2.160.000'],
                ['Air Mineral','Minuman','640','Rp1.920.000'],
                ['Penghapus','Alat Tulis','510','Rp1.275.000']
            ];
            const csv=rows.map(row=>row.map(value=>'"'+String(value??'').replaceAll('"','""')+'"').join(',')).join('\n');
            const blob=new Blob(['\ufeff'+csv],{type:'text/csv;charset=utf-8;'});
            const url=URL.createObjectURL(blob);
            const a=document.createElement('a');
            a.href=url;
            a.download='laporan-polmart.csv';
            a.click();
            URL.revokeObjectURL(url);
        }
    </script>
</x-app-layout>
