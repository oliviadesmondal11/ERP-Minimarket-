<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <span>Inventory & Sales</span>
                <span>/</span>
                <span class="text-slate-800">Master Data Produk</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">🏷️ Katalog 15 SKU Varian Produk (S-03)</h1>
        </div>
        <div class="flex items-center space-x-3 text-xs">
            <span class="px-3 py-1 bg-emerald-50 text-emerald-800 rounded-lg font-bold border border-emerald-200">
                Rule SR-20: Unique SKU per Varian
            </span>
        </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <th class="py-3 px-3">Variant SKU</th>
                        <th class="py-3 px-3">Nama Produk</th>
                        <th class="py-3 px-3">Ukuran</th>
                        <th class="py-3 px-3">Rasa / Varian</th>
                        <th class="py-3 px-3">Unit</th>
                        <th class="py-3 px-3 text-right">Standard Cost</th>
                        <th class="py-3 px-3 text-right">Sales Price</th>
                        <th class="py-3 px-3">Default Vendor</th>
                        <th class="py-3 px-3 text-right">WH-PKU</th>
                        <th class="py-3 px-3 text-right">STORE-PKU</th>
                        <th class="py-3 px-3 text-right">STORE-DUM</th>
                        <th class="py-3 px-3 text-right">Total On Hand</th>
                        <th class="py-3 px-3 text-right">Standard Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php 
                    $grandTotalPcs = 0;
                    $grandTotalValue = 0;
                    foreach ($products as $p): 
                        $grandTotalPcs += (int)$p['total_on_hand'];
                        $grandTotalValue += (int)$p['total_valuation'];
                        $isAir600 = ($p['sku'] === 'AIR-600');
                    ?>
                        <tr class="<?= $isAir600 ? 'bg-amber-50/70 font-semibold' : 'hover:bg-slate-50' ?>">
                            <td class="py-3 px-3 font-mono font-bold text-slate-900"><?= htmlspecialchars($p['sku']) ?></td>
                            <td class="py-3 px-3 text-slate-800 font-semibold"><?= htmlspecialchars($p['name']) ?></td>
                            <td class="py-3 px-3 text-slate-600"><?= htmlspecialchars($p['size']) ?></td>
                            <td class="py-3 px-3 text-slate-600"><?= htmlspecialchars($p['flavor']) ?></td>
                            <td class="py-3 px-3 font-mono text-slate-500"><?= htmlspecialchars($p['unit']) ?></td>
                            <td class="py-3 px-3 text-right font-mono text-slate-600"><?= App\Helpers::formatRupiah($p['cost_price']) ?></td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-emerald-700"><?= App\Helpers::formatRupiah($p['sales_price']) ?></td>
                            <td class="py-3 px-3 font-mono font-bold text-amber-700"><?= htmlspecialchars($p['vendor_code'] ?? 'V01') ?></td>
                            <td class="py-3 px-3 text-right font-mono text-slate-700"><?= (int)$p['wh_pku_qty'] ?></td>
                            <td class="py-3 px-3 text-right font-mono text-slate-700"><?= (int)$p['store_pku_qty'] ?></td>
                            <td class="py-3 px-3 text-right font-mono text-slate-700"><?= (int)$p['store_dum_qty'] ?></td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900"><?= (int)$p['total_on_hand'] ?></td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900"><?= App\Helpers::formatRupiah($p['total_valuation']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="11" class="py-3 px-3 text-right uppercase text-slate-700">Total Akumulasi Perusahaan:</td>
                        <td class="py-3 px-3 text-right font-mono text-slate-900 text-sm"><?= number_format($grandTotalPcs, 0, ',', '.') ?> pcs</td>
                        <td class="py-3 px-3 text-right font-mono text-emerald-800 text-sm"><?= App\Helpers::formatRupiah($grandTotalValue) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
