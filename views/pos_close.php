<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <span>Point of Sale</span>
                <span>/</span>
                <span class="text-slate-800">Close Session</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">🔒 Penutupan Sesi & Penghitungan Kas Fisik (S-16)</h1>
        </div>
        <div class="flex items-center space-x-3 text-xs">
            <span class="px-3 py-1 bg-amber-50 text-amber-800 rounded-lg font-bold border border-amber-200">
                Rule SR-11 & SR-12: Rekonsiliasi Kas Counted vs Expected
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

    <!-- Session Detail Card -->
    <div class="max-w-2xl mx-auto bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6 space-y-6">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-200">
            <div>
                <span class="text-xs font-bold font-mono text-indigo-700 block">Sesi: <?= htmlspecialchars($session['session_number']) ?></span>
                <h3 class="font-bold text-slate-900 text-base">Lokasi: <?= htmlspecialchars($session['location_name'] ?? 'STORE-PKU (Pekanbaru)') ?></h3>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold <?= $session['status'] === 'opened' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' ?>">
                <?= strtoupper($session['status']) ?>
            </span>
        </div>

        <!-- Cash Count Breakdown Table -->
        <div class="space-y-3 text-xs">
            <div class="flex justify-between py-2 border-b border-slate-100">
                <span class="text-slate-500 font-semibold">1. Modal Awal Kasir (Opening Cash):</span>
                <span class="font-mono font-bold text-slate-800 text-sm"><?= App\Helpers::formatRupiah($session['opening_cash']) ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-100">
                <span class="text-slate-500 font-semibold">2. Penerimaan Tunai Penjualan (Cash Tenders):</span>
                <span class="font-mono font-bold text-emerald-700 text-sm"><?= App\Helpers::formatRupiah($session['cash_tenders'] ?? 0) ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-100">
                <span class="text-slate-500 font-semibold">3. Pengeluaran Retur Tunai (Cash Refunds):</span>
                <span class="font-mono font-bold text-rose-700 text-sm">- <?= App\Helpers::formatRupiah($session['cash_refunds'] ?? 0) ?></span>
            </div>
            <div class="flex justify-between py-3 bg-slate-50 px-3 rounded-xl font-bold text-sm">
                <span class="text-slate-800">Uang Kas Sistem yang Diharapkan (Expected Cash):</span>
                <span class="font-mono text-indigo-700" id="txtExpected"><?= App\Helpers::formatRupiah($session['expected_cash']) ?></span>
            </div>
        </div>

        <!-- Cash Count Input Form (T-31, T-32) -->
        <?php if ($session['status'] === 'opened'): ?>
            <form method="POST" action="?action=close_pos_session" class="space-y-4 pt-2">
                <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                <input type="hidden" name="expected_cash" id="valExpected" value="<?= $session['expected_cash'] ?>">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Hitungan Fisik Uang di Laci Kasir (Counted Cash IDR)
                    </label>
                    <input type="number" name="counted_cash" id="inputCounted" onkeyup="checkVariance()" 
                           value="<?= $session['expected_cash'] ?>" required 
                           class="w-full px-4 py-2.5 text-base font-mono font-black border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500">
                    <span class="text-[10px] text-slate-400">Pengujian T-31: Masukkan Rp222.644 (Variance 0) • T-32: Rp217.644 (Selisih -Rp5.000).</span>
                </div>

                <!-- Variance Display -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-600">Selisih Kas (Variance = Counted − Expected):</span>
                    <span class="font-mono font-black text-sm" id="txtVariance">Rp 0</span>
                </div>

                <div class="pt-2 flex space-x-3">
                    <button type="button" onclick="setCounted(<?= $session['expected_cash'] ?>)" 
                            class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">
                        Set Sesuai Expected (T-31)
                    </button>
                    <button type="button" onclick="setCounted(<?= $session['expected_cash'] - 5000 ?>)" 
                            class="px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold border border-rose-200">
                        Set Selisih -Rp5.000 (T-32)
                    </button>
                </div>

                <button type="submit" class="w-full py-3.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-black text-sm shadow-md transition">
                    Tutup Sesi & Posting Jurnal Settlement (T-31)
                </button>
            </form>
        <?php else: ?>
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-center text-xs font-bold text-emerald-800">
                ✅ Sesi kasir telah ditutup dan jurnal settlement penjualan telah diposting ke GL.
            </div>
        <?php endif; ?>

    </div>

</div>

<script>
function setCounted(val) {
    document.getElementById('inputCounted').value = val;
    checkVariance();
}

function checkVariance() {
    const expected = parseInt(document.getElementById('valExpected').value) || 0;
    const counted = parseInt(document.getElementById('inputCounted').value) || 0;
    const variance = counted - expected;
    const txtVariance = document.getElementById('txtVariance');

    if (variance === 0) {
        txtVariance.innerText = 'Rp 0 (Seimbang)';
        txtVariance.className = 'font-mono font-black text-sm text-emerald-700';
    } else if (variance < 0) {
        txtVariance.innerText = '- Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(variance)) + ' (Kurang)';
        txtVariance.className = 'font-mono font-black text-sm text-rose-600';
    } else {
        txtVariance.innerText = '+ Rp ' + new Intl.NumberFormat('id-ID').format(variance) + ' (Lebih)';
        txtVariance.className = 'font-mono font-black text-sm text-blue-600';
    }
}
checkVariance();
</script>
