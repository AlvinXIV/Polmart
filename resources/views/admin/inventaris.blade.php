
<x-app-layout>
    @section('title', 'Inventaris & Master Barang - Polmart')
    @section('page_title', 'Master Inventaris')

<style>
    .inventory-page {
        display: flex;
        flex-direction: column;
        gap: 25px;
        color: #172033;
    }

    .inventory-hero {
        background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
        padding: 30px 34px;
        border-radius: 18px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.3);
    }

    .inventory-hero h1 {
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 7px;
    }

    .inventory-hero p {
        color: #e5efff;
        font-size: 12px;
    }

    .hero-icon { font-size: 65px; }

    .inventory-button {
        border: none;
        border-radius: 10px;
        padding: 11px 17px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: .2s;
    }

    .button-primary {
        background: #2563eb;
        color: white;
    }

    .button-primary:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
    }

    .button-white {
        background: white;
        color: #2563eb;
        margin-top: 17px;
    }

    .inventory-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
    }

    .inventory-stat {
        background: white;
        padding: 18px;
        border: 1px solid #edf0f5;
        border-radius: 14px;
    }

    .inventory-stat span {
        color: #94a3b8;
        font-size: 11px;
    }

    .inventory-stat h2 {
        font-size: 25px;
        font-weight: 800;
        margin-top: 10px;
    }

    .inventory-stat small {
        font-size: 10px;
        color: #94a3b8;
    }

    .inventory-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .inventory-search {
        display: flex;
        align-items: center;
        gap: 9px;
        background: white;
        border: 1px solid #e5eaf1;
        padding: 11px 13px;
        border-radius: 10px;
        width: 280px;
    }

    .inventory-search input {
        border: none;
        outline: none;
        width: 100%;
        font-size: 12px;
    }

    .inventory-filter {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    .inventory-filter select {
        border: 1px solid #e5eaf1;
        border-radius: 9px;
        background: white;
        padding: 10px 12px;
        font-size: 11px;
        color: #475569;
        outline: none;
    }

    .inventory-products {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 17px;
    }

    .product-card {
        background: white;
        border: 1px solid #edf0f5;
        border-radius: 15px;
        overflow: hidden;
        transition: .25s;
    }

    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(15,23,42,.07);
    }

    .product-image {
        height: 175px;
        background: #f1f5f9;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-placeholder { font-size: 65px; }

    .product-status {
        position: absolute;
        top: 12px;
        left: 12px;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 700;
    }

    .status-available {
        color: #15803d;
        background: #dcfce7;
    }

    .status-low {
        color: #b45309;
        background: #fef3c7;
    }

    .status-empty {
        color: #dc2626;
        background: #fee2e2;
    }

    .product-info { padding: 16px; }

    .product-category {
        color: #2563eb;
        font-size: 10px;
        font-weight: 700;
    }

    .product-info h3 {
        font-size: 14px;
        font-weight: 700;
        margin: 7px 0;
    }

    .product-sku {
        color: #94a3b8;
        font-size: 10px;
    }

    .product-price {
        font-size: 17px;
        font-weight: 800;
        margin: 13px 0 8px;
    }

    .product-stock {
        display: flex;
        justify-content: space-between;
        color: #64748b;
        font-size: 10px;
        padding-bottom: 13px;
        border-bottom: 1px solid #f1f5f9;
    }

    .product-actions {
        display: flex;
        gap: 8px;
        margin-top: 13px;
    }

    .product-actions button {
        flex: 1;
        padding: 9px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        border: none;
    }

    .edit-button {
        color: #2563eb;
        background: #eff6ff;
    }

    .stock-button {
        color: #15803d;
        background: #dcfce7;
    }

    .inventory-modal {
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,.5);
        z-index: 200;
        display: none;
        justify-content: center;
        align-items: center;
        padding: 15px;
    }

    .inventory-modal.show { display: flex; }

    .modal-content {
        background: white;
        width: 100%;
        max-width: 480px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 17px;
        padding: 25px;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .modal-header h2 {
        font-size: 18px;
        font-weight: 800;
    }

    .modal-close {
        border: none;
        background: #f1f5f9;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        cursor: pointer;
    }

    .form-group { margin-bottom: 15px; }

    .form-group label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 7px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 11px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        font-size: 12px;
        outline: none;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #2563eb;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 20px;
    }

    .button-cancel {
        background: #f1f5f9;
        color: #475569;
    }

    .empty-message {
        display: none;
        text-align: center;
        padding: 40px;
        background: white;
        border-radius: 15px;
        color: #94a3b8;
    }

    @media(max-width: 1000px) {
        .inventory-products { grid-template-columns: repeat(2, 1fr); }
        .inventory-stats { grid-template-columns: repeat(2, 1fr); }
    }

    @media(max-width: 600px) {
        .inventory-products { grid-template-columns: 1fr; }
        .inventory-hero { padding: 22px; }
        .hero-icon { font-size: 40px; }
        .inventory-search { width: 100%; }
    }
