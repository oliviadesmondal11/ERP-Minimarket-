<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <span>Inventory & Quality Check</span>
                <span>/</span>
                <span class="text-slate-800">Goods Receipt</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">📥 Penerimaan Barang & Inspeksi Mutu (S-09)</h1>
        </div>
        <div class="flex items-center space-x-3 text-xs">
            <span class="px-3 py-1 bg-amber-50 text-amber-800 rounded-lg font-bold border border-amber-200">
                Rule SR-10: Hanya Accepted yang Menambah Stok & Valuasi
            </span>
        </div>
    </div>

    <!-- Alert -->
    <?php if (!empty($_GET['error'])): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-900 text-xs font-bold">❌ <?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 text-xs font-bold">✅ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <!-- Receipts Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-sm font-bold text-slate-800">Daftar Penerimaan Barang dari Vendor</h2>
            <span class="text-xs text-slate-500">T-17: Terima 100 pcs (85 Accepted, 15 Rejected) • T-18: Guard No Re-validate</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100/70 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <th class="py-3 px-4">No. Penerimaan</th>
                        <th class="py-3 px-4">No. PO</th>
                        <th class="py-3 px-4">Pemasok</th>
                        <th class="py-3 px-4">Gudang</th>
                        <th class="py-3 px-4">SKU Barang</th>
                        <th class="py-3 px-4 text-right">Dipesan</th>
                        <th class="py-3 px-4 text-right">Diterima Fisik</th>
                        <th class="py-3 px-4 text-right">Accepted (Lolos QC)</th>
                        <th class="py-3 px-4 text-right">Rejected (Cacat)</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Form Inspeksi & Validasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($receipts)): ?>
                        <tr><td colspan="11" class="py-6 text-center text-slate-400">Belum ada dokumen penerimaan aktif. Buat Purchase Order terlebih dahulu.</td></tr>
                    <?php else: foreach ($receipts as $r): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-700">WH/IN/<?= str_pad($r['id'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-900"><?= htmlspecialchars($r['po_number']) ?></td>
                            <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($r['vendor_name']) ?></td>
                            <td class="py-3 px-4 font-mono text-purple-700 font-bold"><?= htmlspecialchars($r['location_code']) ?></td>
                            <td class="py-3 px-4 font-semibold text-slate-800"><?= htmlspecialchars($r['product_sku']) ?></td>
                            <td class="py-3 px-4 text-right font-mono font-bold"><?= (int)$r['ordered_qty'] ?></td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-800"><?= (int)$r['received_qty'] ?></td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700"><?= (int)$r['accepted_qty'] ?></td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-rose-700"><?= (int)$r['rejected_qty'] ?></td>
                            <td class="py-3 px-4">
                                <?php if ($r['accepted_qty'] > 0 || $r['rejected_qty'] > 0): ?>
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">DONE (VALIDATED)</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">WAITING / READY</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($r['accepted_qty'] === 0 && $r['rejected_qty'] === 0): ?>
                                    <form method="POST" action="?action=validate_receipt" class="inline-flex items-center space-x-1">
                                        <input type="hidden" name="po_id" value="<?= $r['po_id'] ?>">
                                        <input type="hidden" name="product_id" value="<?= $r['product_id'] ?>">
                                        <input type="hidden" name="received_qty" value="<?= $r['ordered_qty'] ?>">
                                        
                                        <div class="flex items-center space-x-1">
                                            <input type="number" name="accepted_qty" value="85" min="0" max="<?= $r['ordered_qty'] ?>" 
                                                   class="w-14 px-1.5 py-1 text-xs border border-emerald-300 rounded font-bold text-center text-emerald-800" title="Accepted (Lolos QC)">
                                            <input type="number" name="rejected_qty" value="15" min="0" max="<?= $r['ordered_qty'] ?>" 
                                                   class="w-14 px-1.5 py-1 text-xs border border-rose-300 rounded font-bold text-center text-rose-800" title="Rejected (Cacat/Ditolak)">
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition">
                                                Validasi QC
                                            </button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <a href="?page=accounting&tab=bills" class="text-xs text-indigo-600 font-bold hover:underline">Siap Ditagih Vendor (SR-06) →</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
