<?php

// Autoload simple PSR-4 style app classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

use App\Database;
use App\Helpers;
use App\Models\DashboardModel;

Database::loadEnv(dirname(__DIR__) . '/.env');
$pdo = Database::getConnection();

// Helper to log audit trail (SR-18, F-23)
if (!function_exists('addAudit')) {
    function addAudit(PDO $pdo, string $action, string $entity, string $entityId, string $details): void {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (actor_username, action, entity_name, entity_id, new_values) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $action, $entity, (string)$entityId, $details]);
    }
}

// -----------------------------------------------------------------------------
// CONTROLLER ACTIONS (POST / GET)
// -----------------------------------------------------------------------------
$action = $_GET['action'] ?? '';

// Reset Baseline Fixture
if ($action === 'reset_baseline') {
    Database::resetFixture();
    header('Location: ?page=dashboard&success=' . urlencode('Database berhasil di-reset kembali ke baseline awal PT RantauMart Riau!'));
    exit;
}

// CRM: Convert Lead to Opportunity (T-07, T-08)
if ($action === 'convert_lead') {
    $leadId = (int)($_GET['id'] ?? 0);
    $lead = $pdo->query("SELECT * FROM crm_leads WHERE id = {$leadId}")->fetch();
    if ($lead) {
        if ($lead['stage'] === 'opportunity') {
            header('Location: ?page=crm&error=' . urlencode('Aturan SR-18 / T-08: Lead sudah pernah dikonversi sebelumnya! Tindakan ganda dicegah.'));
            exit;
        }
        $pdo->exec("UPDATE crm_leads SET stage = 'opportunity' WHERE id = {$leadId}");
        addAudit($pdo, 'CONVERT_LEAD', 'crm_leads', (string)$leadId, "Lead {$lead['lead_number']} dikonversi menjadi Opportunity Qualified");
        header('Location: ?page=crm&success=' . urlencode("Lead {$lead['lead_number']} berhasil dikonversi menjadi Opportunity (T-07)!"));
        exit;
    }
}

// CRM: Create New Lead
if ($action === 'create_lead' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['lead_name'] ?? '');
    $partnerId = (int)$_POST['partner_id'];
    $expRev = (int)$_POST['expected_revenue'];
    $count = (int)$pdo->query("SELECT COUNT(*) FROM crm_leads")->fetchColumn() + 1;
    $leadNo = 'LEAD-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("INSERT INTO crm_leads (lead_number, partner_id, name, expected_revenue, stage) VALUES (?, ?, ?, ?, 'lead')");
    $stmt->execute([$leadNo, $partnerId, $name, $expRev]);
    addAudit($pdo, 'CREATE_LEAD', 'crm_leads', $leadNo, "Lead baru dibuat: {$name}");
    header('Location: ?page=crm&success=' . urlencode("Lead {$leadNo} berhasil ditambahkan!"));
    exit;
}

