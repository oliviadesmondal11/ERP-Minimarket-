<?php
$tab = $_GET['tab'] ?? 'invoices';
?>
<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-semibold mb-1">
                <span>Finance & Accounting</span>
                <span>/</span>
                <span class="text-slate-800">Invoicing & Ledger</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">💳 Invoicing, Tagihan & Pembukuan Ganda (S-10 s/d S-13)</h1>
        </div>
        <div class="flex items-center space-x-2 text-xs">
            <span class="px-3 py-1 bg-blue-50 text-blue-800 rounded-lg font-bold border border-blue-200">
                SR-02: Delivered Invoice • SR-06: Accepted Bill • SR-21: Balance
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

    <!-- Sub-tabs Navigation -->
    <div class="border-b border-slate-200 flex space-x-2 text-xs font-bold">
        <a href="?page=accounting&tab=invoices" 
           class="py-3 px-4 border-b-2 transition <?= $tab === 'invoices' ? 'border-[#714B67] text-[#714B67]' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            📄 Faktur Pelanggan (Customer Invoices — S-10)
        </a>
        <a href="?page=accounting&tab=bills" 
           class="py-3 px-4 border-b-2 transition <?= $tab === 'bills' ? 'border-[#714B67] text-[#714B67]' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            🧾 Tagihan Pemasok (Vendor Bills — S-11)
        </a>
        <a href="?page=accounting&tab=ledger" 
           class="py-3 px-4 border-b-2 transition <?= $tab === 'ledger' ? 'border-[#714B67] text-[#714B67]' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            📚 Jurnal Umum & Buku Besar (S-13)
        </a>
    </div>

    <!-- TAB 1: Customer Invoices -->
    <?php if ($tab === 'invoices'): ?>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-xs text-slate-500 font-semibold">Menampilkan seluruh faktur piutang pelanggan (AR).</span>
                <button onclick="document.getElementById('modalCreateInv').classList.remove('hidden')" 
                        class="px-4 py-2 bg-[#714B67] hover:bg-[#593B52] text-white rounded-lg text-xs font-bold shadow-sm transition">
                    + Buat Invoice dari Delivered Qty (T-19)
                </button>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200">
                            <th class="py-3 px-4">No. Invoice</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Pelanggan</th>
                            <th class="py-3 px-4">Referensi Dokumen</th>
                            <th class="py-3 px-4 text-right">Net Subtotal</th>
                            <th class="py-3 px-4 text-right">PPN 11%</th>
                            <th class="py-3 px-4 text-right">Total Tagihan</th>
                            <th class="py-3 px-4 text-right">Sisa Piutang (AR)</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">Tindakan Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($customerInvoices as $inv): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-mono font-bold text-indigo-700"><?= htmlspecialchars($inv['invoice_number']) ?></td>
                                <td class="py-3 px-4 text-slate-500"><?= htmlspecialchars($inv['invoice_date']) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($inv['partner_name']) ?></td>
                                <td class="py-3 px-4 font-mono text-slate-600"><?= htmlspecialchars($inv['reference_doc'] ?? '-') ?></td>
                                <td class="py-3 px-4 text-right font-mono"><?= App\Helpers::formatRupiah($inv['amount_untaxed']) ?></td>
                                <td class="py-3 px-4 text-right font-mono text-slate-500"><?= App\Helpers::formatRupiah($inv['amount_tax']) ?></td>
                                <td class="py-3 px-4 text-right font-mono font-bold"><?= App\Helpers::formatRupiah($inv['amount_total']) ?></td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-rose-700"><?= App\Helpers::formatRupiah($inv['amount_residual']) ?></td>
                                <td class="py-3 px-4">
                                    <?php if ($inv['amount_residual'] === 0): ?>
                                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">PAID</span>
                                    <?php elseif ($inv['amount_paid'] > 0): ?>
                                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">PARTIALLY PAID</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[10px]">POSTED (UNPAID)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($inv['amount_residual'] > 0): ?>
                                        <button onclick="openPaymentModal(<?= $inv['id'] ?>, '<?= $inv['invoice_number'] ?>', <?= $inv['amount_residual'] ?>, 'inbound')" 
                                                class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold transition">
                                            Bayar (T-23)
                                        </button>
                                    <?php else: ?>
                                        <span class="text-emerald-600 font-bold">Lunas</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- TAB 2: Vendor Bills -->
    <?php if ($tab === 'bills'): ?>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-xs text-slate-500 font-semibold">Menampilkan seluruh tagihan hutang pemasok (AP) — 3-Way Matching.</span>
                <button onclick="document.getElementById('modalCreateBill').classList.remove('hidden')" 
                        class="px-4 py-2 bg-[#714B67] hover:bg-[#593B52] text-white rounded-lg text-xs font-bold shadow-sm transition">
                    + Buat Vendor Bill dari Accepted Qty (T-21)
                </button>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200">
                            <th class="py-3 px-4">No. Tagihan</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Pemasok</th>
                            <th class="py-3 px-4">Referensi PO</th>
                            <th class="py-3 px-4 text-right">Net Subtotal</th>
                            <th class="py-3 px-4 text-right">PPN Masukan</th>
                            <th class="py-3 px-4 text-right">Total Tagihan</th>
                            <th class="py-3 px-4 text-right">Sisa Hutang (AP)</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">Tindakan Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($vendorBills as $bill): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 font-mono font-bold text-amber-700"><?= htmlspecialchars($bill['invoice_number']) ?></td>
                                <td class="py-3 px-4 text-slate-500"><?= htmlspecialchars($bill['invoice_date']) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($bill['partner_name']) ?></td>
                                <td class="py-3 px-4 font-mono text-slate-600"><?= htmlspecialchars($bill['reference_doc'] ?? '-') ?></td>
                                <td class="py-3 px-4 text-right font-mono"><?= App\Helpers::formatRupiah($bill['amount_untaxed']) ?></td>
                                <td class="py-3 px-4 text-right font-mono text-slate-500"><?= App\Helpers::formatRupiah($bill['amount_tax']) ?></td>
                                <td class="py-3 px-4 text-right font-mono font-bold"><?= App\Helpers::formatRupiah($bill['amount_total']) ?></td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-rose-700"><?= App\Helpers::formatRupiah($bill['amount_residual']) ?></td>
                                <td class="py-3 px-4">
                                    <?php if ($bill['amount_residual'] === 0): ?>
                                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">PAID</span>
                                    <?php elseif ($bill['amount_paid'] > 0): ?>
                                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">PARTIALLY PAID</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[10px]">POSTED (UNPAID)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($bill['amount_residual'] > 0): ?>
                                        <button onclick="openPaymentModal(<?= $bill['id'] ?>, '<?= $bill['invoice_number'] ?>', <?= $bill['amount_residual'] ?>, 'outbound')" 
                                                class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded text-xs font-bold transition">
                                            Bayar Vendor (T-24)
                                        </button>
                                    <?php else: ?>
                                        <span class="text-emerald-600 font-bold">Lunas</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- TAB 3: General Ledger -->
    <?php if ($tab === 'ledger'): ?>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-xs text-slate-500 font-semibold">Buku Jurnal Umum Berpasangan (Debit = Credit terverifikasi presisi).</span>
                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 font-bold text-xs rounded-lg border border-emerald-200">
                    Rule SR-21: Setiap Transaksi Balance
                </span>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200">
                            <th class="py-3 px-4">No. Jurnal</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Referensi / Memo</th>
                            <th class="py-3 px-4">Kode Akun</th>
                            <th class="py-3 px-4">Nama Akun Akuntansi</th>
                            <th class="py-3 px-4 text-right">Debit (IDR)</th>
                            <th class="py-3 px-4 text-right">Credit (IDR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                        <?php 
                        $totalDebit = 0;
                        $totalCredit = 0;
                        foreach ($journalItems as $ji): 
                            $totalDebit += (int)$ji['debit'];
                            $totalCredit += (int)$ji['credit'];
                        ?>
                            <tr class="hover:bg-slate-50 font-medium">
                                <td class="py-2.5 px-4 font-bold text-indigo-700"><?= htmlspecialchars($ji['entry_number']) ?></td>
                                <td class="py-2.5 px-4 text-slate-500 font-sans"><?= htmlspecialchars($ji['entry_date']) ?></td>
                                <td class="py-2.5 px-4 text-slate-700 font-sans font-semibold"><?= htmlspecialchars($ji['memo'] ?? $ji['ref_doc']) ?></td>
                                <td class="py-2.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($ji['account_code']) ?></td>
                                <td class="py-2.5 px-4 font-sans text-slate-800"><?= htmlspecialchars($ji['account_name']) ?></td>
                                <td class="py-2.5 px-4 text-right font-bold text-slate-900"><?= (int)$ji['debit'] > 0 ? App\Helpers::formatRupiah($ji['debit']) : '-' ?></td>
                                <td class="py-2.5 px-4 text-right font-bold text-slate-900"><?= (int)$ji['credit'] > 0 ? App\Helpers::formatRupiah($ji['credit']) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                        <tr>
                            <td colspan="5" class="py-3 px-4 text-right uppercase text-slate-700 font-sans">Total Keseimbangan Debit & Credit:</td>
                            <td class="py-3 px-4 text-right text-emerald-800 text-sm"><?= App\Helpers::formatRupiah($totalDebit) ?></td>
                            <td class="py-3 px-4 text-right text-emerald-800 text-sm"><?= App\Helpers::formatRupiah($totalCredit) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Modal Register Payment (T-23, T-24) -->
<div id="modalPayment" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl">
        <h3 class="text-base font-bold text-slate-900 mb-1">Catat Pembayaran (S-12)</h3>
        <p class="text-xs text-slate-500 mb-4" id="payDocInfo">Dokumen Tagihan</p>
        
        <form method="POST" action="?action=register_payment" class="space-y-3">
            <input type="hidden" name="invoice_id" id="payInvoiceId">
            <input type="hidden" name="payment_type" id="payType">
            
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Metode Jurnal Pembayaran</label>
                <select name="journal_code" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                    <option value="1010">1010 Bank (Mandiri / BCA)</option>
                    <option value="1020">1020 POS Cash Drawer</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Bayar (IDR)</label>
                <input type="number" name="amount" id="payAmountInput" min="1" required class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono font-bold text-emerald-800">
                <span class="text-[10px] text-slate-400">SR-15: Pembayaran tidak boleh melebihi sisa tagihan.</span>
            </div>
            <div class="pt-4 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalPayment').classList.add('hidden')" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-md">
                    Posting Pembayaran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Buat Invoice (T-19, T-20) -->
<div id="modalCreateInv" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-base font-bold text-slate-900 mb-1">Buat Customer Invoice dari Delivery</h3>
        <p class="text-xs text-slate-500 mb-4">Aturan SR-02: Hanya barang yang sudah terkirim (Delivered Qty) yang boleh ditagih.</p>
        <form method="POST" action="?action=create_customer_invoice" class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Sales Order yang Sudah Dikirim</label>
                <select name="so_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                    <?php foreach ($salesOrders as $so): ?>
                        <option value="<?= $so['id'] ?>"><?= htmlspecialchars($so['order_number']) ?> - <?= htmlspecialchars($so['customer_name']) ?> (Delivered: <?= (int)$so['delivered_qty'] ?> pcs)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="pt-4 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalCreateInv').classList.add('hidden')" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#714B67] hover:bg-[#593B52] text-white rounded-lg text-xs font-bold shadow-md">
                    Terbitkan Invoice (T-19)
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Buat Vendor Bill (T-21, T-22) -->
<div id="modalCreateBill" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-base font-bold text-slate-900 mb-1">Buat Vendor Bill (3-Way Matching)</h3>
        <p class="text-xs text-slate-500 mb-4">Aturan SR-06: Tagihan vendor hanya atas kuantitas yang lolos inspeksi (Accepted Qty).</p>
        <form method="POST" action="?action=create_vendor_bill" class="space-y-3">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Purchase Order yang Sudah Diterima</label>
                <select name="po_id" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-bold">
                    <?php foreach ($purchaseOrders as $po): ?>
                        <option value="<?= $po['id'] ?>"><?= htmlspecialchars($po['po_number']) ?> - <?= htmlspecialchars($po['vendor_name']) ?> (Accepted: <?= (int)$po['accepted_qty'] ?> pcs)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="pt-4 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modalCreateBill').classList.add('hidden')" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#714B67] hover:bg-[#593B52] text-white rounded-lg text-xs font-bold shadow-md">
                    Terbitkan Vendor Bill (T-21)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaymentModal(invId, docNo, residual, type) {
    document.getElementById('payInvoiceId').value = invId;
    document.getElementById('payType').value = type;
    document.getElementById('payDocInfo').innerText = docNo + ' (Sisa Saldo: Rp ' + new Intl.NumberFormat('id-ID').format(residual) + ')';
    document.getElementById('payAmountInput').value = residual;
    document.getElementById('payAmountInput').max = residual;
    document.getElementById('modalPayment').classList.remove('hidden');
}
</script>
