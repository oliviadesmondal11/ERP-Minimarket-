<div class="space-y-6">

    <!-- POS Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-xl shadow">
                POS
            </div>
            <div>
                <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold">
                    <span>Point of Sale</span>
                    <span>/</span>
                    <span class="text-emerald-700 font-bold">STORE-PKU (Pekanbaru)</span>
                </div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center space-x-2">
                    <span>Register POS01 — Sesi Kasir Aktif</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                        OPEN
                    </span>
                </h1>
            </div>
        </div>

        <div class="flex items-center space-x-3 text-xs">
            <div class="text-right">
                <span class="block text-slate-400">Modal Awal Sesi:</span>
                <span class="font-mono font-bold text-slate-800 text-sm">Rp 200.000</span>
            </div>
            <a href="?page=pos_close" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-bold transition shadow-sm">
                🔒 Tutup Sesi & Hitung Kas (S-16) →
            </a>
        </div>
    </div>

    <!-- Alert / Messages -->
    <?php if (!empty($_GET['error'])): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-900 text-xs font-bold">❌ <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 text-xs font-bold">✅ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <!-- POS Main Screen: Products Grid (Left) + Cart Basket (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Products Grid (8 Cols) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
            <!-- Search & Filter Bar -->
            <div class="flex items-center space-x-3">
                <div class="relative flex-1">
                    <input type="text" id="posSearch" onkeyup="filterPosProducts()" placeholder="Ketik nama produk atau scan barcode..." 
                           class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none">
                    <span class="absolute left-3 top-2.5 text-slate-400">🔍</span>
                </div>
                <button onclick="setTestT29()" class="px-3 py-2 bg-purple-100 text-purple-800 rounded-xl text-[11px] font-bold hover:bg-purple-200 transition" title="Isi keranjang otomatis dengan skenario T-29 (3 AIR-600 + 2 MIE-GOR-80)">
                    ⚡ Quick Test T-29
                </button>
            </div>

            <!-- Product Cards Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[600px] overflow-y-auto pr-1" id="productGrid">
                <?php foreach ($products as $p): 
                    $storeAvail = (int)($storeStock[$p['id']] ?? 0);
                ?>
                    <div class="product-item p-3.5 rounded-xl border border-slate-200 hover:border-emerald-500 hover:shadow-md cursor-pointer transition flex flex-col justify-between bg-slate-50/50 hover:bg-white"
                         data-name="<?= strtolower($p['name']) ?>"
                         data-sku="<?= strtolower($p['sku']) ?>"
                         onclick="addToCart(<?= $p['id'] ?>, '<?= $p['sku'] ?>', '<?= addslashes($p['name']) ?>', <?= $p['sales_price'] ?>, <?= $storeAvail ?>)">
                        <div>
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-mono text-[10px] font-bold text-slate-500"><?= htmlspecialchars($p['sku']) ?></span>
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded <?= $storeAvail > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                                    Avail: <?= $storeAvail ?>
                                </span>
                            </div>
                            <h4 class="font-bold text-slate-900 text-xs leading-snug line-clamp-2"><?= htmlspecialchars($p['name']) ?></h4>
                            <span class="text-[10px] text-slate-400"><?= htmlspecialchars($p['size']) ?> • <?= htmlspecialchars($p['flavor']) ?></span>
                        </div>
                        <div class="mt-3 pt-2 border-t border-slate-100 flex justify-between items-baseline">
                            <span class="font-mono font-black text-emerald-700 text-xs"><?= App\Helpers::formatRupiah($p['sales_price']) ?></span>
                            <span class="text-[10px] text-slate-400 font-mono">+Tambah</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Basket / Checkout (5 Cols) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4 sticky top-20">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <h3 class="font-bold text-slate-900 text-sm flex items-center space-x-1.5">
                    <span>🛒 Keranjang Kasir (Basket)</span>
                </h3>
                <button onclick="clearCart()" class="text-xs text-rose-600 font-bold hover:underline">Kosongkan</button>
            </div>

            <!-- Cart Table Items -->
            <div class="max-h-[300px] overflow-y-auto space-y-2" id="cartContainer">
                <div class="text-center py-10 text-slate-400 text-xs" id="emptyCartNotice">
                    Keranjang belanja masih kosong.<br>Klik produk di sebelah kiri untuk menambahkan.
                </div>
            </div>

            <!-- Cart Summary & Calculation -->
            <div class="pt-3 border-t border-slate-200 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Netto:</span>
                    <span class="font-mono font-bold" id="txtNet">Rp 0</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>PPN Efektif 11% (half-up):</span>
                    <span class="font-mono font-bold" id="txtVat">Rp 0</span>
                </div>
                <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total Pembayaran:</span>
                    <span class="font-mono text-emerald-700 text-lg" id="txtGross">Rp 0</span>
                </div>
            </div>

            <!-- Checkout Form & Submission -->
            <form method="POST" action="?action=pos_checkout" id="posCheckoutForm" class="space-y-3 pt-2">
                <input type="hidden" name="cart_data" id="cartDataJson">
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Uang Diterima dari Pelanggan (Cash Tendered)</label>
                    <input type="number" name="cash_tendered" id="inputCashTendered" onkeyup="calcChange()" placeholder="Contoh: 25000" required 
                           class="w-full px-3 py-2.5 text-sm font-mono font-black border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>
                <div class="p-3 bg-slate-50 rounded-xl flex justify-between items-center text-xs">
                    <span class="text-slate-500 font-bold">Uang Kembalian (Change):</span>
                    <span class="font-mono font-black text-slate-900 text-sm" id="txtChange">Rp 0</span>
                </div>

                <!-- Error Notice for Negative Stock (SR-04) -->
                <div id="stockErrorNotice" class="hidden p-3 bg-rose-50 border border-rose-300 rounded-xl text-rose-800 text-xs font-bold">
                    ⚠️ Aturan SR-04: Transaksi ditolak! Kuantitas keranjang melebihi stok Available di STORE-PKU.
                </div>

                <button type="submit" id="btnPay" disabled 
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white rounded-xl font-black text-sm shadow-md transition">
                    Selesaikan Pembayaran & Cetak Struk (T-29)
                </button>
            </form>
        </div>

    </div>

</div>

<script>
let cart = {};

function addToCart(id, sku, name, price, maxAvail) {
    if (!cart[id]) {
        cart[id] = { id, sku, name, price, qty: 1, maxAvail };
    } else {
        cart[id].qty += 1;
    }
    renderCart();
}

function updateQty(id, delta) {
    if (cart[id]) {
        cart[id].qty += delta;
        if (cart[id].qty <= 0) {
            delete cart[id];
        }
    }
    renderCart();
}

function clearCart() {
    cart = {};
    renderCart();
}

function setTestT29() {
    cart = {};
    // AIR-600 id: 1 (qty: 3, price: 4000)
    // MIE-GOR-80 id: 8 (qty: 2, price: 4200)
    cart[1] = { id: 1, sku: 'AIR-600', name: 'Air mineral Nusa 600ml', price: 4000, qty: 3, maxAvail: 40 };
    cart[8] = { id: 8, sku: 'MIE-GOR-80', name: 'Mi instan Hemat Goreng', price: 4200, qty: 2, maxAvail: 60 };
    renderCart();
    document.getElementById('inputCashTendered').value = 25000;
    calcChange();
}

function renderCart() {
    const container = document.getElementById('cartContainer');
    const emptyNotice = document.getElementById('emptyCartNotice');
    const keys = Object.keys(cart);

    if (keys.length === 0) {
        container.innerHTML = '<div class="text-center py-10 text-slate-400 text-xs">Keranjang belanja kosong.</div>';
        document.getElementById('txtNet').innerText = 'Rp 0';
        document.getElementById('txtVat').innerText = 'Rp 0';
        document.getElementById('txtGross').innerText = 'Rp 0';
        document.getElementById('btnPay').disabled = true;
        document.getElementById('cartDataJson').value = '';
        return;
    }

    let html = '';
    let netTotal = 0;
    let hasStockViolation = false;

    keys.forEach(k => {
        const item = cart[k];
        const lineTotal = item.qty * item.price;
        netTotal += lineTotal;
        const isExceeded = (item.qty > item.maxAvail);
        if (isExceeded) hasStockViolation = true;

        html += `
            <div class="p-2.5 rounded-lg border ${isExceeded ? 'bg-rose-50 border-rose-300' : 'bg-slate-50 border-slate-200'} flex items-center justify-between text-xs">
                <div class="flex-1 pr-2">
                    <span class="font-bold text-slate-800">${item.name}</span>
                    <span class="block text-[10px] text-slate-500 font-mono">Rp ${new Intl.NumberFormat('id-ID').format(item.price)} × ${item.qty} ${isExceeded ? '<b class="text-rose-600">(Stok Toko: '+item.maxAvail+')</b>' : ''}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="updateQty(${item.id}, -1)" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold hover:bg-slate-100">-</button>
                    <span class="font-bold font-mono text-slate-900 w-4 text-center">${item.qty}</span>
                    <button type="button" onclick="updateQty(${item.id}, 1)" class="w-6 h-6 rounded bg-white border border-slate-300 font-bold hover:bg-slate-100">+</button>
                    <span class="font-bold font-mono text-slate-900 w-16 text-right">Rp ${new Intl.NumberFormat('id-ID').format(lineTotal)}</span>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;

    // PPN 11% (half-up)
    const vat = Math.round(netTotal * 0.11);
    const gross = netTotal + vat;

    document.getElementById('txtNet').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(netTotal);
    document.getElementById('txtVat').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(vat);
    document.getElementById('txtGross').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(gross);
    document.getElementById('cartDataJson').value = JSON.stringify(cart);

    // Rule SR-04 enforcement: Block checkout if stock is exceeded!
    const errBox = document.getElementById('stockErrorNotice');
    const btnPay = document.getElementById('btnPay');

    if (hasStockViolation) {
        errBox.classList.remove('hidden');
        btnPay.disabled = true;
    } else {
        errBox.classList.add('hidden');
        btnPay.disabled = false;
    }

    calcChange();
}

function calcChange() {
    const grossStr = document.getElementById('txtGross').innerText.replace(/[^0-9]/g, '');
    const gross = parseInt(grossStr) || 0;
    const tendered = parseInt(document.getElementById('inputCashTendered').value) || 0;
    const change = tendered - gross;

    const txtChange = document.getElementById('txtChange');
    if (tendered > 0) {
        if (change >= 0) {
            txtChange.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(change);
            txtChange.className = 'font-mono font-black text-emerald-700 text-sm';
        } else {
            txtChange.innerText = 'Kurang Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(change));
            txtChange.className = 'font-mono font-black text-rose-600 text-sm';
        }
    } else {
        txtChange.innerText = 'Rp 0';
    }
}

function filterPosProducts() {
    const query = document.getElementById('posSearch').value.toLowerCase();
    const items = document.querySelectorAll('.product-item');
    items.forEach(el => {
        const name = el.getAttribute('data-name');
        const sku = el.getAttribute('data-sku');
        if (name.includes(query) || sku.includes(query)) {
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    });
}
</script>
