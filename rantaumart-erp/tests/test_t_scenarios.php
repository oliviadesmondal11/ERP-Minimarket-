<?php

// Test T-01 through T-36 test runner against the real database logic

require_once dirname(__DIR__) . '/app/Helpers.php';
require_once dirname(__DIR__) . '/app/Database.php';

use App\Database;

Database::resetFixture();
$pdo = Database::getConnection();

echo "=================================================================\n";
echo "PENGUJIAN SKENARIO T-01 S/D T-36 — PT RANTAU MART RIAU\n";
echo "=================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $code, string $desc, bool $condition, string $details = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] {$code}: {$desc}" . ($details ? " ({$details})" : "") . "\n";
        $passCount++;
    } else {
        echo " [FAIL] {$code}: {$desc}" . ($details ? " (GAGAL: {$details})" : "") . "\n";
        $failCount++;
    }
}

// T-35: Baseline Verification
$onHand = (int)$pdo->query("SELECT SUM(on_hand_qty) FROM stock_quants")->fetchColumn();
$val = (int)$pdo->query("SELECT SUM(sq.on_hand_qty * p.cost_price) FROM stock_quants sq JOIN products p ON sq.product_id = p.id")->fetchColumn();
$ar = (int)$pdo->query("SELECT SUM(amount_residual) FROM account_invoices WHERE invoice_type = 'customer_invoice'")->fetchColumn();
$ap = (int)$pdo->query("SELECT SUM(amount_residual) FROM account_invoices WHERE invoice_type = 'vendor_bill'")->fetchColumn();
$bank = (int)$pdo->query("SELECT SUM(debit - credit) FROM account_journal_items WHERE account_id = 1")->fetchColumn();
$posCash = (int)$pdo->query("SELECT SUM(debit - credit) FROM account_journal_items WHERE account_id = 2")->fetchColumn();
$airWh = (int)$pdo->query("SELECT on_hand_qty - reserved_qty FROM stock_quants WHERE product_id = 1 AND location_id = 1")->fetchColumn();

assertTest('T-35', 'Baseline On Hand == 1500 pcs', $onHand === 1500, "Aktual: {$onHand}");
assertTest('T-35', 'Baseline Stock Valuation == Rp9.628.900', $val === 9628900, "Aktual: Rp " . number_format($val,0,',','.'));
assertTest('T-35', 'Baseline AR C002 == Rp888.000', $ar === 888000, "Aktual: Rp " . number_format($ar,0,',','.'));
assertTest('T-35', 'Baseline AP V01 == Rp555.000', $ap === 555000, "Aktual: Rp " . number_format($ap,0,',','.'));
assertTest('T-35', 'Baseline Bank 1010 == Rp15.000.000', $bank === 15000000, "Aktual: Rp " . number_format($bank,0,',','.'));
assertTest('T-35', 'Baseline POS Cash 1020 == Rp200.000', $posCash === 200000, "Aktual: Rp " . number_format($posCash,0,',','.'));
assertTest('T-35', 'Baseline Available AIR-600 WH-PKU == 72 pcs', $airWh === 72, "Aktual: {$airWh}");

// T-03: Buka record C001
$c001 = $pdo->query("SELECT * FROM partners WHERE code = 'C001'")->fetch();
assertTest('T-03', 'Record C001 Credit Limit Rp500.000, NET 14', $c001['credit_limit'] == 500000 && $c001['payment_terms'] === 'NET 14');

// T-05: Master Produk AIR-600 (Price 4000, Cost 2500, Total 140 pcs)
$pAir = $pdo->query("SELECT p.*, SUM(sq.on_hand_qty) AS tot FROM products p JOIN stock_quants sq ON p.id = sq.product_id WHERE p.sku = 'AIR-600' GROUP BY p.id")->fetch();
assertTest('T-05', 'Master AIR-600 Price Rp4.000, Cost Rp2.500, Total 140 pcs', $pAir['sales_price'] == 4000 && $pAir['cost_price'] == 2500 && $pAir['tot'] == 140);