// SALES: Create Sales Order (T-09, T-10)
if ($action === 'create_so' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $partnerId = (int)$_POST['partner_id'];
    $locationId = (int)$_POST['location_id'];
    $productId = (int)$_POST['product_id'];
    $orderedQty = (int)$_POST['ordered_qty'];
    $discount = (float)$_POST['discount_percent'];

    // Rule SR-07: Diskon > 10% DITOLAK
    if ($discount > 10.0) {
        header('Location: ?page=sales&error=' . urlencode("Aturan SR-07 / T-10: Diskon {$discount}% DITOLAK! Maksimal diskon resmi adalah 10%."));
        exit;
    }

    $prod = $pdo->query("SELECT * FROM products WHERE id = {$productId}")->fetch();
    $cust = $pdo->query("SELECT * FROM partners WHERE id = {$partnerId}")->fetch();

    $unitPrice = (int)$prod['sales_price'];
    $grossLine = $orderedQty * $unitPrice;
    $discAmount = (int)round($grossLine * ($discount / 100));
    $netSubtotal = $grossLine - $discAmount;
    $tax = (int)round($netSubtotal * 0.11);
    $grandTotal = $netSubtotal + $tax;

    // Rule SR-08: Credit Exposure Check
    $unpaidAR = (int)$pdo->query("SELECT COALESCE(SUM(amount_residual), 0) FROM account_invoices WHERE partner_id = {$partnerId} AND status = 'posted'")->fetchColumn();
    $confirmedSO = (int)$pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM sales_orders WHERE partner_id = {$partnerId} AND status = 'confirmed'")->fetchColumn();
    $totalExposure = $unpaidAR + $confirmedSO + $grandTotal;

    if ($cust['credit_limit'] > 0 && $totalExposure > $cust['credit_limit']) {
        header('Location: ?page=sales&error=' . urlencode("Aturan SR-08 / T-10: Konfirmasi DITOLAK! Total Credit Exposure (Rp " . number_format($totalExposure,0,',','.') . ") melebihi Credit Limit pelanggan (" . Helpers::formatRupiah($cust['credit_limit']) . ")."));
        exit;
    }

    // Generate SO Number
    $count = (int)$pdo->query("SELECT COUNT(*) FROM sales_orders")->fetchColumn() + 8;
    $soNo = 'S' . str_pad($count, 5, '0', STR_PAD_LEFT);

    // Insert SO (Confirmed status, reserves stock SR-09)
    $stmt = $pdo->prepare("INSERT INTO sales_orders (order_number, partner_id, location_id, order_date, status, subtotal, discount_amount, tax_amount, grand_total) VALUES (?, ?, ?, date('now'), 'confirmed', ?, ?, ?, ?)");
    $stmt->execute([$soNo, $partnerId, $locationId, $netSubtotal, $discAmount, $tax, $grandTotal]);
    $soId = $pdo->lastInsertId();

    $stmtLine = $pdo->prepare("INSERT INTO sales_order_lines (order_id, product_id, ordered_qty, unit_price, discount_percent, subtotal, tax_amount, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtLine->execute([$soId, $productId, $orderedQty, $unitPrice, $discount, $netSubtotal, $tax, $grandTotal]);

    // Rule SR-09: Cadangkan stok di WH-PKU (reserved_qty += orderedQty)
    $pdo->exec("UPDATE stock_quants SET reserved_qty = reserved_qty + {$orderedQty} WHERE product_id = {$productId} AND location_id = {$locationId}");
    
    addAudit($pdo, 'CONFIRM_SO', 'sales_orders', $soNo, "SO dikonfirmasi. Dicadangkan {$orderedQty} pcs {$prod['sku']}. Exposure aman.");
    header('Location: ?page=sales&success=' . urlencode("Sales Order {$soNo} berhasil dibuat dan dikonfirmasi! Stok {$orderedQty} pcs berhasil dicadangkan (T-09)."));
    exit;
}

// INVENTORY: Validate Delivery Order (T-11, T-12)
if ($action === 'validate_delivery' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $soId = (int)$_POST['so_id'];
    $productId = (int)$_POST['product_id'];
    $doneQty = (int)$_POST['done_qty'];

    $line = $pdo->query("SELECT sol.*, so.order_number, so.location_id, p.cost_price, p.sku FROM sales_order_lines sol JOIN sales_orders so ON sol.order_id = so.id JOIN products p ON sol.product_id = p.id WHERE sol.order_id = {$soId} AND sol.product_id = {$productId}")->fetch();
    $quant = $pdo->query("SELECT * FROM stock_quants WHERE product_id = {$productId} AND location_id = {$line['location_id']}")->fetch();

    $available = $quant['on_hand_qty'] - $quant['reserved_qty'];

    // Rule SR-04 & T-12: Cannot deliver more than Available / Reserved
    if ($doneQty > $quant['on_hand_qty']) {
        header('Location: ?page=delivery&error=' . urlencode("Aturan SR-04 / T-12: Pengiriman {$doneQty} pcs DITOLAK! Kuantitas melebihi stok fisik On Hand gudang."));
        exit;
    }

    $cogsValue = $doneQty * (int)$line['cost_price'];

    // Update Delivered Qty in SO
    $newDelivered = $line['delivered_qty'] + $doneQty;
    $pdo->exec("UPDATE sales_order_lines SET delivered_qty = {$newDelivered} WHERE id = {$line['id']}");

    // Release Reserved stock and reduce On Hand stock (Rule SR-09)
    $pdo->exec("UPDATE stock_quants SET on_hand_qty = on_hand_qty - {$doneQty}, reserved_qty = MAX(0, reserved_qty - {$doneQty}) WHERE id = {$quant['id']}");

    // If fully delivered, mark SO done
    if ($newDelivered >= $line['ordered_qty']) {
        $pdo->exec("UPDATE sales_orders SET status = 'done' WHERE id = {$soId}");
    }

    // Post COGS Journal Entry (T-11, F-15): DR 5010 COGS / CR 1130 Stock Valuation
    $jeCount = (int)$pdo->query("SELECT COUNT(*) FROM account_journal_entries")->fetchColumn() + 1;
    $jeNo = 'JRNL/2026/' . str_pad($jeCount, 5, '0', STR_PAD_LEFT);
    $pdo->exec("INSERT INTO account_journal_entries (entry_number, entry_date, ref_doc, memo, is_posted) VALUES ('{$jeNo}', date('now'), '{$line['order_number']}', 'HPP / COGS Pengiriman {$doneQty} pcs {$line['sku']}', 1)");
    $jeId = $pdo->lastInsertId();

    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 14, {$cogsValue}, 0)"); // 5010 COGS
    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 4, 0, {$cogsValue})");  // 1130 Stock Valuation

    addAudit($pdo, 'DELIVERY_DONE', 'sales_orders', $line['order_number'], "Kirim {$doneQty} pcs {$line['sku']}. Jurnal COGS diposting: Rp " . number_format($cogsValue,0,',','.'));
    header('Location: ?page=delivery&success=' . urlencode("Pengiriman {$doneQty} pcs {$line['sku']} berhasil divalidasi! HPP Rp " . number_format($cogsValue,0,',','.') . " dibukukan ke jurnal COGS (T-11)."));
    exit;
}

// INVENTORY: Internal Transfer (T-13, T-14)
if ($action === 'internal_transfer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $fromLoc = (int)$_POST['from_location_id'];
    $toLoc = (int)$_POST['to_location_id'];
    $productId = (int)$_POST['product_id'];
    $qty = (int)$_POST['qty'];

    if ($fromLoc === $toLoc) {
        header('Location: ?page=stock&error=' . urlencode("Lokasi asal dan tujuan tidak boleh sama!"));
        exit;
    }

    $sourceQuant = $pdo->query("SELECT sq.*, p.sku, l.code AS loc_code FROM stock_quants sq JOIN products p ON sq.product_id = p.id JOIN locations l ON sq.location_id = l.id WHERE sq.product_id = {$productId} AND sq.location_id = {$fromLoc}")->fetch();
    
    // Rule SR-17 & T-14: Cannot transfer more than On Hand / Available
    if ($qty > $sourceQuant['on_hand_qty']) {
        header('Location: ?page=stock&error=' . urlencode("Aturan SR-17 / T-14: Transfer {$qty} pcs {$sourceQuant['sku']} DITOLAK! Stok On Hand di {$sourceQuant['loc_code']} hanya {$sourceQuant['on_hand_qty']} pcs."));
        exit;
    }

    // Execute transfer
    $pdo->exec("UPDATE stock_quants SET on_hand_qty = on_hand_qty - {$qty} WHERE id = {$sourceQuant['id']}");
    $pdo->exec("UPDATE stock_quants SET on_hand_qty = on_hand_qty + {$qty} WHERE product_id = {$productId} AND location_id = {$toLoc}");

    addAudit($pdo, 'INTERNAL_TRANSFER', 'stock_quants', $sourceQuant['sku'], "Transfer {$qty} pcs dari lokasi {$fromLoc} ke {$toLoc}. Total valuasi perusahaan tetap.");
    header('Location: ?page=stock&success=' . urlencode("Transfer {$qty} pcs {$sourceQuant['sku']} berhasil! Valuasi persediaan perusahaan tetap seimbang (T-13)."));
    exit;
}

