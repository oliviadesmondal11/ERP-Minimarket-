<div class="space-y-6">

    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <span>Sales & Procurement</span>
                <span>/</span>
                <span class="text-slate-800">Master Contacts</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">👥 Kontak, Pelanggan & Vendor (S-02)</h1>
        </div>
        <div class="flex items-center space-x-3 text-xs">
            <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-lg font-bold border border-blue-200">
                Rule SR-08: Credit Limit & Terms
            </span>
        </div>
    </div>

    <!-- Tabs: Customers & Vendors -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Customers (C001 - C006) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                <h2 class="text-sm font-bold text-slate-800 flex items-center space-x-2">
                    <span>🏢</span>
                    <span>Daftar Pelanggan B2B & Ritel (contacts_customers)</span>
                </h2>
                <span class="text-xs bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded">6 Pelanggan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-100/70 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                            <th class="py-2.5 px-3">ID</th>
                            <th class="py-2.5 px-3">Nama Pelanggan</th>
                            <th class="py-2.5 px-3">Tipe</th>
                            <th class="py-2.5 px-3">Kota</th>
                            <th class="py-2.5 px-3 text-right">Credit Limit</th>
                            <th class="py-2.5 px-3">Terms</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($customers as $c): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 font-mono font-bold text-indigo-700"><?= htmlspecialchars($c['code']) ?></td>
                                <td class="py-2.5 px-3 font-bold text-slate-900"><?= htmlspecialchars($c['name']) ?></td>
                                <td class="py-2.5 px-3"><span class="px-2 py-0.5 bg-slate-100 rounded text-[10px]"><?= htmlspecialchars($c['goods'] ?? 'B2B/Ritel') ?></span></td>
                                <td class="py-2.5 px-3 text-slate-600"><?= htmlspecialchars($c['city']) ?></td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700">
                                    <?= App\Helpers::formatRupiah($c['credit_limit']) ?>
                                </td>
                                <td class="py-2.5 px-3 font-mono text-slate-600"><?= htmlspecialchars($c['payment_terms']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vendors (V01 - V04) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                <h2 class="text-sm font-bold text-slate-800 flex items-center space-x-2">
                    <span>🏭</span>
                    <span>Daftar Pemasok Resmi (contacts_vendors)</span>
                </h2>
                <span class="text-xs bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded">4 Vendor</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-100/70 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                            <th class="py-2.5 px-3">ID</th>
                            <th class="py-2.5 px-3">Nama Vendor</th>
                            <th class="py-2.5 px-3">Kota</th>
                            <th class="py-2.5 px-3">Kategori Pasokan</th>
                            <th class="py-2.5 px-3">Terms</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($vendors as $v): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 font-mono font-bold text-amber-700"><?= htmlspecialchars($v['code']) ?></td>
                                <td class="py-2.5 px-3 font-bold text-slate-900"><?= htmlspecialchars($v['name']) ?></td>
                                <td class="py-2.5 px-3 text-slate-600"><?= htmlspecialchars($v['city']) ?></td>
                                <td class="py-2.5 px-3 text-slate-700 font-medium"><?= htmlspecialchars($v['goods']) ?></td>
                                <td class="py-2.5 px-3 font-mono text-slate-600"><?= htmlspecialchars($v['payment_terms']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
