
<x-app-layout>
    @section('title', 'Transaksi Walk-in / Kasir - Polmart')
    @section('page_title', 'Kasir Walk-in')

    <x-slot name="header">
        <h1>Transaksi Walk-in</h1>
        <p>Catat transaksi pembelian langsung di koperasi Polmart.</p>
    </x-slot>

    <style>
        .transaction-page {
            color: #172033;
        }

        .transaction-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 22px;
            align-items: start;
        }

        .transaction-card {
            background: white;
            border: 1px solid #edf0f5;
            border-radius: 16px;
            padding: 23px;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .card-subtitle {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #475569;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            font-size: 13px;
            outline: none;
            background: white;
            color: #334155;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px #dbeafe;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .product-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .product-item {
            border: 1px solid #edf0f5;
            border-radius: 12px;
            padding: 15px;
            transition: .2s;
        }

        .product-item:hover {
            border-color: #93c5fd;
            background: #f8fbff;
        }

        .product-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            margin-bottom: 12px;
        }

        .product-name {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .product-stock {
            font-size: 11px;
            color: #94a3b8;
            margin-bottom: 12px;
        }

        .product-price {
            font-size: 13px;
            font-weight: 800;
            color: #2563eb;
            margin-bottom: 12px;
        }

        .btn-add {
            width: 100%;
            padding: 9px;
            border: none;
            border-radius: 8px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
        }

        .btn-add:hover {
            background: #2563eb;
            color: white;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table th {
            text-align: left;
            padding: 12px 8px;
            font-size: 10px;
            color: #94a3b8;
            border-bottom: 1px solid #edf0f5;
        }

        .cart-table td {
            padding: 13px 8px;
            font-size: 11px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .qty-input {
            width: 55px;
            padding: 7px;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            text-align: center;
        }

        .btn-remove {
            border: none;
            background: #fee2e2;
            color: #dc2626;
            padding: 6px 9px;
            border-radius: 6px;
            cursor: pointer;
        }

        .empty-cart {
            text-align: center;
            padding: 30px 10px;
            color: #94a3b8;
            font-size: 12px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 13px;
            font-size: 13px;
            color: #64748b;
        }

        .summary-total {
            border-top: 1px dashed #e2e8f0;
            padding-top: 17px;
            margin-top: 17px;
            font-size: 16px;
            font-weight: 800;
            color: #172033;
        }

        .btn-checkout {
            width: 100%;
            border: none;
            background: #2563eb;
            color: white;
            padding: 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            margin-top: 12px;
        }

        .btn-checkout:hover {
            background: #1d4ed8;
        }

        .btn-clear {
            width: 100%;
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            padding: 11px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 9px;
        }

        .cart-wrapper {
            overflow-x: auto;
        }

        @media(max-width: 950px) {
            .transaction-grid {
                grid-template-columns: 1fr;
            }
        }

        @media(max-width: 550px) {
            .product-list {
                grid-template-columns: 1fr 1fr;
            }

            .transaction-card {
                padding: 16px;
            }
        }
    </style>

    <div class="transaction-page">

        <div class="transaction-grid">

            <!-- BAGIAN KIRI -->
            <div>

                <!-- INFORMASI PEMBELI -->
                <div class="transaction-card">
                    <div class="card-title">Informasi Pembeli</div>
                    <div class="card-subtitle">
                        Masukkan informasi pelanggan yang melakukan pembelian.
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Pembeli</label>
                        <input
                            type="text"
                            id="buyerName"
                            class="form-control"
                            placeholder="Masukkan nama pembeli"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Metode Pembayaran</label>
                        <select id="paymentMethod" class="form-control">
                            <option value="Tunai">Tunai</option>
                            <option value="QRIS">QRIS</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>
                </div>

                <!-- PILIH PRODUK -->
                <div class="transaction-card">
                    <div class="card-title">Pilih Produk</div>
                    <div class="card-subtitle">
                        Pilih produk yang ingin dibeli pelanggan.
                    </div>

                    <div class="form-group">
                        <input
                            type="text"
                            id="searchProduct"
                            class="form-control"
                            placeholder="Cari nama produk..."
                            oninput="searchProducts()"
                        >
                    </div>

                    <div class="product-list" id="productList">

                        <div class="product-item" data-name="Buku Tulis">
                            <div class="product-icon">📒</div>
                            <div class="product-name">Buku Tulis</div>
                            <div class="product-stock">Stok: 50 unit</div>
                            <div class="product-price">Rp 5.000</div>
                            <button class="btn-add" onclick="addProduct(1)">
                                + Tambahkan
                            </button>
                        </div>

                        <div class="product-item" data-name="Pulpen Hitam">
                            <div class="product-icon">🖊️</div>
                            <div class="product-name">Pulpen Hitam</div>
                            <div class="product-stock">Stok: 35 unit</div>
                            <div class="product-price">Rp 3.000</div>
                            <button class="btn-add" onclick="addProduct(2)">
                                + Tambahkan
                            </button>
                        </div>

                        <div class="product-item" data-name="Air Mineral">
                            <div class="product-icon">💧</div>
                            <div class="product-name">Air Mineral</div>
                            <div class="product-stock">Stok: 40 unit</div>
                            <div class="product-price">Rp 4.000</div>
                            <button class="btn-add" onclick="addProduct(3)">
                                + Tambahkan
                            </button>
                        </div>

                        <div class="product-item" data-name="Penghapus">
                            <div class="product-icon">🧽</div>
                            <div class="product-name">Penghapus</div>
                            <div class="product-stock">Stok: 25 unit</div>
                            <div class="product-price">Rp 2.000</div>
                            <button class="btn-add" onclick="addProduct(4)">
                                + Tambahkan
                            </button>
                        </div>

                    </div>
                </div>

            </div>

            <!-- BAGIAN KANAN -->
            <div>

                <!-- KERANJANG -->
                <div class="transaction-card">
                    <div class="card-title">Keranjang Belanja</div>
                    <div class="card-subtitle">
                        Daftar produk yang dipilih pelanggan.
                    </div>

                    <div class="cart-wrapper">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody id="cartItems">
                                <tr>
                                    <td colspan="5" class="empty-cart">
                                        Keranjang masih kosong.
                                        <br>
                                        Silakan pilih produk terlebih dahulu.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <button class="btn-clear" onclick="clearCart()">
                        Kosongkan Keranjang
                    </button>
                </div>

                <!-- RINGKASAN -->
                <div class="transaction-card">
                    <div class="card-title">Ringkasan Pembayaran</div>
                    <div class="card-subtitle">
                        Total pembayaran transaksi pelanggan.
                    </div>

                    <div class="summary-row">
                        <span>Total Item</span>
                        <strong id="totalItems">0</strong>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong id="subtotal">Rp 0</strong>
                    </div>

                    <div class="summary-row summary-total">
                        <span>Total Pembayaran</span>
                        <strong id="totalPayment">Rp 0</strong>
                    </div>

                    <button class="btn-checkout" onclick="checkout()">
                        Selesaikan Transaksi
                    </button>
                </div>

            </div>

        </div>

    </div>

    <script>
        const products = [
            { id: 1, name: 'Buku Tulis', price: 5000, stock: 50 },
            { id: 2, name: 'Pulpen Hitam', price: 3000, stock: 35 },
            { id: 3, name: 'Air Mineral', price: 4000, stock: 40 },
            { id: 4, name: 'Penghapus', price: 2000, stock: 25 }
        ];

        let cart = [];

        function formatRupiah(number) {
            return 'Rp ' + number.toLocaleString('id-ID');
        }

        function addProduct(id) {
            const product = products.find(item => item.id === id);
            const existing = cart.find(item => item.id === id);

            if (existing) {
                if (existing.qty >= product.stock) {
                    alert('Jumlah produk melebihi stok yang tersedia.');
                    return;
                }

                existing.qty++;
            } else {
                cart.push({
                    ...product,
                    qty: 1
                });
            }

            renderCart();
        }

        function renderCart() {
            const cartItems = document.getElementById('cartItems');

            if (cart.length === 0) {
                cartItems.innerHTML = `
                    <tr>
                        <td colspan="5" class="empty-cart">
                            Keranjang masih kosong.<br>
                            Silakan pilih produk terlebih dahulu.
                        </td>
                    </tr>
                `;
            } else {
                cartItems.innerHTML = cart.map(item => `
                    <tr>
                        <td><strong>${item.name}</strong></td>
                        <td>${formatRupiah(item.price)}</td>
                        <td>
                            <input
                                type="number"
                                class="qty-input"
                                min="1"
                                max="${item.stock}"
                                value="${item.qty}"
                                onchange="updateQty(${item.id}, this.value)"
                            >
                        </td>
                        <td><strong>${formatRupiah(item.price * item.qty)}</strong></td>
                        <td>
                            <button
                                class="btn-remove"
                                onclick="removeProduct(${item.id})"
                            >×</button>
                        </td>
                    </tr>
                `).join('');
            }

            const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
            const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);

            document.getElementById('totalItems').textContent = totalQty;
            document.getElementById('subtotal').textContent = formatRupiah(total);
            document.getElementById('totalPayment').textContent = formatRupiah(total);
        }

        function updateQty(id, value) {
            const item = cart.find(product => product.id === id);
            const product = products.find(product => product.id === id);
            const qty = parseInt(value);

            if (!item || !Number.isInteger(qty) || qty < 1) {
                renderCart();
                return;
            }

            if (qty > product.stock) {
                alert('Jumlah melebihi stok yang tersedia.');
                renderCart();
                return;
            }

            item.qty = qty;
            renderCart();
        }

        function removeProduct(id) {
            cart = cart.filter(item => item.id !== id);
            renderCart();
        }

        function clearCart() {
            if (cart.length === 0) return;

            if (confirm('Yakin ingin mengosongkan keranjang?')) {
                cart = [];
                renderCart();
            }
        }

        function searchProducts() {
            const keyword = document.getElementById('searchProduct')
                .value.toLowerCase();

            document.querySelectorAll('.product-item').forEach(item => {
                const name = item.dataset.name.toLowerCase();
                item.style.display = name.includes(keyword) ? 'block' : 'none';
            });
        }

        function checkout() {
            const buyer = document.getElementById('buyerName').value.trim();
            const payment = document.getElementById('paymentMethod').value;

            if (!buyer) {
                alert('Silakan masukkan nama pembeli terlebih dahulu.');
                return;
            }

            if (cart.length === 0) {
                alert('Keranjang belanja masih kosong.');
                return;
            }

            const total = cart.reduce(
                (sum, item) => sum + (item.price * item.qty), 0
            );

            alert(
                'Simulasi transaksi berhasil dibuat!\n\n' +
                'Pembeli: ' + buyer + '\n' +
                'Pembayaran: ' + payment + '\n' +
                'Total: ' + formatRupiah(total) +
                '\n\nCatatan: Transaksi ini belum tersimpan ke database.'
            );
        }
    </script>
</x-app-layout>