</style>

<div class="inventory-page">

    <section class="inventory-hero">
        <div>
            <h1>Inventaris Barang 📦</h1>
            <p>Kelola produk, pantau stok, dan atur persediaan Polmart.</p>

            <button class="inventory-button button-white"
                    onclick="openProductModal()">
                + Tambah Barang
            </button>
        </div>

        <div class="hero-icon">🛍️</div>
    </section>

    <section class="inventory-stats">
        <div class="inventory-stat">
            <span>Total Produk</span>
            <h2 id="totalProducts">0</h2>
            <small>Produk terdaftar</small>
        </div>

        <div class="inventory-stat">
            <span>Stok Tersedia</span>
            <h2 id="availableProducts">0</h2>
            <small>Produk dengan stok aman</small>
        </div>

        <div class="inventory-stat">
            <span>Stok Menipis</span>
            <h2 id="lowProducts">0</h2>
            <small>Perlu restock</small>
        </div>

        <div class="inventory-stat">
            <span>Stok Habis</span>
            <h2 id="emptyProducts">0</h2>
            <small>Produk tidak tersedia</small>
        </div>
    </section>

    <section>
        <div style="margin-bottom:17px;">
            <h2 style="font-size:17px;font-weight:800;">Katalog Produk</h2>
            <p style="font-size:11px;color:#94a3b8;margin-top:5px;">
                Daftar seluruh produk yang dikelola koperasi
            </p>
        </div>

        <div class="inventory-toolbar">
            <div class="inventory-search">
                🔍
                <input type="text" id="searchProduct"
                       placeholder="Cari nama atau SKU barang..."
                       oninput="filterProducts()">
            </div>

            <div class="inventory-filter">
                <select id="categoryFilter" onchange="filterProducts()">
                    <option value="">Semua Kategori</option>
                    <option value="ATK">ATK</option>
                    <option value="Minuman">Minuman</option>
                    <option value="Makanan">Makanan</option>
                </select>

                <select id="stockFilter" onchange="filterProducts()">
                    <option value="">Semua Stok</option>
                    <option value="available">Tersedia</option>
                    <option value="low">Menipis</option>
                    <option value="empty">Habis</option>
                </select>
            </div>
        </div>
    </section>

    <section class="inventory-products" id="productList">

        <article class="product-card"
                 data-name="Buku Tulis"
                 data-sku="ATK001"
                 data-category="ATK"
                 data-stock="120"
                 data-price="5000">

            <div class="product-image">
                <span class="product-placeholder">📓</span>
                <span class="product-status"></span>
            </div>

            <div class="product-info">
                <span class="product-category">ATK</span>
                <h3>Buku Tulis</h3>
                <p class="product-sku">SKU: ATK001</p>
                <div class="product-price">Rp 5.000</div>

                <div class="product-stock">
                    <span>Jumlah stok</span>
                    <strong>120 pcs</strong>
                </div>

                <div class="product-actions">
                    <button class="edit-button" onclick="editProduct(this)">✏️ Edit</button>
                    <button class="stock-button" onclick="restockProduct(this)">+ Restock</button>
                </div>
            </div>
        </article>

        <article class="product-card"
                 data-name="Pulpen Hitam"
                 data-sku="ATK002"
                 data-category="ATK"
                 data-stock="8"
                 data-price="3000">

            <div class="product-image">
                <span class="product-placeholder">🖊️</span>
                <span class="product-status"></span>
            </div>

            <div class="product-info">
                <span class="product-category">ATK</span>
                <h3>Pulpen Hitam</h3>
                <p class="product-sku">SKU: ATK002</p>
                <div class="product-price">Rp 3.000</div>

                <div class="product-stock">
                    <span>Jumlah stok</span>
                    <strong>8 pcs</strong>
                </div>

                <div class="product-actions">
                    <button class="edit-button" onclick="editProduct(this)">✏️ Edit</button>
                    <button class="stock-button" onclick="restockProduct(this)">+ Restock</button>
                </div>
            </div>
        </article>

        <article class="product-card"
                 data-name="Air Mineral"
                 data-sku="MNM001"
                 data-category="Minuman"
                 data-stock="45"
                 data-price="4000">

            <div class="product-image">
                <span class="product-placeholder">🥤</span>
                <span class="product-status"></span>
            </div>

            <div class="product-info">
                <span class="product-category">Minuman</span>
                <h3>Air Mineral</h3>
                <p class="product-sku">SKU: MNM001</p>
                <div class="product-price">Rp 4.000</div>

                <div class="product-stock">
                    <span>Jumlah stok</span>
                    <strong>45 pcs</strong>
                </div>

                <div class="product-actions">
                    <button class="edit-button" onclick="editProduct(this)">✏️ Edit</button>
                    <button class="stock-button" onclick="restockProduct(this)">+ Restock</button>
                </div>
            </div>
        </article>

        <article class="product-card"
                 data-name="Penghapus"
                 data-sku="ATK003"
                 data-category="ATK"
                 data-stock="0"
                 data-price="2000">

            <div class="product-image">
                <span class="product-placeholder">🧽</span>
                <span class="product-status"></span>
            </div>

            <div class="product-info">
                <span class="product-category">ATK</span>
                <h3>Penghapus</h3>
                <p class="product-sku">SKU: ATK003</p>
                <div class="product-price">Rp 2.000</div>

                <div class="product-stock">
                    <span>Jumlah stok</span>
                    <strong>0 pcs</strong>
                </div>

                <div class="product-actions">
                    <button class="edit-button" onclick="editProduct(this)">✏️ Edit</button>
                    <button class="stock-button" onclick="restockProduct(this)">+ Restock</button>
                </div>
            </div>
        </article>

    </section>

    <div class="empty-message" id="emptyMessage">
        <div style="font-size:35px;">🔍</div>
        <h3 style="font-weight:700;margin:10px;">Produk tidak ditemukan</h3>
        <p style="font-size:12px;">Coba gunakan kata kunci lain.</p>
    </div>

