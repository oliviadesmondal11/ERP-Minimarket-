-- =============================================================================
-- ERP PT RANTAU MART RIAU - BASELINE CONTROL VERIFICATION QUERY
-- Skenario Pengujian Baseline:
-- 1. Total On Hand = 1.500 pcs
-- 2. Nilai Stok = Rp9.628.900
-- 3. AR (Piutang Usaha) = Rp888.000
-- 4. AP (Hutang Usaha) = Rp555.000
-- 5. Kas Bank = Rp15.000.000
-- 6. POS Cash = Rp200.000
-- 7. Available AIR-600 di WH-PKU = 72 pcs
-- =============================================================================

WITH baseline_metrics AS (
    SELECT 
        (SELECT COALESCE(SUM(on_hand_qty), 0) FROM stock_quants) AS actual_on_hand,
        1500 AS target_on_hand,

        (SELECT COALESCE(SUM(sq.on_hand_qty * p.cost_price), 0) 
         FROM stock_quants sq 
         JOIN products p ON sq.product_id = p.id) AS actual_stock_value,
        9628900::BIGINT AS target_stock_value,

        (SELECT COALESCE(SUM(amount_residual), 0) 
         FROM account_invoices 
         WHERE invoice_type = 'customer_invoice' AND status = 'posted') AS actual_ar,
        888000::BIGINT AS target_ar,

        (SELECT COALESCE(SUM(amount_residual), 0) 
         FROM account_invoices 
         WHERE invoice_type = 'vendor_bill' AND status = 'posted') AS actual_ap,
        555000::BIGINT AS target_ap,

        (SELECT COALESCE(SUM(aji.debit - aji.credit), 0) 
         FROM account_journal_items aji 
         JOIN chart_of_accounts coa ON aji.account_id = coa.id 
         WHERE coa.code = '111200') AS actual_bank,
        15000000::BIGINT AS target_bank,

        (SELECT COALESCE(opening_cash, 0) 
         FROM pos_sessions 
         WHERE status = 'opened' 
         ORDER BY id LIMIT 1) AS actual_pos_cash,
        200000::BIGINT AS target_pos_cash,

        (SELECT (sq.on_hand_qty - sq.reserved_qty) 
         FROM stock_quants sq 
         JOIN products p ON sq.product_id = p.id 
         JOIN locations l ON sq.location_id = l.id 
         WHERE p.sku = 'AIR-600' AND l.code = 'WH-PKU') AS actual_avail_air600_wh,
        72 AS target_avail_air600_wh
)
SELECT 
    '1. Total On Hand Stock' AS parameter,
    actual_on_hand AS actual,
    target_on_hand AS expected,
    CASE WHEN actual_on_hand = target_on_hand THEN 'PASS' ELSE 'FAIL' END AS status
FROM baseline_metrics
UNION ALL
SELECT 
    '2. Total Stock Valuation (IDR)',
    actual_stock_value,
    target_stock_value,
    CASE WHEN actual_stock_value = target_stock_value THEN 'PASS' ELSE 'FAIL' END
FROM baseline_metrics
UNION ALL
SELECT 
    '3. Accounts Receivable (AR) (IDR)',
    actual_ar,
    target_ar,
    CASE WHEN actual_ar = target_ar THEN 'PASS' ELSE 'FAIL' END
FROM baseline_metrics
UNION ALL
SELECT 
    '4. Accounts Payable (AP) (IDR)',
    actual_ap,
    target_ap,
    CASE WHEN actual_ap = target_ap THEN 'PASS' ELSE 'FAIL' END
FROM baseline_metrics
UNION ALL
SELECT 
    '5. Bank Balance (IDR)',
    actual_bank,
    target_bank,
    CASE WHEN actual_bank = target_bank THEN 'PASS' ELSE 'FAIL' END
FROM baseline_metrics
UNION ALL
SELECT 
    '6. POS Opening Cash (IDR)',
    actual_pos_cash,
    target_pos_cash,
    CASE WHEN actual_pos_cash = target_pos_cash THEN 'PASS' ELSE 'FAIL' END
FROM baseline_metrics
UNION ALL
SELECT 
    '7. Available AIR-600 @ WH-PKU',
    actual_avail_air600_wh,
    target_avail_air600_wh,
    CASE WHEN actual_avail_air600_wh = target_avail_air600_wh THEN 'PASS' ELSE 'FAIL' END
FROM baseline_metrics;