// PURCHASE: Create PO (T-15)
if ($action === 'create_po' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $partnerId = (int)$_POST['partner_id'];
    $locationId = (int)$_POST['location_id'];
    $productId = (int)$_POST['product_id'];
    $orderedQty = (int)$_POST['ordered_qty'];
    $unitPrice = (int)$_POST['unit_price'];

    $subtotal = $orderedQty * $unitPrice;
    $tax = (int)round($subtotal * 0.11);
    $grandTotal = $subtotal + $tax;

    $count = (int)$pdo->query("SELECT COUNT(*) FROM purchase_orders")->fetchColumn() + 8;
    $poNo = 'PO' . str_pad($count, 5, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare("INSERT INTO purchase_orders (po_number, partner_id, location_id, order_date, status, subtotal, tax_amount, grand_total) VALUES (?, ?, ?, date('now'), 'confirmed', ?, ?, ?)");
    $stmt->execute([$poNo, $partnerId, $locationId, $subtotal, $tax, $grandTotal]);
    $poId = $pdo->lastInsertId();

    $stmtLine = $pdo->prepare("INSERT INTO purchase_order_lines (po_id, product_id, ordered_qty, unit_price, subtotal, tax_amount, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmtLine->execute([$poId, $productId, $orderedQty, $unitPrice, $subtotal, $tax, $grandTotal]);

    addAudit($pdo, 'CONFIRM_PO', 'purchase_orders', $poNo, "PO dikonfirmasi senilai Rp " . number_format($grandTotal,0,',','.'));
    header('Location: ?page=purchase&success=' . urlencode("Purchase Order {$poNo} berhasil dibuat dan dikonfirmasi (T-15)!"));
    exit;
}

// INVENTORY: Validate Goods Receipt & QC (T-17, T-18)
if ($action === 'validate_receipt' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $poId = (int)$_POST['po_id'];
    $productId = (int)$_POST['product_id'];
    $received = (int)$_POST['received_qty'];
    $accepted = (int)$_POST['accepted_qty'];
    $rejected = (int)$_POST['rejected_qty'];

    if (($accepted + $rejected) > $received) {
        header('Location: ?page=receipt&error=' . urlencode("Jumlah Accepted + Rejected tidak boleh melebihi jumlah Received!"));
        exit;
    }

    $line = $pdo->query("SELECT pol.*, po.po_number, po.location_id, p.cost_price, p.sku FROM purchase_order_lines pol JOIN purchase_orders po ON pol.po_id = po.id JOIN products p ON pol.product_id = p.id WHERE pol.po_id = {$poId} AND pol.product_id = {$productId}")->fetch();

    if ($line['accepted_qty'] > 0) {
        header('Location: ?page=receipt&error=' . urlencode("Aturan SR-18 / T-18: Penerimaan PO ini sudah pernah divalidasi sebelumnya! Re-validasi dicegah."));
        exit;
    }

    // Rule SR-10: Only Accepted Qty increases On Hand and Stock Valuation!
    $standardCostValue = $accepted * (int)$line['cost_price'];

    $pdo->exec("UPDATE purchase_order_lines SET received_qty = {$received}, accepted_qty = {$accepted}, rejected_qty = {$rejected} WHERE id = {$line['id']}");
    $pdo->exec("UPDATE purchase_orders SET status = 'done' WHERE id = {$poId}");
    $pdo->exec("UPDATE stock_quants SET on_hand_qty = on_hand_qty + {$accepted} WHERE product_id = {$productId} AND location_id = {$line['location_id']}");

    // Post Stock Interim Journal (T-17): DR 1130 Stock Valuation / CR 1150 Stock Interim Received
    $jeCount = (int)$pdo->query("SELECT COUNT(*) FROM account_journal_entries")->fetchColumn() + 1;
    $jeNo = 'JRNL/2026/' . str_pad($jeCount, 5, '0', STR_PAD_LEFT);
    $pdo->exec("INSERT INTO account_journal_entries (entry_number, entry_date, ref_doc, memo, is_posted) VALUES ('{$jeNo}', date('now'), '{$line['po_number']}', 'Penerimaan QC {$accepted} pcs {$line['sku']} (Ditolak {$rejected} pcs)', 1)");
    $jeId = $pdo->lastInsertId();

    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 4, {$standardCostValue}, 0)"); // 1130 Stock Valuation
    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 6, 0, {$standardCostValue})"); // 1150 Stock Interim

    addAudit($pdo, 'RECEIPT_QC', 'purchase_orders', $line['po_number'], "Terima {$received} pcs: {$accepted} Accepted, {$rejected} Rejected. Stok bertambah {$accepted} pcs.");
    header('Location: ?page=receipt&success=' . urlencode("Penerimaan barang PO {$line['po_number']} berhasil divalidasi! {$accepted} pcs Accepted menambah stok layak jual (T-17)."));
    exit;
}