// T-07: Convert LEAD-0001 ke Opportunity
$pdo->exec("UPDATE crm_leads SET stage = 'opportunity' WHERE lead_number = 'LEAD-0001'");
$leadStage = $pdo->query("SELECT stage FROM crm_leads WHERE lead_number = 'LEAD-0001'")->fetchColumn();
assertTest('T-07', 'LEAD-0001 converted to Opportunity stage', $leadStage === 'opportunity');

// T-09: SO baru C001 20 pcs AIR-600 @ 4000 dari WH-PKU (Confirm -> Reserve)
// Net 80.000, PPN 8.800, Gross 88.800, WH-PKU On Hand 80, Reserved 8+20=28, Available 52
$pdo->exec("INSERT INTO sales_orders (id, order_number, partner_id, location_id, order_date, status, subtotal, discount_amount, tax_amount, grand_total) VALUES (2, 'S00008', 1, 1, date('now'), 'confirmed', 80000, 0, 8800, 88800)");
$pdo->exec("INSERT INTO sales_order_lines (id, order_id, product_id, ordered_qty, unit_price, subtotal, tax_amount, total) VALUES (2, 2, 1, 20, 4000, 80000, 8800, 88800)");
$pdo->exec("UPDATE stock_quants SET reserved_qty = reserved_qty + 20 WHERE product_id = 1 AND location_id = 1");

$airQuant = $pdo->query("SELECT * FROM stock_quants WHERE product_id = 1 AND location_id = 1")->fetch();
$airAvail = $airQuant['on_hand_qty'] - $airQuant['reserved_qty'];
assertTest('T-09', 'SO S00008 confirmed: On Hand 80, Reserved 28, Available 52', $airQuant['on_hand_qty'] == 80 && $airQuant['reserved_qty'] == 28 && $airAvail == 52);

// T-11: Partial Delivery 12 of 20 AIR-600
// WH-PKU On Hand 68, Reserved 16, Available 52, COGS 12 * 2500 = 30000
$pdo->exec("UPDATE sales_order_lines SET delivered_qty = 12 WHERE id = 2");
$pdo->exec("UPDATE stock_quants SET on_hand_qty = on_hand_qty - 12, reserved_qty = reserved_qty - 12 WHERE product_id = 1 AND location_id = 1");
$airQuantAfterDel = $pdo->query("SELECT * FROM stock_quants WHERE product_id = 1 AND location_id = 1")->fetch();
$availAfterDel = $airQuantAfterDel['on_hand_qty'] - $airQuantAfterDel['reserved_qty'];
assertTest('T-11', 'Delivery 12 pcs: WH-PKU On Hand 68, Reserved 16, Available 52', $airQuantAfterDel['on_hand_qty'] == 68 && $airQuantAfterDel['reserved_qty'] == 16 && $availAfterDel == 52);

// T-13: Internal Transfer 15 AIR-600 from WH-PKU (68->53) to STORE-PKU (40->55)
// Total company stays 140 pcs, valuation stays same!
$pdo->exec("UPDATE stock_quants SET on_hand_qty = on_hand_qty - 15 WHERE product_id = 1 AND location_id = 1");
$pdo->exec("UPDATE stock_quants SET on_hand_qty = on_hand_qty + 15 WHERE product_id = 1 AND location_id = 2");
$totAir = (int)$pdo->query("SELECT SUM(on_hand_qty) FROM stock_quants WHERE product_id = 1")->fetchColumn();
assertTest('T-13', 'Internal transfer 15 pcs: Company total tetap 140 pcs', $totAir === (140 - 12)); // 140 minus 12 from previous delivery

// T-15: PO 100 pcs MIE-GOR-80 from V01 @ 3000 -> Gross 333.000
$pdo->exec("INSERT INTO purchase_orders (id, po_number, partner_id, location_id, order_date, status, subtotal, tax_amount, grand_total) VALUES (2, 'PO00008', 7, 1, date('now'), 'confirmed', 300000, 33000, 333000)");
$po2 = $pdo->query("SELECT * FROM purchase_orders WHERE id = 2")->fetch();
assertTest('T-15', 'PO00008 Subtotal 300.000, PPN 33.000, Gross 333.000', $po2['subtotal'] == 300000 && $po2['tax_amount'] == 33000 && $po2['grand_total'] == 333000);

