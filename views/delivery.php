<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <span>Inventory</span>
                <span>/</span>
                <span class="text-slate-800">Delivery Orders</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">🚚 Pengiriman & Surat Jalan (S-06)</h1>
        </div>
        <div class="flex items-center space-x-3 text-xs">
            <span class="px-3 py-1 bg-purple-50 text-purple-800 rounded-lg font-bold border border-purple-200">
                Rule SR-02 & SR-09: Partial Fulfillment & Backorder
            </span>
        </div>
    </div>

    <?php if (!empty($_GET['error'])): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-900 text-xs font-bold">❌ <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 text-xs font-bold">✅ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <!-- Delivery Orders List -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-sm font-bold text-slate-800">Daftar Dokumen Pengiriman Barang (Surat Jalan)</h2>
            <span class="text-xs text-slate-500">T-11: Kirim 12 dari 20 AIR-600 • T-12: Validasi Batas Stok</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100/70 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <th class="py-3 px-4">No. Pengiriman</th>
                        <th class="py-3 px-4">Referensi SO</th>
                        <th class="py-3 px-4">Tujuan / Pelanggan</th>
                        <th class="py-3 px-4">Gudang Asal</th>
                        <th class="py-3 px-4">SKU Barang</th>
                        <th class="py-3 px-4 text-right">Demand</th>
                        <th class="py-3 px-4 text-right">Reserved</th>
                        <th class="py-3 px-4 text-right">Done Qty</th>
                        <th class="py-3 px-4 text-right">Backorder</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Proses Validasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($deliveries)): ?>
                        <tr><td colspan="11" class="py-6 text-center text-slate-400">Belum ada dokumen pengiriman. Konfirmasi Sales Order terlebih dahulu.</td></tr>
                    <?php else: foreach ($deliveries as $d): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-700">WH/OUT/<?= str_pad($d['id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800"><?= htmlspecialchars($d['order_number']) ?></td>
                            <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($d['customer_name']) ?></td>
                            <td class="py-3 px-4 font-mono text-purple-700 font-bold"><?= htmlspecialchars($d['location_code']) ?></td>
                            <td class="py-3 px-4 font-semibold text-slate-800"><?= htmlspecialchars($d['product_sku']) ?></td>
                            <td class="py-3 px-4 text-right font-mono font-bold"><?= (int)$d['ordered_qty'] ?></td>
                            <td class="py-3 px-4 text-right font-mono text-amber-700 font-bold"><?= (int)$d['reserved_qty'] ?></td>
                            <td class="py-3 px-4 text-right font-mono text-emerald-700 font-bold"><?= (int)$d['delivered_qty'] ?></td>
                            <td class="py-3 px-4 text-right font-mono text-slate-500 font-bold"><?= max(0, (int)$d['ordered_qty'] - (int)$d['delivered_qty']) ?></td>
                            <td class="py-3 px-4">
                                <?php if ($d['delivered_qty'] >= $d['ordered_qty']): ?>
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">DONE</span>
                                <?php elseif ($d['delivered_qty'] > 0): ?>
                                    <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[10px]">PARTIAL DONE</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">WAITING / READY</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($d['delivered_qty'] < $d['ordered_qty']): ?>
                                    <form method="POST" action="?action=validate_delivery" class="inline-flex items-center space-x-1">
                                        <input type="hidden" name="so_id" value="<?= $d['so_id'] ?>">
                                        <input type="hidden" name="product_id" value="<?= $d['product_id'] ?>">
                                        <input type="number" name="done_qty" value="<?= min(12, $d['ordered_qty'] - $d['delivered_qty']) ?>" 
                                               min="1" max="<?= $d['ordered_qty'] - $d['delivered_qty'] ?>" 
                                               class="w-16 px-1.5 py-1 text-xs border border-slate-300 rounded font-bold text-center">
                                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition">
                                            Validasi Kirim
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="?page=accounting&tab=invoices" class="text-xs text-indigo-600 font-bold hover:underline">Siap Ditagih (SR-02) →</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