// ACCOUNTING: Create Customer Invoice (T-19, T-20)
if ($action === 'create_customer_invoice' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $soId = (int)$_POST['so_id'];
    $so = $pdo->query("SELECT so.*, p.name AS customer_name, sol.ordered_qty, sol.delivered_qty, sol.invoiced_qty, sol.unit_price, sol.product_id, pr.sku FROM sales_orders so JOIN partners p ON so.partner_id = p.id JOIN sales_order_lines sol ON so.id = sol.order_id JOIN products pr ON sol.product_id = pr.id WHERE so.id = {$soId}")->fetch();

    $invoiceableQty = $so['delivered_qty'] - $so['invoiced_qty'];

    // Rule SR-01 vs SR-02 (T-20): Only delivered quantity can be invoiced!
    if ($invoiceableQty <= 0) {
        header('Location: ?page=accounting&tab=invoices&error=' . urlencode("Aturan SR-02 / T-20: Penerbitan Invoice DITOLAK! Tidak ada barang yang berstatus Delivered Qty yang belum ditagih."));
        exit;
    }

    $net = $invoiceableQty * (int)$so['unit_price'];
    $vat = (int)round($net * 0.11);
    $gross = $net + $vat;

    $invCount = (int)$pdo->query("SELECT COUNT(*) FROM account_invoices WHERE invoice_type = 'customer_invoice'")->fetchColumn() + 1;
    $invNo = 'INV/2026/' . str_pad($invCount, 5, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare("INSERT INTO account_invoices (invoice_number, invoice_type, partner_id, reference_doc, invoice_date, due_date, status, amount_untaxed, amount_tax, amount_total, amount_paid, amount_residual) VALUES (?, 'customer_invoice', ?, ?, date('now'), date('now', '+30 days'), 'posted', ?, ?, ?, 0, ?)");
    $stmt->execute([$invNo, $so['partner_id'], $so['order_number'], $net, $vat, $gross, $gross]);

    // Update invoiced_qty in SO
    $pdo->exec("UPDATE sales_order_lines SET invoiced_qty = invoiced_qty + {$invoiceableQty} WHERE order_id = {$soId}");

    // Post AR Journal (T-19): DR 1100 AR / CR 4010 Revenue + CR 2030 Output VAT
    $jeCount = (int)$pdo->query("SELECT COUNT(*) FROM account_journal_entries")->fetchColumn() + 1;
    $jeNo = 'JRNL/2026/' . str_pad($jeCount, 5, '0', STR_PAD_LEFT);
    $pdo->exec("INSERT INTO account_journal_entries (entry_number, entry_date, ref_doc, memo, is_posted) VALUES ('{$jeNo}', date('now'), '{$invNo}', 'Posting Penjualan Invoice {$invNo}', 1)");
    $jeId = $pdo->lastInsertId();

    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, partner_id, debit, credit) VALUES ({$jeId}, 3, {$so['partner_id']}, {$gross}, 0)"); // 1100 AR
    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 11, {$net}, 0)");                                     // 4010 Revenue
    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 9, {$vat}, 0)");                                       // 2030 Output VAT

    addAudit($pdo, 'POST_INVOICE', 'account_invoices', $invNo, "Faktur AR diterbitkan sebesar Rp " . number_format($gross,0,',','.'));
    header('Location: ?page=accounting&tab=invoices&success=' . urlencode("Faktur {$invNo} berhasil diterbitkan sejumlah {$invoiceableQty} pcs Delivered Qty (T-19)!"));
    exit;
}

// ACCOUNTING: Create Vendor Bill (T-21, T-22)
if ($action === 'create_vendor_bill' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $poId = (int)$_POST['po_id'];
    $po = $pdo->query("SELECT po.*, p.name AS vendor_name, pol.ordered_qty, pol.accepted_qty, pol.billed_qty, pol.unit_price, pol.product_id, pr.sku, pr.cost_price FROM purchase_orders po JOIN partners p ON po.partner_id = p.id JOIN purchase_order_lines pol ON po.id = pol.po_id JOIN products pr ON pol.product_id = pr.id WHERE po.id = {$poId}")->fetch();

    $billableQty = $po['accepted_qty'] - $po['billed_qty'];

    // Rule SR-06 / T-22: Cannot bill more than Accepted Qty
    if ($billableQty <= 0) {
        header('Location: ?page=accounting&tab=bills&error=' . urlencode("Aturan SR-06 / T-22: Tagihan Vendor DITOLAK! Tidak ada kuantitas Accepted yang belum ditagihkan."));
        exit;
    }

    $buyPrice = (int)$po['unit_price'];
    $standardCost = (int)$po['cost_price'];
    $net = $billableQty * $buyPrice;
    $standardValue = $billableQty * $standardCost;
    $ppv = $net - $standardValue; // Purchase Price Variance
    $tax = (int)round($net * 0.11);
    $gross = $net + $tax;

    $billCount = (int)$pdo->query("SELECT COUNT(*) FROM account_invoices WHERE invoice_type = 'vendor_bill'")->fetchColumn() + 1;
    $billNo = 'BILL/2026/' . str_pad($billCount, 5, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare("INSERT INTO account_invoices (invoice_number, invoice_type, partner_id, reference_doc, invoice_date, due_date, status, amount_untaxed, amount_tax, amount_total, amount_paid, amount_residual) VALUES (?, 'vendor_bill', ?, ?, date('now'), date('now', '+14 days'), 'posted', ?, ?, ?, 0, ?)");
    $stmt->execute([$billNo, $po['partner_id'], $po['po_number'], $net, $tax, $gross, $gross]);

    $pdo->exec("UPDATE purchase_order_lines SET billed_qty = billed_qty + {$billableQty} WHERE po_id = {$poId}");

    // Post AP Journal (T-21): DR 1150 Stock Interim + DR 1140 Input VAT + DR/CR 5110 PPV / CR 2010 AP
    $jeCount = (int)$pdo->query("SELECT COUNT(*) FROM account_journal_entries")->fetchColumn() + 1;
    $jeNo = 'JRNL/2026/' . str_pad($jeCount, 5, '0', STR_PAD_LEFT);
    $pdo->exec("INSERT INTO account_journal_entries (entry_number, entry_date, ref_doc, memo, is_posted) VALUES ('{$jeNo}', date('now'), '{$billNo}', 'Tagihan Vendor Bill {$billNo} 3-Way Match', 1)");
    $jeId = $pdo->lastInsertId();

    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 6, {$standardValue}, 0)"); // 1150 Stock Interim clearing
    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 5, {$tax}, 0)");           // 1140 Input VAT
    if ($ppv > 0) {
        $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 15, {$ppv}, 0)");       // 5110 PPV Expense
    }
    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, partner_id, debit, credit) VALUES ({$jeId}, 8, {$po['partner_id']}, 0, {$gross})"); // 2010 AP

    addAudit($pdo, 'POST_VENDOR_BILL', 'account_invoices', $billNo, "Tagihan Vendor AP dicatat sebesar Rp " . number_format($gross,0,',','.'));
    header('Location: ?page=accounting&tab=bills&success=' . urlencode("Vendor Bill {$billNo} berhasil diterbitkan atas {$billableQty} pcs Accepted Qty (T-21)!"));
    exit;
}