</div>

<!-- MODAL TAMBAH / EDIT BARANG -->
<div class="inventory-modal" id="productModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Tambah Barang</h2>
            <button class="modal-close" onclick="closeModal()">✕</button>
        </div>

        <form id="productForm" onsubmit="saveProduct(event)">
            <div class="form-group">
                <label>Kode Barang (SKU)</label>
                <input type="text" id="productSku" required placeholder="Contoh: ATK004">
            </div>

            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" id="productName" required placeholder="Masukkan nama barang">
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <select id="productCategory" required>
                    <option value="">Pilih kategori</option>
                    <option value="ATK">ATK</option>
                    <option value="Minuman">Minuman</option>
                    <option value="Makanan">Makanan</option>
                </select>
            </div>

            <div class="form-group">
                <label>Harga Satuan (Rp)</label>
                <input type="number" id="productPrice" min="0" required>
            </div>

            <div class="form-group">
                <label>Jumlah Stok</label>
                <input type="number" id="productStock" min="0" required>
            </div>

            <div class="form-group">
                <label>Foto Produk</label>
                <input type="file" id="productPhoto" accept="image/*">
            </div>

            <div class="modal-footer">
                <button type="button" class="inventory-button button-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="inventory-button button-primary">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL RESTOCK -->
<div class="inventory-modal" id="restockModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Tambah Stok</h2>
            <button class="modal-close" onclick="closeRestock()">✕</button>
        </div>

        <div class="form-group">
            <label>Nama Barang</label>
            <input type="text" id="restockName" readonly>
        </div>

        <div class="form-group">
            <label>Jumlah Stok Tambahan</label>
            <input type="number" id="restockAmount" min="1" placeholder="Masukkan jumlah">
        </div>

        <div class="modal-footer">
            <button class="inventory-button button-cancel" onclick="closeRestock()">Batal</button>
            <button class="inventory-button button-primary" onclick="saveRestock()">Simpan Stok</button>
        </div>
    </div>
