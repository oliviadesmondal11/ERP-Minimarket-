<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <span>Sales</span>
                <span>/</span>
                <span class="text-slate-800">Sales Orders</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">💼 Penjualan B2B & Sales Order (S-05)</h1>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="document.getElementById('modalNewSO').classList.remove('hidden')" 
                    class="px-4 py-2 bg-[#714B67] hover:bg-[#593B52] text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center space-x-1.5">
                <span>+ Buat Sales Order Baru (T-09)</span>
            </button>
        </div>
    </div>

    <!-- Alert / Flash Message if any -->
    <?php if (!empty($_GET['error'])): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-900 text-xs font-bold flex items-center space-x-2">
            <span>❌</span>
            <span><?= htmlspecialchars($_GET['error']) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 text-xs font-bold flex items-center space-x-2">
            <span>✅</span>
            <span><?= htmlspecialchars($_GET['success']) ?></span>
        </div>
    <?php endif; ?>

    <!-- Sales Orders Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-sm font-bold text-slate-800">Daftar Dokumen Quotation & Sales Order</h2>
            <span class="text-xs text-slate-500 font-medium">Aturan: Diskon ≤ 10% (SR-07) • Credit Limit Check (SR-08)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100/70 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <th class="py-3 px-4">Nomor SO</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Gudang Sumber</th>
                        <th class="py-3 px-4">Item & Qty</th>
                        <th class="py-3 px-4 text-right">Net Subtotal</th>
                        <th class="py-3 px-4 text-right">PPN 11%</th>
                        <th class="py-3 px-4 text-right">Gross Total</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi / Alur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($salesOrders as $so): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-700"><?= htmlspecialchars($so['order_number']) ?></td>
                            <td class="py-3 px-4 text-slate-500"><?= htmlspecialchars($so['order_date']) ?></td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <?= htmlspecialchars($so['customer_code']) ?> - <?= htmlspecialchars($so['customer_name']) ?>
                            </td>
                            <td class="py-3 px-4 font-mono text-purple-700 font-bold"><?= htmlspecialchars($so['location_code']) ?></td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-800"><?= htmlspecialchars($so['product_sku'] ?? 'AIR-600') ?></span>
                                <span class="text-slate-500">(<?= (int)$so['ordered_qty'] ?> pcs)</span>
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-700"><?= App\Helpers::formatRupiah($so['subtotal']) ?></td>
                            <td class="py-3 px-4 text-right font-mono text-slate-500"><?= App\Helpers::formatRupiah($so['tax_amount']) ?></td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-800"><?= App\Helpers::formatRupiah($so['grand_total']) ?></td>
                            <td class="py-3 px-4">
                                <?php if ($so['status'] === 'confirmed'): ?>
                                    <span class="px-2.5 py-1 rounded bg-blue-100 text-blue-800 font-bold text-[10px] uppercase">Confirmed (Reserved)</span>
                                <?php elseif ($so['status'] === 'done'): ?>
                                    <span class="px-2.5 py-1 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase">Done (Delivered)</span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded bg-amber-100 text-amber-800 font-bold text-[10px] uppercase">Quotation</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($so['status'] === 'draft' || $so['status'] === 'quotation'): ?>
                                    <a href="?action=confirm_so&id=<?= $so['id'] ?>" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded font-bold text-xs">Confirm</a>
                                <?php elseif ($so['status'] === 'confirmed'): ?>
                                    <a href="?page=delivery&so_id=<?= $so['id'] ?>" class="px-3 py-1 bg-[#714B67] hover:bg-[#593B52] text-white rounded font-bold text-xs">Proses Kirim →</a>
                                <?php else: ?>
                                    <span class="text-slate-400 font-semibold">Terkirim</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Form Buat SO Baru (T-09, T-10) -->
<div id="modalNewSO" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl">
        <h3 class="text-base font-bold text-slate-900 mb-3">Buat Quotation / Sales Order Baru</h3>
        <p class="text-xs text-slate-500 mb-4">Pengujian T-09: 20 pcs AIR-600 @ Rp4.000 untuk Warung Kopi Pasar (C001).</p>
        
        <form method="POST" action="?action=create_so" class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pelanggan</label>
                <select name="partner_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['code']) ?> - <?= htmlspecialchars($c['name']) ?> (Limit: <?= App\Helpers::formatRupiah($c['credit_limit']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Gudang Pengeluaran</label>
                <select name="location_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
                    <option value="1">WH-PKU (Gudang Pusat Pekanbaru)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Produk SKU</label>
                    <select name="product_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['sku']) ?> - <?= htmlspecialchars($p['name']) ?> (Rp<?= number_format($p['sales_price'],0,',','.') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kuantitas Pesan (pcs)</label>
                    <input type="number" name="ordered_qty" value="20" min="1" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Diskon (%) — Maksimal 10% (SR-07)</label>
                <input type="number" name="discount_percent" value="0" min="0" max="100" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                <span class="text-[10px] text-slate-400">Aturan SR-07: Diskon di atas 10% otomatis ditolak sistem.</span>
            </div>
            <div class="pt-4 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalNewSO').classList.add('hidden')" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#714B67] hover:bg-[#593B52] text-white rounded-lg text-xs font-bold shadow-md">
                    Konfirmasi & Cadangkan Stok (SO)
                </button>
            </div>
        </form>
    </div>
</div>
