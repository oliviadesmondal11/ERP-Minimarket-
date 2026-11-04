<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <span>Inventory</span>
                <span>/</span>
                <span class="text-slate-800">Stock & Transfer</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">🏬 Matriks Stok Persediaan & Transfer Internal (S-07)</h1>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="document.getElementById('modalTransfer').classList.remove('hidden')" 
                    class="px-4 py-2 bg-[#714B67] hover:bg-[#593B52] text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center space-x-1.5">
                <span>🔄 Transfer Antar Lokasi (T-13)</span>
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

    <!-- Stock Matrix Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-sm font-bold text-slate-800">Sebaran Persediaan Fisik, Reservasi & Stok Tersedia</h2>
            <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded">Rumus: Available = On Hand − Reserved</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100/70 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                        <th class="py-3 px-3">Kode Lokasi</th>
                        <th class="py-3 px-3">Nama Lokasi</th>
                        <th class="py-3 px-3">SKU</th>
                        <th class="py-3 px-3">Nama Produk</th>
                        <th class="py-3 px-3 text-right">Standard Cost</th>
                        <th class="py-3 px-3 text-right">On Hand (Fisik)</th>
                        <th class="py-3 px-3 text-right">Reserved (Cadangan)</th>
                        <th class="py-3 px-3 text-right">Available (Tersedia)</th>
                        <th class="py-3 px-3 text-right">Total Valuasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php 
                    $grandOnHand = 0;
                    $grandReserved = 0;
                    $grandAvailable = 0;
                    $grandValuation = 0;

                    foreach ($stockData as $s): 
                        $onHand = (int)$s['on_hand_qty'];
                        $reserved = (int)$s['reserved_qty'];
                        $available = $onHand - $reserved;
                        $valuation = $onHand * (int)$s['cost_price'];

                        $grandOnHand += $onHand;
                        $grandReserved += $reserved;
                        $grandAvailable += $available;
                        $grandValuation += $valuation;

                        $isAirControl = ($s['sku'] === 'AIR-600' && $s['location_code'] === 'WH-PKU');
                    ?>
                        <tr class="<?= $isAirControl ? 'bg-amber-50/80 font-bold border-l-4 border-l-amber-500' : 'hover:bg-slate-50' ?>">
                            <td class="py-2.5 px-3 font-mono font-bold">
                                <span class="px-2 py-0.5 rounded text-[11px] <?= str_starts_with($s['location_code'], 'WH') ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                                    <?= htmlspecialchars($s['location_code']) ?>
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600"><?= htmlspecialchars($s['location_name']) ?></td>
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-800"><?= htmlspecialchars($s['sku']) ?></td>
                            <td class="py-2.5 px-3 text-slate-800">
                                <?= htmlspecialchars($s['product_name']) ?>
                                <?php if ($isAirControl): ?>
                                    <span class="ml-1.5 text-[10px] bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded font-black">
                                        TARGET BASELINE (72 AVAIL)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-slate-600"><?= App\Helpers::formatRupiah($s['cost_price']) ?></td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900"><?= $onHand ?></td>
                            <td class="py-2.5 px-3 text-right font-mono <?= $reserved > 0 ? 'text-amber-700 font-bold' : 'text-slate-400' ?>"><?= $reserved ?></td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold <?= $isAirControl ? 'text-emerald-700 text-sm' : 'text-cyan-700' ?>"><?= $available ?></td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900"><?= App\Helpers::formatRupiah($valuation) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="5" class="py-3.5 px-3 text-right uppercase text-slate-700">Total Akumulasi Perusahaan:</td>
                        <td class="py-3.5 px-3 text-right font-mono text-slate-900 text-sm"><?= number_format($grandOnHand, 0, ',', '.') ?> pcs</td>
                        <td class="py-3.5 px-3 text-right font-mono text-amber-700"><?= number_format($grandReserved, 0, ',', '.') ?> pcs</td>
                        <td class="py-3.5 px-3 text-right font-mono text-cyan-700 text-sm"><?= number_format($grandAvailable, 0, ',', '.') ?> pcs</td>
                        <td class="py-3.5 px-3 text-right font-mono text-emerald-800 text-sm"><?= App\Helpers::formatRupiah($grandValuation) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>

<!-- Modal Form Internal Transfer (T-13, T-14) -->
<div id="modalTransfer" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-base font-bold text-slate-900 mb-2">Transfer Internal Antar-Lokasi (S-07)</h3>
        <p class="text-xs text-slate-500 mb-4">Pengujian T-13: Transfer 15 AIR-600 dari WH-PKU ke STORE-PKU • T-14: Validasi batas stok.</p>
        
        <form method="POST" action="?action=internal_transfer" class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Asal (Source)</label>
                    <select name="from_location_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                        <option value="1">WH-PKU (Gudang Pusat)</option>
                        <option value="2">STORE-PKU (Minimarket PKU)</option>
                        <option value="3">STORE-DUM (Minimarket Dumai)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lokasi Tujuan (Dest)</label>
                    <select name="to_location_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                        <option value="2">STORE-PKU (Minimarket PKU)</option>
                        <option value="3">STORE-DUM (Minimarket Dumai)</option>
                        <option value="1">WH-PKU (Gudang Pusat)</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Produk SKU</label>
                <select name="product_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $p['sku'] === 'AIR-600' ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['sku']) ?> - <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Transfer (pcs)</label>
                <input type="number" name="qty" value="15" min="1" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold font-mono">
                <span class="text-[10px] text-slate-400">SR-17: Transfer hanya memindahkan stok antar lokasi tanpa mengubah nilai total perusahaan.</span>
            </div>
            <div class="pt-4 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalTransfer').classList.add('hidden')" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#714B67] hover:bg-[#593B52] text-white rounded-lg text-xs font-bold shadow-md">
                    Proses Transfer Stok
                </button>
            </div>
        </form>
    </div>
</div>
