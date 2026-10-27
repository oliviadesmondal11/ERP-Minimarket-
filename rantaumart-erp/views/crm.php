<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <span>Sales</span>
                <span>/</span>
                <span class="text-slate-800">CRM Pipeline</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">📈 CRM Pipeline & Opportunities (S-04)</h1>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="document.getElementById('modalNewLead').classList.remove('hidden')" 
                    class="px-4 py-2 bg-[#714B67] hover:bg-[#593B52] text-white rounded-lg text-xs font-bold shadow-sm transition">
                + Tambah Lead Baru
            </button>
        </div>
    </div>

    <!-- CRM Kanban Board (New / Qualified / Won / Lost) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

        <!-- Stage: New -->
        <div class="bg-slate-200/60 rounded-xl p-4 border border-slate-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span>1. New (Baru)</span>
                </span>
                <span class="text-xs bg-white text-slate-600 px-2 py-0.5 rounded-full font-bold">
                    <?= count(array_filter($leads, fn($l) => $l['stage'] === 'lead')) ?>
                </span>
            </div>

            <div class="space-y-3">
                <?php foreach (array_filter($leads, fn($l) => $l['stage'] === 'lead') as $l): ?>
                    <div class="bg-white rounded-lg p-4 shadow-sm border border-slate-200 hover:shadow transition">
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-mono font-bold text-indigo-700"><?= htmlspecialchars($l['lead_number']) ?></span>
                            <span class="text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-bold">New Lead</span>
                        </div>
                        <h4 class="font-bold text-slate-900 text-sm mt-1"><?= htmlspecialchars($l['name']) ?></h4>
                        <div class="text-xs text-slate-500 mt-1">
                            Akun: <strong><?= htmlspecialchars($l['partner_code'] ?? 'C001') ?> - <?= htmlspecialchars($l['partner_name'] ?? 'Warung Kopi Pasar') ?></strong>
                        </div>
                        <div class="mt-2 text-xs font-mono font-bold text-emerald-700">
                            Est. Revenue: <?= App\Helpers::formatRupiah($l['expected_revenue']) ?>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <a href="?action=convert_lead&id=<?= $l['id'] ?>" 
                               class="w-full text-center py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition">
                                Convert to Opportunity →
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Stage: Qualified -->
        <div class="bg-slate-200/60 rounded-xl p-4 border border-slate-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span>2. Qualified</span>
                </span>
                <span class="text-xs bg-white text-slate-600 px-2 py-0.5 rounded-full font-bold">
                    <?= count(array_filter($leads, fn($l) => $l['stage'] === 'opportunity')) ?>
                </span>
            </div>

            <div class="space-y-3">
                <?php foreach (array_filter($leads, fn($l) => $l['stage'] === 'opportunity') as $l): ?>
                    <div class="bg-white rounded-lg p-4 shadow-sm border border-slate-200 hover:shadow transition">
                        <div class="flex justify-between items-start">
                            <span class="text-xs font-mono font-bold text-indigo-700"><?= htmlspecialchars($l['lead_number']) ?></span>
                            <span class="text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-bold">Opportunity</span>
                        </div>
                        <h4 class="font-bold text-slate-900 text-sm mt-1"><?= htmlspecialchars($l['name']) ?></h4>
                        <div class="text-xs text-slate-500 mt-1">
                            Akun: <strong><?= htmlspecialchars($l['partner_code']) ?> - <?= htmlspecialchars($l['partner_name']) ?></strong>
                        </div>
                        <div class="mt-2 text-xs font-mono font-bold text-emerald-700">
                            Est: <?= App\Helpers::formatRupiah($l['expected_revenue']) ?>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-100 flex items-center space-x-2">
                            <a href="?page=sales&new_so=1&customer=<?= $l['partner_id'] ?>" 
                               class="flex-1 text-center py-1.5 bg-[#714B67] hover:bg-[#593B52] text-white rounded text-xs font-bold transition">
                                Buat Quotation
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Stage: Won -->
        <div class="bg-slate-200/60 rounded-xl p-4 border border-slate-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span>3. Won (Menang)</span>
                </span>
                <span class="text-xs bg-white text-slate-600 px-2 py-0.5 rounded-full font-bold">
                    <?= count(array_filter($leads, fn($l) => $l['stage'] === 'won')) ?>
                </span>
            </div>
            <div class="space-y-3">
                <?php foreach (array_filter($leads, fn($l) => $l['stage'] === 'won') as $l): ?>
                    <div class="bg-white rounded-lg p-4 shadow-sm border border-slate-200">
                        <span class="text-xs font-mono font-bold text-emerald-700"><?= htmlspecialchars($l['lead_number']) ?></span>
                        <h4 class="font-bold text-slate-900 text-sm mt-1"><?= htmlspecialchars($l['name']) ?></h4>
                        <span class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-bold">Deals Won</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Stage: Lost -->
        <div class="bg-slate-200/60 rounded-xl p-4 border border-slate-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                    <span>4. Lost</span>
                </span>
                <span class="text-xs bg-white text-slate-600 px-2 py-0.5 rounded-full font-bold">0</span>
            </div>
            <p class="text-xs text-slate-400 text-center py-6">Tidak ada lead dibatalkan.</p>
        </div>

    </div>

</div>

<!-- Modal Tambah Lead Baru -->
<div id="modalNewLead" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-base font-bold text-slate-900 mb-4">Tambah Calon Pelanggan (Lead Baru)</h3>
        <form method="POST" action="?action=create_lead" class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lead / Peluang</label>
                <input type="text" name="lead_name" required placeholder="Contoh: Pasokan Mingguan Warung Kopi" 
                       class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Pelanggan</label>
                <select name="partner_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['code']) ?> - <?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi Nilai Penjualan (IDR)</label>
                <input type="number" name="expected_revenue" value="88800" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg">
            </div>
            <div class="pt-4 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalNewLead').classList.add('hidden')" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#714B67] hover:bg-[#593B52] text-white rounded-lg text-xs font-bold">Simpan Lead</button>
            </div>
        </form>
    </div>
</div>