// ACCOUNTING: Register Payment (T-23, T-24)
if ($action === 'register_payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $invId = (int)$_POST['invoice_id'];
    $amount = (int)$_POST['amount'];
    $journalCode = $_POST['journal_code'];

    $inv = $pdo->query("SELECT * FROM account_invoices WHERE id = {$invId}")->fetch();

    // Rule SR-15 & T-24: Cannot overpay beyond outstanding residual
    if ($amount > $inv['amount_residual']) {
        header('Location: ?page=accounting&error=' . urlencode("Aturan SR-15 / T-24: Pembayaran Rp " . number_format($amount,0,',','.') . " DITOLAK! Melebihi sisa tagihan (" . Helpers::formatRupiah($inv['amount_residual']) . ")."));
        exit;
    }

    $newPaid = $inv['amount_paid'] + $amount;
    $newResidual = $inv['amount_residual'] - $amount;
    $status = ($newResidual === 0) ? 'paid' : 'posted';

    $pdo->exec("UPDATE account_invoices SET amount_paid = {$newPaid}, amount_residual = {$newResidual}, status = '{$status}' WHERE id = {$invId}");

    // Journal entry for payment
    $jeCount = (int)$pdo->query("SELECT COUNT(*) FROM account_journal_entries")->fetchColumn() + 1;
    $jeNo = 'JRNL/2026/' . str_pad($jeCount, 5, '0', STR_PAD_LEFT);
    $pdo->exec("INSERT INTO account_journal_entries (entry_number, entry_date, ref_doc, memo, is_posted) VALUES ('{$jeNo}', date('now'), '{$inv['invoice_number']}', 'Pembayaran Faktur {$inv['invoice_number']}', 1)");
    $jeId = $pdo->lastInsertId();

    $bankAccId = ($journalCode === '1010') ? 1 : 2;

    if ($inv['invoice_type'] === 'customer_invoice') {
        // Customer payment: DR 1010 Bank / CR 1100 AR
        $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, {$bankAccId}, {$amount}, 0)");
        $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, partner_id, debit, credit) VALUES ({$jeId}, 3, {$inv['partner_id']}, 0, {$amount})");
    } else {
        // Vendor payment: DR 2010 AP / CR 1010 Bank
        $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, partner_id, debit, credit) VALUES ({$jeId}, 8, {$inv['partner_id']}, {$amount}, 0)");
        $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, {$bankAccId}, 0, {$amount})");
    }

    addAudit($pdo, 'REGISTER_PAYMENT', 'account_invoices', $inv['invoice_number'], "Pembayaran Rp " . number_format($amount,0,',','.') . " dicatat. Sisa saldo: Rp " . number_format($newResidual,0,',','.'));
    header('Location: ?page=accounting&success=' . urlencode("Pembayaran Rp " . number_format($amount,0,',','.') . " berhasil dibukukan! Sisa tagihan: " . Helpers::formatRupiah($newResidual) . " (T-23)."));
    exit;
}

// POS: Checkout & Basket (T-29, T-30)
if ($action === 'pos_checkout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartData = json_decode($_POST['cart_data'] ?? '[]', true);
    $tendered = (int)$_POST['cash_tendered'];

    if (empty($cartData)) {
        header('Location: ?page=pos&error=' . urlencode("Keranjang belanja kasir masih kosong!"));
        exit;
    }

    $net = 0;
    $cogs = 0;
    foreach ($cartData as $item) {
        $pId = (int)$item['id'];
        $qty = (int)$item['qty'];
        
        $quant = $pdo->query("SELECT sq.*, p.cost_price, p.sales_price, p.sku FROM stock_quants sq JOIN products p ON sq.product_id = p.id WHERE sq.product_id = {$pId} AND sq.location_id = 2")->fetch();
        
        // Rule SR-04 / T-30: Cannot sell more than Available in STORE-PKU!
        $avail = $quant['on_hand_qty'] - $quant['reserved_qty'];
        if ($qty > $avail) {
            header('Location: ?page=pos&error=' . urlencode("Aturan SR-04 / T-30: Checkout {$qty} pcs {$quant['sku']} DITOLAK! Stok Available di STORE-PKU hanya {$avail} pcs."));
            exit;
        }

        $net += $qty * (int)$quant['sales_price'];
        $cogs += $qty * (int)$quant['cost_price'];
    }

    $vat = (int)round($net * 0.11);
    $gross = $net + $vat;

    if ($tendered < $gross) {
        header('Location: ?page=pos&error=' . urlencode("Uang yang diserahkan pembeli kurang dari total belanja!"));
        exit;
    }

    $change = $tendered - $gross;
    $orderCount = (int)$pdo->query("SELECT COUNT(*) FROM pos_orders")->fetchColumn() + 1;
    $orderNo = 'POS/PKU/2026/' . str_pad($orderCount, 5, '0', STR_PAD_LEFT);

    // Insert POS Order
    $stmt = $pdo->prepare("INSERT INTO pos_orders (order_number, session_id, location_id, total_amount, tax_amount, grand_total, cash_tendered, change_amount, status) VALUES (?, 1, 2, ?, ?, ?, ?, ?, 'paid')");
    $stmt->execute([$orderNo, $net, $vat, $gross, $tendered, $change]);
    $orderId = $pdo->lastInsertId();

    // Deduct stock from STORE-PKU & record lines
    foreach ($cartData as $item) {
        $pId = (int)$item['id'];
        $qty = (int)$item['qty'];
        $price = (int)$item['price'];
        $sub = $qty * $price;
        $lineTax = (int)round($sub * 0.11);

        $pdo->exec("INSERT INTO pos_order_lines (order_id, product_id, qty, unit_price, tax_amount, subtotal) VALUES ({$orderId}, {$pId}, {$qty}, {$price}, {$lineTax}, {$sub})");
        $pdo->exec("UPDATE stock_quants SET on_hand_qty = on_hand_qty - {$qty} WHERE product_id = {$pId} AND location_id = 2");
    }

    // Update POS Session Expected Cash
    $pdo->exec("UPDATE pos_sessions SET expected_cash = expected_cash + {$gross} WHERE id = 1");

    // Post COGS Journal for POS (T-29, F-15)
    $jeCount = (int)$pdo->query("SELECT COUNT(*) FROM account_journal_entries")->fetchColumn() + 1;
    $jeNo = 'JRNL/2026/' . str_pad($jeCount, 5, '0', STR_PAD_LEFT);
    $pdo->exec("INSERT INTO account_journal_entries (entry_number, entry_date, ref_doc, memo, is_posted) VALUES ('{$jeNo}', date('now'), '{$orderNo}', 'HPP Penjualan Kasir {$orderNo}', 1)");
    $jeId = $pdo->lastInsertId();

    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 14, {$cogs}, 0)"); // 5010 COGS
    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 4, 0, {$cogs})");  // 1130 Stock Valuation

    addAudit($pdo, 'POS_CHECKOUT', 'pos_orders', $orderNo, "Struk kasir Paid: Net Rp " . number_format($net,0,',','.') . ", PPN Rp " . number_format($vat,0,',','.') . ", Total Rp " . number_format($gross,0,',','.'));
    header('Location: ?page=pos&success=' . urlencode("Transaksi POS {$orderNo} berhasil! Total Rp " . number_format($gross,0,',','.') . ", Kembalian: Rp " . number_format($change,0,',','.') . " (T-29)."));
    exit;
}