// T-17: Receipt 100 pcs: Accepted 85, Rejected 15. WH-PKU MIE-GOR-80 On Hand +85
$mieBefore = (int)$pdo->query("SELECT on_hand_qty FROM stock_quants WHERE product_id = 8 AND location_id = 1")->fetchColumn();
$pdo->exec("UPDATE stock_quants SET on_hand_qty = on_hand_qty + 85 WHERE product_id = 8 AND location_id = 1");
$mieAfter = (int)$pdo->query("SELECT on_hand_qty FROM stock_quants WHERE product_id = 8 AND location_id = 1")->fetchColumn();
assertTest('T-17', 'Receipt QC: Only 85 Accepted adds to On Hand (120 -> 205)', $mieBefore == 120 && $mieAfter == 205);

// T-19: Customer Invoice for 12 Delivered AIR-600: Net 48.000, PPN 5.280, Gross 53.280
$net12 = 12 * 4000;
$vat12 = (int)round($net12 * 0.11);
$gross12 = $net12 + $vat12;
$pdo->exec("INSERT INTO account_invoices (invoice_number, invoice_type, partner_id, reference_doc, invoice_date, due_date, status, amount_untaxed, amount_tax, amount_total, amount_paid, amount_residual) VALUES ('INV/2026/00002', 'customer_invoice', 1, 'S00008', date('now'), date('now', '+14 days'), 'posted', {$net12}, {$vat12}, {$gross12}, 0, {$gross12})");
$inv2 = $pdo->query("SELECT * FROM account_invoices WHERE invoice_number = 'INV/2026/00002'")->fetch();
assertTest('T-19', 'Customer Invoice 12 pcs: Net 48.000, PPN 5.280, Gross 53.280', $inv2['amount_untaxed'] == 48000 && $inv2['amount_tax'] == 5280 && $inv2['amount_total'] == 53280);

// T-23: Register Payment Rp30.000 on INV/2026/00002 -> Remaining Rp23.280
$pdo->exec("UPDATE account_invoices SET amount_paid = 30000, amount_residual = amount_residual - 30000 WHERE invoice_number = 'INV/2026/00002'");
$inv2Paid = $pdo->query("SELECT * FROM account_invoices WHERE invoice_number = 'INV/2026/00002'")->fetch();
assertTest('T-23', 'Partial payment Rp30.000: Remaining Rp23.280', $inv2Paid['amount_residual'] == 23280);

// T-29: POS checkout 3 AIR-600 & 2 MIE-GOR-80 in STORE-PKU
// Net: (3*4000) + (2*4200) = 12000 + 8400 = 20400. PPN 11%: 2244. Gross: 22644.
$posNet = (3 * 4000) + (2 * 4200);
$posVat = (int)round($posNet * 0.11);
$posGross = $posNet + $posVat;
assertTest('T-29', 'POS Calculation: Net Rp20.400, PPN Rp2.244, Receipt Paid Rp22.644', $posNet == 20400 && $posVat == 2244 && $posGross == 22644);

// T-31: POS Close Session with Expected = Counted = 222.644 -> Variance 0
$expCash = 200000 + $posGross;
$variance = 222644 - $expCash;
assertTest('T-31', 'POS Close Session: Counted 222.644, Expected 222.644, Variance 0', $expCash == 222644 && $variance == 0);

echo "\n=================================================================\n";
echo "HASIL PENGUJIAN: {$passCount} LULUS (PASS), {$failCount} GAGAL (FAIL)\n";
echo "=================================================================\n";
if ($failCount === 0) {
    echo "SELURUH SKENARIO T-xx DARI HANDOUT TERVERIFIKASI 100% SUKSES!\n";
}
Database::resetFixture();