</div>

<script>
    let editingCard = null;
    let selectedCard = null;

    function openProductModal() {
        editingCard = null;
        document.getElementById('productForm').reset();
        document.getElementById('modalTitle').innerText = 'Tambah Barang';
        document.getElementById('productModal').classList.add('show');
    }

    function closeModal() {
        document.getElementById('productModal').classList.remove('show');
    }

    function closeRestock() {
        document.getElementById('restockModal').classList.remove('show');
    }

    function getStatus(stock) {
        if (stock <= 0) return ['empty', 'Stok Habis'];
        if (stock <= 10) return ['low', 'Stok Menipis'];
        return ['available', 'Tersedia'];
    }

    function updateCard(card) {
        const stock = Number(card.dataset.stock);
        const [status, label] = getStatus(stock);

        const statusElement = card.querySelector('.product-status');
        statusElement.className = 'product-status status-' + status;
        statusElement.textContent = label;

        card.querySelector('.product-stock strong').textContent = stock + ' pcs';
        updateStatistics();
    }

    function updateStatistics() {
        const cards = [...document.querySelectorAll('.product-card')];

        document.getElementById('totalProducts').textContent = cards.length;

        document.getElementById('availableProducts').textContent =
            cards.filter(c => Number(c.dataset.stock) > 10).length;

        document.getElementById('lowProducts').textContent =
            cards.filter(c => Number(c.dataset.stock) > 0 &&
                Number(c.dataset.stock) <= 10).length;

        document.getElementById('emptyProducts').textContent =
            cards.filter(c => Number(c.dataset.stock) === 0).length;
    }

    function filterProducts() {
        const search = document.getElementById('searchProduct').value.toLowerCase();
        const category = document.getElementById('categoryFilter').value;
        const stockFilter = document.getElementById('stockFilter').value;

        let visible = 0;

        document.querySelectorAll('.product-card').forEach(card => {
            const name = card.dataset.name.toLowerCase();
            const sku = card.dataset.sku.toLowerCase();
            const cardCategory = card.dataset.category;
            const stock = Number(card.dataset.stock);

            const status = stock <= 0 ? 'empty' :
                stock <= 10 ? 'low' : 'available';

            const matchSearch = name.includes(search) || sku.includes(search);
            const matchCategory = !category || category === cardCategory;
            const matchStock = !stockFilter || stockFilter === status;

            const show = matchSearch && matchCategory && matchStock;
            card.style.display = show ? '' : 'none';

            if (show) visible++;
        });

        document.getElementById('emptyMessage').style.display =
            visible === 0 ? 'block' : 'none';
    }

    function editProduct(button) {
        editingCard = button.closest('.product-card');

        document.getElementById('modalTitle').innerText = 'Edit Barang';
        document.getElementById('productSku').value = editingCard.dataset.sku;
        document.getElementById('productName').value = editingCard.dataset.name;
        document.getElementById('productCategory').value = editingCard.dataset.category;
        document.getElementById('productStock').value = editingCard.dataset.stock;
        document.getElementById('productPrice').value = editingCard.dataset.price;

        document.getElementById('productModal').classList.add('show');
    }

    function saveProduct(event) {
        event.preventDefault();

        const sku = document.getElementById('productSku').value.trim();
        const name = document.getElementById('productName').value.trim();
        const category = document.getElementById('productCategory').value;
        const price = Number(document.getElementById('productPrice').value);
        const stock = Number(document.getElementById('productStock').value);

        if (!sku || !name || !category || price < 0 || stock < 0) {
            alert('Periksa kembali data barang.');
            return;
        }

        const existing = [...document.querySelectorAll('.product-card')]
            .find(card =>
                card.dataset.sku.toLowerCase() === sku.toLowerCase() &&
                card !== editingCard
            );

        if (existing) {
            alert('SKU sudah digunakan oleh produk lain.');
            return;
        }

        if (editingCard) {
            editingCard.dataset.sku = sku;
            editingCard.dataset.name = name;
            editingCard.dataset.category = category;
            editingCard.dataset.stock = stock;
            editingCard.dataset.price = price;

            editingCard.querySelector('.product-category').textContent = category;
            editingCard.querySelector('h3').textContent = name;
            editingCard.querySelector('.product-sku').textContent = 'SKU: ' + sku;
            editingCard.querySelector('.product-price').textContent =
                'Rp ' + price.toLocaleString('id-ID');

            updateCard(editingCard);

        } else {
            const card = document.createElement('article');

            card.className = 'product-card';
            card.dataset.name = name;
            card.dataset.sku = sku;
            card.dataset.category = category;
            card.dataset.stock = stock;
            card.dataset.price = price;

            card.innerHTML = `
                <div class="product-image">
                    <span class="product-placeholder">📦</span>
                    <span class="product-status"></span>
                </div>
                <div class="product-info">
                    <span class="product-category"></span>
                    <h3></h3>
                    <p class="product-sku"></p>
                    <div class="product-price"></div>
                    <div class="product-stock">
                        <span>Jumlah stok</span>
                        <strong></strong>
                    </div>
                    <div class="product-actions">
                        <button class="edit-button" onclick="editProduct(this)">✏️ Edit</button>
                        <button class="stock-button" onclick="restockProduct(this)">+ Restock</button>
                    </div>
                </div>
            `;

            card.querySelector('.product-category').textContent = category;
            card.querySelector('h3').textContent = name;
            card.querySelector('.product-sku').textContent = 'SKU: ' + sku;
            card.querySelector('.product-price').textContent =
                'Rp ' + price.toLocaleString('id-ID');

            document.getElementById('productList').appendChild(card);
            updateCard(card);
        }

        closeModal();
        document.getElementById('productForm').reset();
        filterProducts();
        updateStatistics();

        alert('Data barang berhasil diperbarui pada tampilan demo.');
    }

    function restockProduct(button) {
        selectedCard = button.closest('.product-card');

        document.getElementById('restockName').value = selectedCard.dataset.name;
        document.getElementById('restockAmount').value = '';

        document.getElementById('restockModal').classList.add('show');
    }

    function saveRestock() {
        const amount = Number(document.getElementById('restockAmount').value);

        if (!amount || amount < 1) {
            alert('Masukkan jumlah stok yang valid.');
            return;
        }

        selectedCard.dataset.stock =
            Number(selectedCard.dataset.stock) + amount;

        updateCard(selectedCard);
        closeRestock();
        filterProducts();

        alert('Stok berhasil diperbarui pada tampilan demo.');
    }

    document.querySelectorAll('.product-card').forEach(card => {
        updateCard(card);
    });
</script>

</x-app-layout>
