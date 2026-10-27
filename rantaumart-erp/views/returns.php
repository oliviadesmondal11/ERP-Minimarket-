<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <span>Inventory & Accounting</span>
                <span>/</span>
                <span class="text-slate-800">Returns & Refunds</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">🔄 Retur Penjualan, Refund & Credit Note (S-17)</h1>
        </div>
        <div class="flex items-center space-x-3 text-xs">
            <span class="px-3 py-1 bg-rose-50 text-rose-800 rounded-lg font-bold border border-rose-200">
                Rule SR-16: Pengembalian Nilai Barang, Pajak & Kas Seimbang
            </span>
        </div>
    </div>

    <!-- Alert / Messages -->
    <?php if (!empty($_GET['error'])): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-900 text-xs font-bold">❌ <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 text-xs font-bold">✅ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <!-- Two Columns: POS Cash Refund (T-33) & B2B Credit Note (T-34) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Form 1: POS Cash Refund (T-33) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center space-x-2">
                    <span>🏪</span>
                    <span>Retur Ritel Toko POS (Cash Refund — T-33)</span>
                </h3>
                <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded">Restockable</span>
            </div>
            <p class="text-xs text-slate-500">
                Skenario T-33: Pelanggan meretur 1 botol AIR-600 dari struk kasir toko. Kas kasir berkurang Rp4.440 (termasuk PPN 11%) dan stok STORE-PKU bertambah 1 pcs.
            </p>

            <form method="POST" action="?action=process_pos_refund" class="space-y-3 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Produk Diretur</label>
                    <select name="product_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                        <option value="1">AIR-600 (Air mineral Nusa 600ml) — Refund Rp 4.440 (HPP Rp 2.500)</option>
                        <option value="8">MIE-GOR-80 (Mi instan Hemat Goreng) — Refund Rp 4.662</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Retur</label>
                        <input type="number" name="return_qty" value="1" min="1" max="5" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Barang</label>
                        <select name="condition" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                            <option value="resalable">Layak Jual (Kembalikan ke Stok)</option>
                            <option value="damaged">Rusak (Hapuskan)</option>
                        </select>
                    </div>
                </div>
                <div class="pt-3">
                    <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md transition">
                        Proses Refund Tunai Kasir (T-33)
                    </button>
                </div>
            </form>
        </div>

        <!-- Form 2: B2B Customer Return & Credit Note (T-34) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm flex items-center space-x-2">
                    <span>🏢</span>
                    <span>Retur Grosir B2B & Nota Kredit (Credit Note — T-34)</span>
                </h3>
                <span class="text-[10px] bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded">Credit Note AR</span>
            </div>
            <p class="text-xs text-slate-500">
                Skenario T-34: Pelanggan B2B meretur barang yang telah terkirim. Mengurangi saldo Piutang (AR), membalik revenue dan PPN, serta memulihkan stok gudang WH-PKU.
            </p>

            <form method="POST" action="?action=process_b2b_return" class="space-y-3 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pelanggan B2B</label>
                    <select name="partner_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['code']) ?> - <?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Produk Diretur</label>
                        <select name="product_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                            <option value="1">AIR-600 (Air mineral Nusa 600ml)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kuantitas Retur (pcs)</label>
                        <input type="number" name="return_qty" value="2" min="1" max="100" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                    </div>
                </div>
                <div class="pt-3">
                    <button type="submit" class="w-full py-2.5 bg-[#714B67] hover:bg-[#593B52] text-white rounded-xl text-xs font-bold shadow-md transition">
                        Terbitkan Credit Note B2B (T-34)
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>
