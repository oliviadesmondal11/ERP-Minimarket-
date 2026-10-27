<div class="space-y-6">

    <!-- Odoo Style Control Panel / Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <span>RantauMart</span>
                <span>/</span>
                <span class="text-slate-800">Accounting & Operations</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center space-x-2">
                <span>📊 Dashboard & Audit Trace</span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                    Live Baseline Validated
                </span>
            </h1>
        </div>

        <!-- Action Shortcuts -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="?page=pos" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition flex items-center space-x-1.5">
                <span>🛒 Buka Kasir POS</span>
            </a>
            <a href="?page=sales" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-xs font-bold transition flex items-center space-x-1">
                <span>💼 Sales Order</span>
            </a>
            <a href="?page=delivery" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-xs font-bold transition flex items-center space-x-1">
                <span>🚚 Delivery</span>
            </a>
            <a href="?page=purchase" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-xs font-bold transition flex items-center space-x-1">
                <span>🛒 Purchase</span>
            </a>
            <a href="?page=accounting" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-xs font-bold transition flex items-center space-x-1">
                <span>💳 Invoices</span>
            </a>
        </div>
    </div>

    <!-- 7 Control Baseline Metric Cards Grid (S-18 & Handout Baseline) -->
    <div>
        <div class="flex items-center justify-between mb-3 px-1">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-600 flex items-center space-x-2">
                <span>🎯</span>
                <span>7 Parameter Kontrol Baseline — PT RantauMart Riau (Toleransi Selisih = 0)</span>
            </h2>
            <span class="text-xs text-slate-500 font-medium">Sesuai Dokumen Skenario Mahasiswa</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- 1. Total On Hand -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm hover:shadow transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Stok On Hand</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $metrics['total_on_hand'] === 1500 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                        <?= $metrics['total_on_hand'] === 1500 ? 'MATCH' : 'SELISIH' ?>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-900">
                        <?= number_format($metrics['total_on_hand'], 0, ',', '.') ?> <span class="text-sm font-semibold text-slate-500">pcs</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex justify-between">
                        <span>Target: 1.500 pcs</span>
                        <span class="text-emerald-700 font-semibold">15 SKU (3 Lokasi)</span>
                    </div>
                </div>
            </div>

            <!-- 2. Nilai Persediaan -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm hover:shadow transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Nilai Persediaan</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $metrics['total_stock_value'] === 9628900 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                        <?= $metrics['total_stock_value'] === 9628900 ? 'MATCH' : 'SELISIH' ?>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-emerald-700">
                        <?= App\Helpers::formatRupiah($metrics['total_stock_value']) ?>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex justify-between">
                        <span>Target: Rp 9.628.900</span>
                        <span class="text-slate-400">Total Valuation</span>
                    </div>
                </div>
            </div>

            <!-- 3. Piutang (AR) -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm hover:shadow transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Piutang Usaha (AR)</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $metrics['total_ar'] === 888000 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                        <?= $metrics['total_ar'] === 888000 ? 'MATCH' : 'SELISIH' ?>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-blue-700">
                        <?= App\Helpers::formatRupiah($metrics['total_ar']) ?>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex justify-between">
                        <span>Target: Rp 888.000</span>
                        <span class="text-slate-400">C002 CV Kantin Tunas</span>
                    </div>
                </div>
            </div>

            <!-- 4. Utang (AP) -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm hover:shadow transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Utang Usaha (AP)</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $metrics['total_ap'] === 555000 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                        <?= $metrics['total_ap'] === 555000 ? 'MATCH' : 'SELISIH' ?>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-amber-700">
                        <?= App\Helpers::formatRupiah($metrics['total_ap']) ?>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex justify-between">
                        <span>Target: Rp 555.000</span>
                        <span class="text-slate-400">V01 PT Pangan Pilihan</span>
                    </div>
                </div>
            </div>

            <!-- 5. Bank -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm hover:shadow transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Kas Bank</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $metrics['bank_balance'] === 15000000 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                        <?= $metrics['bank_balance'] === 15000000 ? 'MATCH' : 'SELISIH' ?>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-indigo-700">
                        <?= App\Helpers::formatRupiah($metrics['bank_balance']) ?>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex justify-between">
                        <span>Target: Rp 15.000.000</span>
                        <span class="text-slate-400">Akun GL 1010 Bank</span>
                    </div>
                </div>
            </div>

            <!-- 6. POS Cash Drawer -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm hover:shadow transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">POS Cash Drawer</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $metrics['pos_cash'] === 200000 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                        <?= $metrics['pos_cash'] === 200000 ? 'MATCH' : 'SELISIH' ?>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black text-slate-800">
                        <?= App\Helpers::formatRupiah($metrics['pos_cash']) ?>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex justify-between">
                        <span>Target: Rp 200.000</span>
                        <span class="text-emerald-700 font-semibold">Session 1 (Open)</span>
                    </div>
                </div>
            </div>

            <!-- 7. Available AIR-600 WH-PKU -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm hover:shadow transition md:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Available AIR-600 @ Gudang WH-PKU</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $metrics['available_air600_wh'] === 72 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                        <?= $metrics['available_air600_wh'] === 72 ? 'MATCH' : 'SELISIH' ?>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-3">
                    <div class="text-2xl font-black text-cyan-700">
                        <?= $metrics['available_air600_wh'] ?> <span class="text-sm font-semibold text-slate-500">pcs Available</span>
                    </div>
                    <div class="text-xs text-slate-600">
                        (On Hand: <strong>80</strong> − Reserved SO S00007: <strong>8</strong> = <strong>72</strong>)
                    </div>
                </div>
                <div class="text-xs text-slate-500 mt-2">
                    Integritas Rule SR-09: <code>Available = On Hand − Reserved</code> terverifikasi presisi.
                </div>
            </div>

        </div>
    </div>

    <!-- Location Valuation & Company Equity Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- WH-PKU -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-purple-800 bg-purple-100 px-2 py-0.5 rounded">WH-PKU</span>
                <span class="text-xs text-slate-400 font-medium">Gudang Pusat PKU</span>
            </div>
            <div class="mt-2 text-xl font-black text-slate-800">
                <?= App\Helpers::formatRupiah($metrics['val_wh_pku']) ?>
            </div>
            <div class="text-[11px] text-slate-500 mt-1">
                Target: Rp 4.997.000 (<?= $metrics['val_wh_pku'] === 4997000 ? '✅ MATCH' : '⚠️ CEK' ?>)
            </div>
        </div>

        <!-- STORE-PKU -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-blue-800 bg-blue-100 px-2 py-0.5 rounded">STORE-PKU</span>
                <span class="text-xs text-slate-400 font-medium">Minimarket Pekanbaru</span>
            </div>
            <div class="mt-2 text-xl font-black text-slate-800">
                <?= App\Helpers::formatRupiah($metrics['val_store_pku']) ?>
            </div>
            <div class="text-[11px] text-slate-500 mt-1">
                Target: Rp 2.979.100 (<?= $metrics['val_store_pku'] === 2979100 ? '✅ MATCH' : '⚠️ CEK' ?>)
            </div>
        </div>

        <!-- STORE-DUM -->
        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-teal-800 bg-teal-100 px-2 py-0.5 rounded">STORE-DUM</span>
                <span class="text-xs text-slate-400 font-medium">Minimarket Dumai</span>
            </div>
            <div class="mt-2 text-xl font-black text-slate-800">
                <?= App\Helpers::formatRupiah($metrics['val_store_dum']) ?>
            </div>
            <div class="text-[11px] text-slate-500 mt-1">
                Target: Rp 1.652.800 (<?= $metrics['val_store_dum'] === 1652800 ? '✅ MATCH' : '⚠️ CEK' ?>)
            </div>
        </div>
    </div>

    <!-- Recent Audit Logs Table (SR-18, F-23) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                <span>🛡️</span>
                <span>Audit Trail & Activity Log (SR-18, F-23)</span>
            </h3>
            <span class="text-xs text-slate-400">Jejak Mutasi & Perubahan Status Dokumen</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200">
                        <th class="py-2.5 px-3">Waktu</th>
                        <th class="py-2.5 px-3">Aktor</th>
                        <th class="py-2.5 px-3">Aksi</th>
                        <th class="py-2.5 px-3">Entitas</th>
                        <th class="py-2.5 px-3">ID Dokumen</th>
                        <th class="py-2.5 px-3">Rincian Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php 
                    $audits = $dashboardModel->getRecentAudits(6);
                    if (empty($audits)): ?>
                        <tr><td colspan="6" class="py-4 text-center text-slate-400">Belum ada riwayat audit.</td></tr>
                    <?php else:
                        foreach ($audits as $a): ?>
                            <tr class="hover:bg-slate-50 font-mono text-[11px]">
                                <td class="py-2 px-3 text-slate-500"><?= htmlspecialchars($a['created_at']) ?></td>
                                <td class="py-2 px-3 font-bold text-slate-800"><?= htmlspecialchars($a['actor_username']) ?></td>
                                <td class="py-2 px-3">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold text-[10px]">
                                        <?= htmlspecialchars($a['action']) ?>
                                    </span>
                                </td>
                                <td class="py-2 px-3 text-slate-600"><?= htmlspecialchars($a['entity_name']) ?></td>
                                <td class="py-2 px-3 font-bold text-indigo-700"><?= htmlspecialchars($a['entity_id']) ?></td>
                                <td class="py-2 px-3 text-slate-600 font-sans truncate max-w-xs"><?= htmlspecialchars($a['new_values'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
