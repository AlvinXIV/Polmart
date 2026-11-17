
<x-app-layout>
    @section('title', 'Impor Data Excel - Polmart')
    @section('page_title', 'Impor Data Excel')

    <x-slot name="header">
        <h1>Impor Data Excel</h1>
        <p>Tambahkan data inventaris Polmart secara massal menggunakan file Excel.</p>
    </x-slot>

    <style>
        .import-page {
            color: #172033;
        }

        .import-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 22px;
        }

        .import-stat {
            background: white;
            border: 1px solid #edf0f5;
            border-radius: 14px;
            padding: 20px;
        }

        .import-stat span {
            display: block;
            color: #94a3b8;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .import-stat strong {
            font-size: 24px;
            font-weight: 800;
        }

        .import-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 20px;
            align-items: start;
        }

        .import-card {
            background: white;
            border: 1px solid #edf0f5;
            border-radius: 15px;
            padding: 24px;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .card-description {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.7;
            margin-bottom: 22px;
        }

        .upload-area {
            border: 2px dashed #bfdbfe;
            border-radius: 13px;
            background: #f8fbff;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: .2s;
        }

        .upload-area:hover,
        .upload-area.dragover {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .upload-icon {
            width: 60px;
            height: 60px;
            background: #dbeafe;
            border-radius: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 27px;
            margin: 0 auto 15px;
        }

        .upload-area h3 {
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 7px;
        }

        .upload-area p {
            font-size: 11px;
            color: #94a3b8;
        }

        .upload-button {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 16px;
        }

        .selected-file {
            display: none;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 13px;
            margin-top: 15px;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .selected-file.show {
            display: flex;
        }

        .file-name {
            font-size: 12px;
            font-weight: 700;
            word-break: break-all;
        }

        .file-size {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .remove-file {
            border: none;
            background: #fee2e2;
            color: #dc2626;
            padding: 7px 10px;
            border-radius: 7px;
            cursor: pointer;
        }

        .btn-import {
            width: 100%;
            border: none;
            border-radius: 9px;
            padding: 13px;
            background: #2563eb;
            color: white;
            font-size: 12px;
            font-weight: 800;
            margin-top: 18px;
            cursor: pointer;
        }

        .btn-import:hover {
            background: #1d4ed8;
        }

        .btn-import:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        .template-button {
            display: block;
            text-align: center;
            text-decoration: none;
            padding: 12px;
            border: 1px solid #bfdbfe;
            border-radius: 9px;
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
            background: #eff6ff;
        }

        .template-button:hover {
            background: #dbeafe;
        }

        .instruction-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .instruction-list li {
            display: flex;
            gap: 10px;
            font-size: 12px;
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 14px;
        }

        .instruction-number {
            width: 23px;
            height: 23px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #eff6ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 800;
        }

        .preview-card {
            background: white;
            border: 1px solid #edf0f5;
            border-radius: 15px;
            padding: 22px;
            margin-top: 2px;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 17px;
        }

        .preview-header h3 {
            font-size: 15px;
            font-weight: 800;
        }

        .preview-badge {
            font-size: 10px;
            color: #2563eb;
            background: #eff6ff;
            padding: 6px 10px;
            border-radius: 20px;
        }

        .preview-wrapper {
            overflow-x: auto;
        }

        .preview-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .preview-table th {
            text-align: left;
            font-size: 10px;
            color: #94a3b8;
            padding: 12px;
            background: #f8fafc;
        }

        .preview-table td {
            font-size: 11px;
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .preview-empty {
            text-align: center;
            padding: 30px !important;
            color: #94a3b8;
        }

        .notice {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            border-radius: 10px;
            padding: 13px;
            font-size: 11px;
            line-height: 1.7;
            margin-top: 17px;
        }

        .import-result {
            display: none;
            margin-top: 15px;
            padding: 13px;
            border-radius: 9px;
            font-size: 12px;
            line-height: 1.6;
        }

        .import-result.show {
            display: block;
        }

        .import-result.success {
            background: #dcfce7;
            color: #166534;
        }

        .import-result.error {
            background: #fee2e2;
            color: #991b1b;
        }

        @media(max-width: 850px) {
            .import-grid {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width: 650px) {
            .import-stats {
                grid-template-columns: 1fr;
            }

            .import-card {
                padding: 17px;
            }
        }
    </style>

    <div class="import-page">

        <!-- STATISTIK -->
        <div class="import-stats">
            <div class="import-stat">
                <span>Total Data Barang</span>
                <strong>150</strong>
            </div>

            <div class="import-stat">
                <span>Impor Berhasil</span>
                <strong>12</strong>
            </div>

            <div class="import-stat">
                <span>Impor Terakhir</span>
                <strong style="font-size:16px;">Belum ada</strong>
            </div>
        </div>

        <div class="import-grid">

            <!-- UPLOAD FILE -->
            <div class="import-card">
                <div class="card-title">Upload File Excel</div>
                <div class="card-description">
                    Pilih file Excel yang berisi data inventaris barang untuk
                    dimasukkan ke dalam sistem Polmart.
                </div>

                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">📊</div>

                    <h3>Drag & drop file Excel di sini</h3>
                    <p>atau pilih file dari perangkat kamu</p>

                    <label for="excelFile" class="upload-button">
                        Pilih File Excel
                    </label>

                    <input
                        type="file"
                        id="excelFile"
                        accept=".xlsx,.xls,.csv"
                        hidden
                    >

                    <p style="margin-top:15px;">
                        Format: XLSX, XLS, CSV
                    </p>
                </div>

                <div class="selected-file" id="selectedFile">
                    <div>
                        <div class="file-name" id="fileName"></div>
                        <div class="file-size" id="fileSize"></div>
                    </div>

                    <button class="remove-file"
                            type="button"
                            onclick="removeFile()">
                        Hapus
                    </button>
                </div>

                <button class="btn-import"
                        id="importButton"
                        onclick="simulateImport()"
                        disabled>
                    Mulai Impor Data
                </button>

                <div class="import-result" id="importResult"></div>

                <div class="notice">
                    <strong>Perhatian:</strong> Pastikan file yang diunggah
                    sesuai dengan format template agar data dapat dibaca
                    dengan benar.
                </div>
            </div>

            <!-- PANDUAN -->
            <div>

                <div class="import-card">
                    <div class="card-title">Template Excel</div>

                    <div class="card-description">
                        Gunakan template yang sudah disediakan agar struktur
                        data sesuai dengan sistem.
                    </div>

                    <a href="#" class="template-button"
                       onclick="downloadTemplate(event)">
                        ↓ Download Template Excel
                    </a>
                </div>

                <div class="import-card">
                    <div class="card-title">Panduan Impor</div>

                    <div class="card-description">
                        Ikuti langkah berikut sebelum melakukan impor data.
                    </div>

                    <ul class="instruction-list">
                        <li>
                            <span class="instruction-number">1</span>
                            Download template Excel yang disediakan.
                        </li>

                        <li>
                            <span class="instruction-number">2</span>
                            Isi data barang sesuai kolom template.
                        </li>

                        <li>
                            <span class="instruction-number">3</span>
                            Simpan file dalam format XLSX, XLS, atau CSV.
                        </li>

                        <li>
                            <span class="instruction-number">4</span>
                            Upload file dan periksa data sebelum impor.
                        </li>

                        <li>
                            <span class="instruction-number">5</span>
                            Klik tombol Mulai Impor Data.
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- PREVIEW DATA -->
        <div class="preview-card">
            <div class="preview-header">
                <h3>Preview Data</h3>
                <span class="preview-badge" id="previewCount">
                    0 Data
                </span>
            </div>

            <div class="preview-wrapper">
                <table class="preview-table">
                    <thead>
                        <tr>
                            <th>KODE BARANG</th>
                            <th>NAMA BARANG</th>
                            <th>KATEGORI</th>
                            <th>STOK</th>
                            <th>HARGA</th>
                        </tr>
                    </thead>

                    <tbody id="previewBody">
                        <tr>
                            <td colspan="5" class="preview-empty">
                                Belum ada data untuk ditampilkan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        let selectedFile = null;

        const fileInput = document.getElementById('excelFile');
        const uploadArea = document.getElementById('uploadArea');

        fileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                handleFile(this.files[0]);
            }
        });

        uploadArea.addEventListener('dragover', function (event) {
            event.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function () {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function (event) {
            event.preventDefault();
            uploadArea.classList.remove('dragover');

            const file = event.dataTransfer.files[0];

            if (file) {
                handleFile(file);
            }
        });

        function handleFile(file) {
            const allowedExtensions = ['xlsx', 'xls', 'csv'];
            const extension = file.name.split('.').pop().toLowerCase();

            if (!allowedExtensions.includes(extension)) {
                alert('Format file tidak didukung. Pilih XLSX, XLS, atau CSV.');
                return;
            }

            selectedFile = file;

            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent =
                (file.size / 1024).toFixed(2) + ' KB';

            document.getElementById('selectedFile').classList.add('show');
            document.getElementById('importButton').disabled = false;

            document.getElementById('importResult').className = 'import-result';
            document.getElementById('importResult').textContent = '';

            previewCSV(file);
        }

        function removeFile() {
            selectedFile = null;
            fileInput.value = '';

            document.getElementById('selectedFile').classList.remove('show');
            document.getElementById('importButton').disabled = true;

            document.getElementById('previewBody').innerHTML = `
                <tr>
                    <td colspan="5" class="preview-empty">
                        Belum ada data untuk ditampilkan.
                    </td>
                </tr>
            `;

            document.getElementById('previewCount').textContent = '0 Data';
            document.getElementById('importResult').className = 'import-result';
        }

        function previewCSV(file) {
            const extension = file.name.split('.').pop().toLowerCase();

            if (extension !== 'csv') {
                document.getElementById('previewBody').innerHTML = `
                    <tr>
                        <td colspan="5" class="preview-empty">
                            Preview otomatis saat ini hanya mendukung CSV.
                            File Excel sudah dipilih, tetapi belum dibaca.
                        </td>
                    </tr>
                `;

                document.getElementById('previewCount').textContent =
                    'Preview belum tersedia';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                const lines = event.target.result.trim().split(/\r?\n/);

                if (lines.length < 2) {
                    alert('File CSV belum memiliki data.');
                    removeFile();
                    return;
                }

                const rows = lines.slice(1).map(line => line.split(','));

                const previewRows = rows.slice(0, 5);

                document.getElementById('previewBody').innerHTML =
                    previewRows.map(row => `
                        <tr>
                            <td>${escapeHTML(row[0] || '-')}</td>
                            <td>${escapeHTML(row[1] || '-')}</td>
                            <td>${escapeHTML(row[2] || '-')}</td>
                            <td>${escapeHTML(row[3] || '-')}</td>
                            <td>${escapeHTML(row[4] || '-')}</td>
                        </tr>
                    `).join('');

                document.getElementById('previewCount').textContent =
                    rows.length + ' Data';
            };

            reader.readAsText(file);
        }

        function escapeHTML(value) {
            return String(value).replace(/[&<>"']/g, character => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            })[character]);
        }

        function simulateImport() {
            if (!selectedFile) {
                alert('Silakan pilih file terlebih dahulu.');
                return;
            }

            const result = document.getElementById('importResult');

            result.className = 'import-result show success';
            result.innerHTML =
                '<strong>File berhasil dipilih!</strong><br>' +
                'Ini masih simulasi frontend. Data belum dimasukkan ke database Polmart.';
        }

        function downloadTemplate(event) {
            event.preventDefault();

            const csv = [
                'Kode Barang,Nama Barang,Kategori,Stok,Harga',
                'BRG001,Buku Tulis,Alat Tulis,50,5000',
                'BRG002,Pulpen Hitam,Alat Tulis,35,3000',
                'BRG003,Air Mineral,Minuman,40,4000',
                'BRG004,Penghapus,Alat Tulis,25,2000'
            ].join('\n');

            const blob = new Blob([csv], {
                type: 'text/csv;charset=utf-8;'
            });

            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = url;
            link.download = 'template-inventaris-polmart.csv';
            link.click();

            URL.revokeObjectURL(url);
        }
    </script>
</x-app-layout>