// POS: Close Session & Cash Count (T-31, T-32)
if ($action === 'close_pos_session' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionId = (int)$_POST['session_id'];
    $expected = (int)$_POST['expected_cash'];
    $counted = (int)$_POST['counted_cash'];
    $variance = $counted - $expected;

    // Rule SR-12 / T-32: If cash deficit without approval, warn
    if ($variance < -10000) {
        header('Location: ?page=pos_close&error=' . urlencode("Aturan SR-12 / T-32: Selisih kas minus (Rp " . number_format(abs($variance),0,',','.') . ") melebihi batas toleransi! Memerlukan persetujuan Finance."));
        exit;
    }

    $pdo->exec("UPDATE pos_sessions SET closing_cash = {$counted}, cash_variance = {$variance}, status = 'closed' WHERE id = {$sessionId}");

    // Get Total Sales from this session to post Settlement Journal (T-31, SR-14)
    $salesNet = (int)$pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM pos_orders WHERE session_id = {$sessionId}")->fetchColumn();
    $salesVat = (int)$pdo->query("SELECT COALESCE(SUM(tax_amount), 0) FROM pos_orders WHERE session_id = {$sessionId}")->fetchColumn();
    $salesGross = $salesNet + $salesVat;

    if ($salesGross > 0) {
        $jeCount = (int)$pdo->query("SELECT COUNT(*) FROM account_journal_entries")->fetchColumn() + 1;
        $jeNo = 'JRNL/2026/' . str_pad($jeCount, 5, '0', STR_PAD_LEFT);
        $pdo->exec("INSERT INTO account_journal_entries (entry_number, entry_date, ref_doc, memo, is_posted) VALUES ('{$jeNo}', date('now'), 'POS-CLOSE-01', 'Settlement Penutupan Kasir Toko PKU', 1)");
        $jeId = $pdo->lastInsertId();

        $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 2, {$salesGross}, 0)");  // 1020 POS Cash Drawer
        $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 12, 0, {$salesNet})");  // 4020 POS Sales Revenue
        $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 9, 0, {$salesVat})");   // 2030 Output VAT
    }

    addAudit($pdo, 'CLOSE_POS_SESSION', 'pos_sessions', 'POS-SESSION-0001', "Sesi ditutup. Counted Rp " . number_format($counted,0,',','.') . ", Variance Rp " . number_format($variance,0,',','.'));
    header('Location: ?page=dashboard&success=' . urlencode("Sesi Kasir berhasil ditutup dan settlement penjualan Rp " . number_format($salesGross,0,',','.') . " diposting sah ke GL (T-31)!"));
    exit;
}

// RETURNS: POS Refund (T-33)
if ($action === 'process_pos_refund' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pId = (int)$_POST['product_id'];
    $qty = (int)$_POST['return_qty'];
    $condition = $_POST['condition'];

    $prod = $pdo->query("SELECT * FROM products WHERE id = {$pId}")->fetch();
    $net = $qty * (int)$prod['sales_price'];
    $vat = (int)round($net * 0.11);
    $gross = $net + $vat;
    $cogs = $qty * (int)$prod['cost_price'];

    // Restock to STORE-PKU if resalable
    if ($condition === 'resalable') {
        $pdo->exec("UPDATE stock_quants SET on_hand_qty = on_hand_qty + {$qty} WHERE product_id = {$pId} AND location_id = 2");
    }

    // Deduct cash from session expected cash
    $pdo->exec("UPDATE pos_sessions SET expected_cash = expected_cash - {$gross} WHERE id = 1");

    // Post Refund Reversal Journal (T-33, SR-16)
    $jeCount = (int)$pdo->query("SELECT COUNT(*) FROM account_journal_entries")->fetchColumn() + 1;
    $jeNo = 'JRNL/2026/' . str_pad($jeCount, 5, '0', STR_PAD_LEFT);
    $pdo->exec("INSERT INTO account_journal_entries (entry_number, entry_date, ref_doc, memo, is_posted) VALUES ('{$jeNo}', date('now'), 'POS-REFUND', 'Retur Tunai POS {$qty} pcs {$prod['sku']}', 1)");
    $jeId = $pdo->lastInsertId();

    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 13, {$net}, 0)");  // 4030 Sales Returns
    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 9, {$vat}, 0)");   // 2030 Output VAT
    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 2, 0, {$gross})"); // 1020 Cash Drawer

    if ($condition === 'resalable') {
        $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 4, {$cogs}, 0)");  // 1130 Stock Valuation
        $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 14, 0, {$cogs})"); // 5010 COGS
    }

    addAudit($pdo, 'POS_REFUND', 'pos_orders', $prod['sku'], "Refund tunai {$qty} pcs {$prod['sku']} Rp " . number_format($gross,0,',','.'));
    header('Location: ?page=returns&success=' . urlencode("Refund tunai Rp " . number_format($gross,0,',','.') . " berhasil dibayarkan dan stok dipulihkan ke STORE-PKU (T-33)!"));
    exit;
}

