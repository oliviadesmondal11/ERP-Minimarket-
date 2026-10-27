<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <span>Procurement</span>
                <span>/</span>
                <span class="text-slate-800">Purchase Orders</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">🛒 Pengadaan & Purchase Order (S-08)</h1>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="document.getElementById('modalNewPO').classList.remove('hidden')" 
                    class="px-4 py-2 bg-[#714B67] hover:bg-[#593B52] text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center space-x-1.5">
                <span>+ Buat RFQ / PO Baru (T-15)</span>
            </button>
        </div>
    </div>

    <!-- Alert / Messages -->
    <?php if (!empty($_GET['error'])): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-900 text-xs font-bold">❌ <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 text-xs font-bold">✅ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <!-- PO Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-sm font-bold text-slate-800">Daftar Dokumen Permintaan & Pesanan Pembelian</h2>
            <span class="text-xs text-slate-500 font-medium">T-15: PO 100 MIE-GOR-80 @ Rp3.000 • T-16: Price Lock</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100/70 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <th class="py-3 px-4">No. PO</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Pemasok (Vendor)</th>
                        <th class="py-3 px-4">Gudang Tujuan</th>
                        <th class="py-3 px-4">Barang & Kuantitas</th>
                        <th class="py-3 px-4 text-right">Harga Satuan</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                        <th class="py-3 px-4 text-right">PPN 11%</th>
                        <th class="py-3 px-4 text-right">Gross PO</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($purchaseOrders as $po): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-700"><?= htmlspecialchars($po['po_number']) ?></td>
                            <td class="py-3 px-4 text-slate-500"><?= htmlspecialchars($po['order_date']) ?></td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <?= htmlspecialchars($po['vendor_code']) ?> - <?= htmlspecialchars($po['vendor_name']) ?>
                            </td>
                            <td class="py-3 px-4 font-mono text-purple-700 font-bold"><?= htmlspecialchars($po['location_code']) ?></td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800"><?= htmlspecialchars($po['product_sku']) ?></span>
                                <span class="text-slate-500">(<?= (int)$po['ordered_qty'] ?> pcs)</span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-700"><?= App\Helpers::formatRupiah($po['unit_price']) ?></td>
                            <td class="py-3 px-4 text-right font-mono text-slate-700"><?= App\Helpers::formatRupiah($po['subtotal']) ?></td>
                            <td class="py-3 px-4 text-right font-mono text-slate-500"><?= App\Helpers::formatRupiah($po['tax_amount']) ?></td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-800"><?= App\Helpers::formatRupiah($po['grand_total']) ?></td>
                            <td class="py-3 px-4">
                                <?php if ($po['status'] === 'confirmed'): ?>
                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[10px]">PURCHASE ORDER</span>
                                <?php elseif ($po['status'] === 'done'): ?>
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">DONE (RECEIVED)</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">RFQ</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($po['status'] === 'confirmed'): ?>
                                    <a href="?page=receipt&po_id=<?= $po['id'] ?>" class="px-2.5 py-1 bg-[#714B67] hover:bg-[#593B52] text-white rounded font-bold text-xs transition">
                                        Penerimaan Gudang (S-09) →
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-400 font-medium">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Form Buat PO Baru (T-15) -->
<div id="modalNewPO" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl">
        <h3 class="text-base font-bold text-slate-900 mb-3">Buat RFQ / Purchase Order Baru</h3>
        <p class="text-xs text-slate-500 mb-4">Pengujian T-15: 100 pcs MIE-GOR-80 dari PT Pangan Pilihan (V01) @ Rp3.000.</p>
        
        <form method="POST" action="?action=create_po" class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pemasok (Vendor)</label>
                <select name="partner_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
                    <?php foreach ($vendors as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['code']) ?> - <?= htmlspecialchars($v['name']) ?> (<?= htmlspecialchars($v['goods']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Gudang Penerima</label>
                <select name="location_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
                    <option value="1">WH-PKU (Gudang pusat Pekanbaru)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Produk SKU</label>
                    <select name="product_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['sku'] === 'MIE-GOR-80' ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['sku']) ?> - <?= htmlspecialchars($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kuantitas Pesan</label>
                    <input type="number" name="ordered_qty" value="100" min="1" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Harga Beli Vendor (IDR)</label>
                <input type="number" name="unit_price" value="3000" min="100" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                <span class="text-[10px] text-slate-400">Harga kesepakatan vendor pricelist.</span>
            </div>
            <div class="pt-4 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalNewPO').classList.add('hidden')" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#714B67] hover:bg-[#593B52] text-white rounded-lg text-xs font-bold shadow-md">
                    Konfirmasi Purchase Order
                </button>
            </div>
        </form>
    </div>
</div>