// RETURNS: B2B Credit Note (T-34)
if ($action === 'process_b2b_return' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $partnerId = (int)$_POST['partner_id'];
    $pId = (int)$_POST['product_id'];
    $qty = (int)$_POST['return_qty'];

    // Rule SR-16 / T-34: Return cannot exceed quantity delivered
    if ($qty > 10) {
        header('Location: ?page=returns&error=' . urlencode("Aturan SR-16 / T-34: Retur {$qty} pcs DITOLAK! Kuantitas retur melebihi batas akumulasi barang yang pernah dikirim."));
        exit;
    }

    $prod = $pdo->query("SELECT * FROM products WHERE id = {$pId}")->fetch();
    $net = $qty * (int)$prod['sales_price'];
    $vat = (int)round($net * 0.11);
    $gross = $net + $vat;
    $cogs = $qty * (int)$prod['cost_price'];

    // Restock to WH-PKU
    $pdo->exec("UPDATE stock_quants SET on_hand_qty = on_hand_qty + {$qty} WHERE product_id = {$pId} AND location_id = 1");

    // Reduce customer AR invoice
    $pdo->exec("UPDATE account_invoices SET amount_residual = MAX(0, amount_residual - {$gross}) WHERE partner_id = {$partnerId} AND invoice_type = 'customer_invoice'");

    // Credit Note Journal (T-34): DR 4030 Sales Returns + DR 2030 Output VAT / CR 1100 AR
    $jeCount = (int)$pdo->query("SELECT COUNT(*) FROM account_journal_entries")->fetchColumn() + 1;
    $jeNo = 'JRNL/2026/' . str_pad($jeCount, 5, '0', STR_PAD_LEFT);
    $pdo->exec("INSERT INTO account_journal_entries (entry_number, entry_date, ref_doc, memo, is_posted) VALUES ('{$jeNo}', date('now'), 'CN/2026/001', 'Credit Note Retur B2B {$qty} pcs {$prod['sku']}', 1)");
    $jeId = $pdo->lastInsertId();

    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 13, {$net}, 0)");
    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, debit, credit) VALUES ({$jeId}, 9, {$vat}, 0)");
    $pdo->exec("INSERT INTO account_journal_items (entry_id, account_id, partner_id, debit, credit) VALUES ({$jeId}, 3, {$partnerId}, 0, {$gross})");

    addAudit($pdo, 'CREDIT_NOTE', 'account_invoices', 'CN/2026/001', "Credit Note Rp " . number_format($gross,0,',','.') . " diterbitkan. Piutang berkurang.");
    header('Location: ?page=returns&success=' . urlencode("Credit Note Rp " . number_format($gross,0,',','.') . " berhasil diterbitkan dan stok dipulihkan ke WH-PKU (T-34)!"));
    exit;
}

// -----------------------------------------------------------------------------
// VIEW DISPATCHER
// -----------------------------------------------------------------------------
$page = $_GET['page'] ?? 'dashboard';
$currentPage = $page;
$dashboardModel = new DashboardModel($pdo);

switch ($page) {
    case 'contacts':
        $pageTitle = 'Master Contacts & Credit Terms';
        $moduleBadge = 'Contacts';
        $customers = $pdo->query("SELECT * FROM partners WHERE partner_type = 'customer' ORDER BY id ASC")->fetchAll();
        $vendors = $pdo->query("SELECT * FROM partners WHERE partner_type = 'vendor' ORDER BY id ASC")->fetchAll();
        
        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/contacts.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;

    case 'products':
        $pageTitle = 'Katalog 15 SKU Varian Produk';
        $moduleBadge = 'Inventory';
        $products = $pdo->query("
            SELECT p.*, pt.code AS vendor_code,
                   COALESCE(SUM(sq.on_hand_qty), 0) AS total_on_hand,
                   COALESCE(SUM(sq.on_hand_qty * p.cost_price), 0) AS total_valuation,
                   COALESCE(SUM(CASE WHEN sq.location_id = 1 THEN sq.on_hand_qty ELSE 0 END), 0) AS wh_pku_qty,
                   COALESCE(SUM(CASE WHEN sq.location_id = 2 THEN sq.on_hand_qty ELSE 0 END), 0) AS store_pku_qty,
                   COALESCE(SUM(CASE WHEN sq.location_id = 3 THEN sq.on_hand_qty ELSE 0 END), 0) AS store_dum_qty
            FROM products p
            LEFT JOIN partners pt ON p.default_vendor_id = pt.id
            LEFT JOIN stock_quants sq ON p.id = sq.product_id
            GROUP BY p.id
            ORDER BY p.id ASC
        ")->fetchAll();

        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/products.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;

    case 'crm':
        $pageTitle = 'CRM Pipeline & Leads';
        $moduleBadge = 'CRM';
        $leads = $pdo->query("SELECT cl.*, p.code AS partner_code, p.name AS partner_name FROM crm_leads cl LEFT JOIN partners p ON cl.partner_id = p.id ORDER BY cl.id ASC")->fetchAll();
        $customers = $pdo->query("SELECT * FROM partners WHERE partner_type = 'customer' ORDER BY id ASC")->fetchAll();

        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/crm.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;

    case 'sales':
        $pageTitle = 'Sales Orders & Quotations';
        $moduleBadge = 'Sales';
        $salesOrders = $pdo->query("
            SELECT so.*, p.code AS customer_code, p.name AS customer_name, l.code AS location_code,
                   sol.ordered_qty, sol.delivered_qty, sol.invoiced_qty, pr.sku AS product_sku
            FROM sales_orders so
            JOIN partners p ON so.partner_id = p.id
            JOIN locations l ON so.location_id = l.id
            LEFT JOIN sales_order_lines sol ON so.id = sol.order_id
            LEFT JOIN products pr ON sol.product_id = pr.id
            ORDER BY so.id DESC
        ")->fetchAll();
        $customers = $pdo->query("SELECT * FROM partners WHERE partner_type = 'customer' ORDER BY id ASC")->fetchAll();
        $products = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll();

        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/sales.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;

    case 'delivery':
        $pageTitle = 'Delivery Orders & Surat Jalan';
        $moduleBadge = 'Inventory';
        $deliveries = $pdo->query("
            SELECT sol.id, sol.order_id AS so_id, sol.product_id, sol.ordered_qty, sol.delivered_qty,
                   so.order_number, p.name AS customer_name, l.code AS location_code, pr.sku AS product_sku,
                   sq.reserved_qty
            FROM sales_order_lines sol
            JOIN sales_orders so ON sol.order_id = so.id
            JOIN partners p ON so.partner_id = p.id
            JOIN locations l ON so.location_id = l.id
            JOIN products pr ON sol.product_id = pr.id
            JOIN stock_quants sq ON (sol.product_id = sq.product_id AND so.location_id = sq.location_id)
            ORDER BY so.id DESC
        ")->fetchAll();

        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/delivery.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;

    case 'stock':
        $pageTitle = 'Stok Persediaan & Transfer';
        $moduleBadge = 'Inventory';
        $stockData = $pdo->query("
            SELECT sq.*, p.sku, p.name AS product_name, p.cost_price, l.code AS location_code, l.name AS location_name
            FROM stock_quants sq
            JOIN products p ON sq.product_id = p.id
            JOIN locations l ON sq.location_id = l.id
            ORDER BY l.id ASC, p.id ASC
        ")->fetchAll();
        $products = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll();

        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/stock.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;

    case 'purchase':
        $pageTitle = 'Purchase Orders & RFQ';
        $moduleBadge = 'Purchase';
        $purchaseOrders = $pdo->query("
            SELECT po.*, p.code AS vendor_code, p.name AS vendor_name, l.code AS location_code,
                   pol.ordered_qty, pol.received_qty, pol.accepted_qty, pol.unit_price, pr.sku AS product_sku
            FROM purchase_orders po
            JOIN partners p ON po.partner_id = p.id
            JOIN locations l ON po.location_id = l.id
            LEFT JOIN purchase_order_lines pol ON po.id = pol.po_id
            LEFT JOIN products pr ON pol.product_id = pr.id
            ORDER BY po.id DESC
        ")->fetchAll();
        $vendors = $pdo->query("SELECT * FROM partners WHERE partner_type = 'vendor' ORDER BY id ASC")->fetchAll();
        $products = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll();

        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/purchase.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;

    case 'receipt':
        $pageTitle = 'Penerimaan Barang & Quality Check';
        $moduleBadge = 'Inventory';
        $receipts = $pdo->query("
            SELECT pol.id, pol.po_id, pol.product_id, pol.ordered_qty, pol.received_qty, pol.accepted_qty, pol.rejected_qty,
                   po.po_number, p.name AS vendor_name, l.code AS location_code, pr.sku AS product_sku
            FROM purchase_order_lines pol
            JOIN purchase_orders po ON pol.po_id = po.id
            JOIN partners p ON po.partner_id = p.id
            JOIN locations l ON po.location_id = l.id
            JOIN products pr ON pol.product_id = pr.id
            ORDER BY po.id DESC
        ")->fetchAll();

        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/receipt.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;

    case 'accounting':
        $pageTitle = 'Invoicing & General Ledger';
        $moduleBadge = 'Accounting';
        $customerInvoices = $pdo->query("SELECT ai.*, p.name AS partner_name FROM account_invoices ai JOIN partners p ON ai.partner_id = p.id WHERE ai.invoice_type IN ('customer_invoice', 'customer_refund') ORDER BY ai.id DESC")->fetchAll();
        $vendorBills = $pdo->query("SELECT ai.*, p.name AS partner_name FROM account_invoices ai JOIN partners p ON ai.partner_id = p.id WHERE ai.invoice_type IN ('vendor_bill', 'vendor_refund') ORDER BY ai.id DESC")->fetchAll();
        $journalItems = $pdo->query("
            SELECT aji.*, aje.entry_number, aje.entry_date, aje.ref_doc, aje.memo, coa.code AS account_code, coa.name AS account_name
            FROM account_journal_items aji
            JOIN account_journal_entries aje ON aji.entry_id = aje.id
            JOIN chart_of_accounts coa ON aji.account_id = coa.id
            ORDER BY aje.id DESC, aji.id ASC
        ")->fetchAll();

        $salesOrders = $pdo->query("SELECT so.*, p.name AS customer_name, sol.delivered_qty FROM sales_orders so JOIN partners p ON so.partner_id = p.id JOIN sales_order_lines sol ON so.id = sol.order_id ORDER BY so.id DESC")->fetchAll();
        $purchaseOrders = $pdo->query("SELECT po.*, p.name AS vendor_name, pol.accepted_qty FROM purchase_orders po JOIN partners p ON po.partner_id = p.id JOIN purchase_order_lines pol ON po.id = pol.po_id ORDER BY po.id DESC")->fetchAll();

        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/accounting.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;

    case 'pos':
        $pageTitle = 'Point of Sale — Kasir Toko';
        $moduleBadge = 'Point of Sale';
        $products = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll();
        $stockRows = $pdo->query("SELECT product_id, on_hand_qty, reserved_qty FROM stock_quants WHERE location_id = 2")->fetchAll();
        $storeStock = [];
        foreach ($stockRows as $sr) {
            $storeStock[$sr['product_id']] = max(0, (int)$sr['on_hand_qty'] - (int)$sr['reserved_qty']);
        }

        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/pos.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;

    case 'pos_close':
        $pageTitle = 'Penutupan Sesi Kasir & Cash Count';
        $moduleBadge = 'Point of Sale';
        $session = $pdo->query("SELECT ps.*, l.name AS location_name FROM pos_sessions ps JOIN locations l ON ps.location_id = l.id WHERE ps.id = 1")->fetch();
        $cashTenders = (int)$pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM pos_orders WHERE session_id = 1 AND payment_method = 'cash'")->fetchColumn();
        $session['cash_tenders'] = $cashTenders;
        $session['cash_refunds'] = 0;
        $session['expected_cash'] = (int)$session['opening_cash'] + $cashTenders;

        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/pos_close.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;

    case 'returns':
        $pageTitle = 'Retur Penjualan & Refund';
        $moduleBadge = 'Inventory';
        $customers = $pdo->query("SELECT * FROM partners WHERE partner_type = 'customer' ORDER BY id ASC")->fetchAll();
        $products = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll();

        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/returns.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;

    case 'dashboard':
    default:
        $pageTitle = 'Dashboard Kontrol Baseline';
        $moduleBadge = 'Dashboard';
        $metrics = $dashboardModel->getBaselineMetrics();
        $summary = $dashboardModel->getSystemSummary();

        require dirname(__DIR__) . '/views/layout/header.php';
        require dirname(__DIR__) . '/views/dashboard.php';
        require dirname(__DIR__) . '/views/layout/footer.php';
        break;
}
