-- =============================================================================
-- ERP PT RANTAU MART RIAU - OFFICIAL SCHEMA & BASELINE FIXTURE SEED
-- Based on: Student Scenario Handout — PT RantauMart Riau
-- Database Engine: PostgreSQL 14+ / MariaDB / SQLite compatible
-- Currency: IDR (Rupiah) stored as BIGINT (No floating point)
-- PPN: Effective 11% (Tarif formal 12% x DPP 11/12), rounded half-up per line
-- 15 SKUs | 6 Customers | 4 Vendors | 3 Locations (WH-PKU, STORE-PKU, STORE-DUM)
-- =============================================================================

BEGIN;

DROP TABLE IF EXISTS audit_logs CASCADE;
DROP TABLE IF EXISTS pos_order_lines CASCADE;
DROP TABLE IF EXISTS pos_orders CASCADE;
DROP TABLE IF EXISTS pos_sessions CASCADE;
DROP TABLE IF EXISTS account_payments CASCADE;
DROP TABLE IF EXISTS account_journal_items CASCADE;
DROP TABLE IF EXISTS account_journal_entries CASCADE;
DROP TABLE IF EXISTS account_invoice_lines CASCADE;
DROP TABLE IF EXISTS account_invoices CASCADE;
DROP TABLE IF EXISTS chart_of_accounts CASCADE;
DROP TABLE IF EXISTS stock_moves CASCADE;
DROP TABLE IF EXISTS stock_quants CASCADE;
DROP TABLE IF EXISTS delivery_order_lines CASCADE;
DROP TABLE IF EXISTS delivery_orders CASCADE;
DROP TABLE IF EXISTS receipt_lines CASCADE;
DROP TABLE IF EXISTS receipts CASCADE;
DROP TABLE IF EXISTS purchase_order_lines CASCADE;
DROP TABLE IF EXISTS purchase_orders CASCADE;
DROP TABLE IF EXISTS vendor_pricelists CASCADE;
DROP TABLE IF EXISTS sales_order_lines CASCADE;
DROP TABLE IF EXISTS sales_orders CASCADE;
DROP TABLE IF EXISTS crm_leads CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS product_categories CASCADE;
DROP TABLE IF EXISTS partners CASCADE;
DROP TABLE IF EXISTS locations CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS roles CASCADE;

-- 1. Roles & Users
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL
);

INSERT INTO roles (id, code, name) VALUES
(1, 'sales', 'Sales / CRM'),
(2, 'warehouse', 'Warehouse / Inventory'),
(3, 'procurement', 'Procurement / Purchase'),
(4, 'finance', 'Finance & Accounting'),
(5, 'cashier', 'POS Cashier / Supervisor'),
(6, 'owner', 'Owner / Management');

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    role_id INT NOT NULL REFERENCES roles(id),
    username VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE
);

INSERT INTO users (id, role_id, username, full_name, email, password_hash) VALUES
(1, 1, 'sales.putri', 'Putri Aulia (Sales Manager)', 'putri@rantaumart.co.id', 'password123'),
(2, 2, 'wh.deni', 'Deni Pratama (Kepala Gudang)', 'deni@rantaumart.co.id', 'password123'),
(3, 3, 'proc.mira', 'Mira Salsabila (Manajer Procurement)', 'mira@rantaumart.co.id', 'password123'),
(4, 4, 'fin.arif', 'Arif Firmansyah (Kepala Finance)', 'arif@rantaumart.co.id', 'password123'),
(5, 5, 'pos.rani', 'Rani Oktaviani (Supervisor Kasir)', 'rani@rantaumart.co.id', 'password123'),
(6, 6, 'owner.budi', 'Budi Haryanto (Direktur Operasional)', 'budi@rantaumart.co.id', 'password123');

-- 2. Locations
CREATE TABLE locations (
    id SERIAL PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    function TEXT NOT NULL
);

INSERT INTO locations (id, code, name, city, function) VALUES
(1, 'WH-PKU', 'Gudang pusat Pekanbaru', 'Pekanbaru', 'B2B distribution dan vendor receipts'),
(2, 'STORE-PKU', 'RantauMart Pekanbaru', 'Pekanbaru', 'POS sales, opening and closing cash'),
(3, 'STORE-DUM', 'RantauMart Dumai', 'Dumai', 'POS inventory / store stock');

-- 3. Partners (Contacts: 6 Customers, 4 Vendors)
CREATE TABLE partners (
    id SERIAL PRIMARY KEY,
    code VARCHAR(30) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    partner_type VARCHAR(20) NOT NULL, -- 'customer' or 'vendor'
    city VARCHAR(50),
    credit_limit BIGINT NOT NULL DEFAULT 0,
    payment_terms VARCHAR(50) NOT NULL DEFAULT 'NET 30',
    goods VARCHAR(150)
);

INSERT INTO partners (id, code, name, partner_type, city, credit_limit, payment_terms, goods) VALUES
(1, 'C001', 'Warung Kopi Pasar', 'customer', 'Pekanbaru', 500000, 'NET 14', NULL),
(2, 'C002', 'CV Kantin Tunas', 'customer', 'Pekanbaru', 2000000, 'NET 30', NULL),
(3, 'C003', 'TK Harapan Keluarga', 'customer', 'Siak', 1000000, 'NET 14', NULL),
(4, 'C004', 'Grosir Mitra Dumai', 'customer', 'Dumai', 5000000, 'NET 30', NULL),
(5, 'C005', 'Ibu Ratna Puspita', 'customer', 'Pekanbaru', 0, 'COD', NULL),
(6, 'C006', 'PT Sudut Ritel', 'customer', 'Padang', 3000000, 'NET 30', NULL),
(7, 'V01', 'PT Pangan Pilihan', 'vendor', 'Pekanbaru', 0, 'NET 30', 'Makanan kemasan'),
(8, 'V02', 'CV Tirta Susu', 'vendor', 'Medan', 0, 'NET 14', 'Minuman UHT dan air mineral'),
(9, 'V03', 'UD Sembako Bersama', 'vendor', 'Pekanbaru', 0, 'NET 30', 'Beras, gula, minyak'),
(10, 'V04', 'PT Rumah Bersih', 'vendor', 'Batam', 0, 'NET 14', 'Produk rumah tangga');

-- 4. Products (15 Realistis SKUs)
CREATE TABLE product_categories (
    id SERIAL PRIMARY KEY,
    code VARCHAR(30) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL
);

INSERT INTO product_categories (id, code, name) VALUES
(1, 'BEV', 'Minuman & Air'),
(2, 'MILK', 'Susu UHT'),
(3, 'SNK', 'Biskuit & Makanan Ringan'),
(4, 'NOODLE', 'Mi Instan'),
(5, 'COF', 'Kopi'),
(6, 'STAPLE', 'Sembako (Beras, Gula, Minyak)'),
(7, 'HPC', 'Kebutuhan Rumah Tangga & Pembersih');

CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    sku VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    size VARCHAR(50) NOT NULL,
    flavor VARCHAR(50) NOT NULL,
    unit VARCHAR(20) NOT NULL DEFAULT 'pcs',
    sales_price BIGINT NOT NULL,
    cost_price BIGINT NOT NULL,
    default_vendor_id INT REFERENCES partners(id),
    category_id INT REFERENCES product_categories(id),
    barcode VARCHAR(50) UNIQUE
);

INSERT INTO products (id, sku, name, size, flavor, unit, sales_price, cost_price, default_vendor_id, category_id, barcode) VALUES
(1, 'AIR-600', 'Air mineral Nusa', '600 ml', 'Original', 'pcs', 4000, 2500, 8, 1, '8991001006001'),
(2, 'AIR-1500', 'Air mineral Nusa', '1,5 L', 'Original', 'pcs', 7500, 5000, 8, 1, '8991001015002'),
(3, 'MIL-COK-200', 'Susu UHT Segar', '200 ml', 'Cokelat', 'pcs', 7000, 4200, 8, 2, '8991002002001'),
(4, 'MIL-STR-200', 'Susu UHT Segar', '200 ml', 'Stroberi', 'pcs', 7000, 4200, 8, 2, '8991002002002'),
(5, 'MIL-PLN-1L', 'Susu UHT Segar', '1 L', 'Plain', 'pcs', 25000, 17500, 8, 2, '8991002010003'),
(6, 'BIS-COK-120', 'Biskuit Crispy', '120 g', 'Cokelat', 'pcs', 9500, 6000, 7, 3, '8991003001201'),
(7, 'BIS-VAN-120', 'Biskuit Crispy', '120 g', 'Vanila', 'pcs', 9500, 6000, 7, 3, '8991003001202'),
(8, 'MIE-GOR-80', 'Mi instan Hemat', '80 g', 'Goreng', 'pcs', 4200, 2800, 7, 4, '8991004000801'),
(9, 'MIE-KUA-80', 'Mi instan Hemat', '80 g', 'Kuah', 'pcs', 4200, 2800, 7, 4, '8991004000802'),
(10, 'KOP-SCH-20', 'Kopi instan sachet', '20 g', 'Original', 'pcs', 3500, 1900, 7, 5, '8991005000201'),
(11, 'GUL-1KG', 'Gula kemasan', '1 kg', 'Putih', 'pcs', 18000, 13000, 9, 6, '8991006001001'),
(12, 'BER-5KG', 'Beras premium', '5 kg', 'Premium', 'pcs', 76000, 60000, 9, 6, '8991006005002'),
(13, 'MIN-1L', 'Minyak goreng', '1 L', 'Botol', 'pcs', 20000, 14500, 9, 6, '8991006001003'),
(14, 'SAB-450', 'Sabun cair', '450 ml', 'Antibakteri', 'pcs', 22000, 14000, 10, 7, '8991007004501'),
(15, 'DET-800', 'Detergen bubuk', '800 g', 'Lemon', 'pcs', 24000, 15500, 10, 7, '8991007008002');

-- 5. Vendor Pricelist
CREATE TABLE vendor_pricelists (
    id SERIAL PRIMARY KEY,
    vendor_id INT NOT NULL REFERENCES partners(id),
    product_id INT NOT NULL REFERENCES products(id),
    unit_price BIGINT NOT NULL,
    min_qty INT NOT NULL DEFAULT 1,
    currency VARCHAR(10) NOT NULL DEFAULT 'IDR'
);

INSERT INTO vendor_pricelists (vendor_id, product_id, unit_price, min_qty, currency) VALUES
(7, 8, 3000, 1, 'IDR'),   -- V01 MIE-GOR-80 Rp3.000
(7, 6, 6200, 1, 'IDR'),   -- V01 BIS-COK-120 Rp6.200
(8, 1, 2500, 1, 'IDR'),   -- V02 AIR-600 Rp2.500
(8, 3, 4300, 1, 'IDR'),   -- V02 MIL-COK-200 Rp4.300
(9, 12, 60500, 1, 'IDR'), -- V03 BER-5KG Rp60.500
(9, 11, 13200, 1, 'IDR'), -- V03 GUL-1KG Rp13.200
(10, 14, 14400, 1, 'IDR');-- V04 SAB-450 Rp14.400

-- 6. Stock Quants (Opening Stock Baseline - Total 1.500 pcs, Total Rp9.628.900)
-- WH-PKU AIR-600: On Hand 80, Reserved 8, Available 72!
CREATE TABLE stock_quants (
    id SERIAL PRIMARY KEY,
    product_id INT NOT NULL REFERENCES products(id),
    location_id INT NOT NULL REFERENCES locations(id),
    on_hand_qty INT NOT NULL DEFAULT 0 CHECK (on_hand_qty >= 0),
    reserved_qty INT NOT NULL DEFAULT 0 CHECK (reserved_qty >= 0),
    CONSTRAINT chk_reserved_lte_onhand CHECK (reserved_qty <= on_hand_qty),
    CONSTRAINT uq_product_loc UNIQUE (product_id, location_id)
);

INSERT INTO stock_quants (product_id, location_id, on_hand_qty, reserved_qty) VALUES
(1, 1, 80, 8),   -- AIR-600 WH-PKU (On Hand 80, Reserved 8 -> Available 72!)
(1, 2, 40, 0),   -- AIR-600 STORE-PKU
(1, 3, 20, 0),   -- AIR-600 STORE-DUM
(2, 1, 50, 0),   -- AIR-1500 WH-PKU
(2, 2, 30, 0),   -- AIR-1500 STORE-PKU
(2, 3, 20, 0),   -- AIR-1500 STORE-DUM
(3, 1, 50, 0),   -- MIL-COK-200 WH-PKU
(3, 2, 24, 0),   -- MIL-COK-200 STORE-PKU
(3, 3, 12, 0),   -- MIL-COK-200 STORE-DUM
(4, 1, 50, 0),   -- MIL-STR-200 WH-PKU
(4, 2, 24, 0),   -- MIL-STR-200 STORE-PKU
(4, 3, 12, 0),   -- MIL-STR-200 STORE-DUM
(5, 1, 20, 0),   -- MIL-PLN-1L WH-PKU
(5, 2, 10, 0),   -- MIL-PLN-1L STORE-PKU
(5, 3, 8, 0),    -- MIL-PLN-1L STORE-DUM
(6, 1, 50, 0),   -- BIS-COK-120 WH-PKU
(6, 2, 25, 0),   -- BIS-COK-120 STORE-PKU
(6, 3, 10, 0),   -- BIS-COK-120 STORE-DUM
(7, 1, 50, 0),   -- BIS-VAN-120 WH-PKU
(7, 2, 25, 0),   -- BIS-VAN-120 STORE-PKU
(7, 3, 10, 0),   -- BIS-VAN-120 STORE-DUM
(8, 1, 120, 0),  -- MIE-GOR-80 WH-PKU
(8, 2, 60, 0),   -- MIE-GOR-80 STORE-PKU
(8, 3, 40, 0),   -- MIE-GOR-80 STORE-DUM
(9, 1, 120, 0),  -- MIE-KUA-80 WH-PKU
(9, 2, 50, 0),   -- MIE-KUA-80 STORE-PKU
(9, 3, 30, 0),   -- MIE-KUA-80 STORE-DUM
(10, 1, 100, 0), -- KOP-SCH-20 WH-PKU
(10, 2, 80, 0),  -- KOP-SCH-20 STORE-PKU
(10, 3, 40, 0),  -- KOP-SCH-20 STORE-DUM
(11, 1, 30, 0),  -- GUL-1KG WH-PKU
(11, 2, 20, 0),  -- GUL-1KG STORE-PKU
(11, 3, 10, 0),  -- GUL-1KG STORE-DUM
(12, 1, 15, 0),  -- BER-5KG WH-PKU
(12, 2, 10, 0),  -- BER-5KG STORE-PKU
(12, 3, 5, 0),   -- BER-5KG STORE-DUM
(13, 1, 30, 0),  -- MIN-1L WH-PKU
(13, 2, 20, 0),  -- MIN-1L STORE-PKU
(13, 3, 10, 0),  -- MIN-1L STORE-DUM
(14, 1, 20, 0),  -- SAB-450 WH-PKU
(14, 2, 15, 0),  -- SAB-450 STORE-PKU
(14, 3, 10, 0),  -- SAB-450 STORE-DUM
(15, 1, 20, 0),  -- DET-800 WH-PKU
(15, 2, 15, 0),  -- DET-800 STORE-PKU
(15, 3, 10, 0);  -- DET-800 STORE-DUM

-- 7. Chart of Accounts (COA: 1010 to 5140)
CREATE TABLE chart_of_accounts (
    id SERIAL PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(30) NOT NULL
);

INSERT INTO chart_of_accounts (id, code, name, type) VALUES
(1, '1010', 'Bank', 'Asset'),
(2, '1020', 'POS Cash Drawer', 'Asset'),
(3, '1100', 'Trade Receivables', 'Asset'),
(4, '1130', 'Stock Valuation', 'Asset'),
(5, '1140', 'Input VAT', 'Asset'),
(6, '1150', 'Stock Interim Received', 'Liability / clearing'),
(7, '1190', 'POS Settlement Clearing', 'Asset / clearing'),
(8, '2010', 'Accounts Payable', 'Liability'),
(9, '2030', 'Output VAT', 'Liability'),
(10, '3010', 'Opening Equity', 'Equity'),
(11, '4010', 'B2B Sales Revenue', 'Revenue'),
(12, '4020', 'POS Sales Revenue', 'Revenue'),
(13, '4030', 'Sales Returns', 'Contra Revenue'),
(14, '5010', 'COGS', 'Expense'),
(15, '5110', 'Purchase Price Variance', 'Expense'),
(16, '5130', 'Cash Over / Short', 'Expense or income'),
(17, '5140', 'Stock Write-off', 'Expense');

-- 8. General Ledger Entries (Opening Balance Journal)
CREATE TABLE account_journal_entries (
    id SERIAL PRIMARY KEY,
    entry_number VARCHAR(50) UNIQUE NOT NULL,
    entry_date DATE NOT NULL DEFAULT CURRENT_DATE,
    ref_doc VARCHAR(100),
    memo TEXT,
    is_posted BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE account_journal_items (
    id SERIAL PRIMARY KEY,
    entry_id INT NOT NULL REFERENCES account_journal_entries(id) ON DELETE CASCADE,
    account_id INT NOT NULL REFERENCES chart_of_accounts(id),
    partner_id INT REFERENCES partners(id),
    debit BIGINT NOT NULL DEFAULT 0,
    credit BIGINT NOT NULL DEFAULT 0
);

-- Jurnal Saldo Awal Pembukaan:
-- Debit: Inventory 1130 = Rp9.628.900, AR 1100 = Rp888.000, Bank 1010 = Rp15.000.000, POS Cash 1020 = Rp200.000
-- Credit: AP 2010 = Rp555.000, Equity 3010 = Rp25.161.900
-- Total Debit = Credit = Rp25.716.900!
INSERT INTO account_journal_entries (id, entry_number, entry_date, ref_doc, memo, is_posted)
VALUES (1, 'JRNL/2026/00001', CURRENT_DATE, 'OPENING_BALANCE', 'Jurnal Saldo Awal PT RantauMart Riau', TRUE);

INSERT INTO account_journal_items (entry_id, account_id, partner_id, debit, credit) VALUES
(1, 4, NULL, 9628900, 0),       -- 1130 Stock Valuation: Debit Rp9.628.900
(1, 3, 2, 888000, 0),          -- 1100 Trade Receivables (C002): Debit Rp888.000
(1, 1, NULL, 15000000, 0),     -- 1010 Bank: Debit Rp15.000.000
(1, 2, NULL, 200000, 0),       -- 1020 POS Cash Drawer: Debit Rp200.000
(1, 8, 7, 0, 555000),          -- 2010 Accounts Payable (V01): Credit Rp555.000
(1, 10, NULL, 0, 25161900);    -- 3010 Opening Equity: Credit Rp25.161.900

-- 9. Existing Documents: Invoices & Bills
CREATE TABLE account_invoices (
    id SERIAL PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    invoice_type VARCHAR(30) NOT NULL, -- 'customer_invoice', 'vendor_bill', 'customer_refund', 'vendor_refund'
    partner_id INT NOT NULL REFERENCES partners(id),
    reference_doc VARCHAR(50),
    invoice_date DATE NOT NULL DEFAULT CURRENT_DATE,
    due_date DATE NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'posted',
    amount_untaxed BIGINT NOT NULL DEFAULT 0,
    amount_tax BIGINT NOT NULL DEFAULT 0,
    amount_total BIGINT NOT NULL DEFAULT 0,
    amount_paid BIGINT NOT NULL DEFAULT 0,
    amount_residual BIGINT NOT NULL DEFAULT 0
);

-- AR C002 CV Kantin Tunas Rp888.000
INSERT INTO account_invoices (id, invoice_number, invoice_type, partner_id, reference_doc, invoice_date, due_date, status, amount_untaxed, amount_tax, amount_total, amount_paid, amount_residual)
VALUES (1, 'INV/2026/00001', 'customer_invoice', 2, 'SALDO-AWAL-AR', CURRENT_DATE - INTERVAL '10 days', CURRENT_DATE + INTERVAL '20 days', 'posted', 800000, 88000, 888000, 0, 888000);

-- AP V01 PT Pangan Pilihan Rp555.000
INSERT INTO account_invoices (id, invoice_number, invoice_type, partner_id, reference_doc, invoice_date, due_date, status, amount_untaxed, amount_tax, amount_total, amount_paid, amount_residual)
VALUES (2, 'BILL/2026/00001', 'vendor_bill', 7, 'SALDO-AWAL-AP', CURRENT_DATE - INTERVAL '5 days', CURRENT_DATE + INTERVAL '25 days', 'posted', 500000, 55000, 555000, 0, 555000);

-- 10. Existing Documents: CRM Lead LEAD-0001
CREATE TABLE crm_leads (
    id SERIAL PRIMARY KEY,
    lead_number VARCHAR(50) UNIQUE NOT NULL,
    partner_id INT REFERENCES partners(id),
    name VARCHAR(150) NOT NULL,
    expected_revenue BIGINT DEFAULT 0,
    stage VARCHAR(30) NOT NULL DEFAULT 'lead' -- 'lead', 'opportunity', 'won', 'lost'
);

INSERT INTO crm_leads (id, lead_number, partner_id, name, expected_revenue, stage)
VALUES (1, 'LEAD-0001', 1, 'Pesanan Rutin Warung Kopi Pasar', 88800, 'lead');

-- 11. Existing Documents: Sales Order S00007 (8 AIR-600 @ 4.000)
CREATE TABLE sales_orders (
    id SERIAL PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    partner_id INT NOT NULL REFERENCES partners(id),
    location_id INT NOT NULL REFERENCES locations(id),
    order_date DATE NOT NULL DEFAULT CURRENT_DATE,
    status VARCHAR(30) NOT NULL DEFAULT 'confirmed', -- 'draft', 'confirmed', 'done', 'cancelled'
    subtotal BIGINT NOT NULL DEFAULT 32000,
    discount_amount BIGINT NOT NULL DEFAULT 0,
    tax_amount BIGINT NOT NULL DEFAULT 3520,
    grand_total BIGINT NOT NULL DEFAULT 35520
);

CREATE TABLE sales_order_lines (
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL REFERENCES sales_orders(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES products(id),
    ordered_qty INT NOT NULL,
    delivered_qty INT NOT NULL DEFAULT 0,
    invoiced_qty INT NOT NULL DEFAULT 0,
    unit_price BIGINT NOT NULL,
    discount_percent NUMERIC(5,2) NOT NULL DEFAULT 0,
    subtotal BIGINT NOT NULL,
    tax_amount BIGINT NOT NULL,
    total BIGINT NOT NULL
);

INSERT INTO sales_orders (id, order_number, partner_id, location_id, order_date, status, subtotal, discount_amount, tax_amount, grand_total)
VALUES (1, 'S00007', 1, 1, CURRENT_DATE, 'confirmed', 32000, 0, 3520, 35520);

INSERT INTO sales_order_lines (id, order_id, product_id, ordered_qty, delivered_qty, invoiced_qty, unit_price, discount_percent, subtotal, tax_amount, total)
VALUES (1, 1, 1, 8, 0, 0, 4000, 0, 32000, 3520, 35520);

-- 12. Existing Documents: Purchase Order PO00007 (30 MIE-GOR-80 @ 3.000)
CREATE TABLE purchase_orders (
    id SERIAL PRIMARY KEY,
    po_number VARCHAR(50) UNIQUE NOT NULL,
    partner_id INT NOT NULL REFERENCES partners(id),
    location_id INT NOT NULL REFERENCES locations(id),
    order_date DATE NOT NULL DEFAULT CURRENT_DATE,
    status VARCHAR(30) NOT NULL DEFAULT 'confirmed', -- 'rfq', 'confirmed', 'done', 'cancelled'
    subtotal BIGINT NOT NULL DEFAULT 90000,
    tax_amount BIGINT NOT NULL DEFAULT 9900,
    grand_total BIGINT NOT NULL DEFAULT 99900
);

CREATE TABLE purchase_order_lines (
    id SERIAL PRIMARY KEY,
    po_id INT NOT NULL REFERENCES purchase_orders(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES products(id),
    ordered_qty INT NOT NULL,
    received_qty INT NOT NULL DEFAULT 0,
    accepted_qty INT NOT NULL DEFAULT 0,
    rejected_qty INT NOT NULL DEFAULT 0,
    billed_qty INT NOT NULL DEFAULT 0,
    unit_price BIGINT NOT NULL,
    subtotal BIGINT NOT NULL,
    tax_amount BIGINT NOT NULL,
    total BIGINT NOT NULL
);

INSERT INTO purchase_orders (id, po_number, partner_id, location_id, order_date, status, subtotal, tax_amount, grand_total)
VALUES (1, 'PO00007', 7, 1, CURRENT_DATE, 'confirmed', 90000, 9900, 99900);

INSERT INTO purchase_order_lines (id, po_id, product_id, ordered_qty, received_qty, accepted_qty, rejected_qty, billed_qty, unit_price, subtotal, tax_amount, total)
VALUES (1, 1, 8, 30, 0, 0, 0, 0, 3000, 90000, 9900, 99900);

-- 13. POS Session (Active Open Session for STORE-PKU with Opening Cash Rp200.000)
CREATE TABLE pos_sessions (
    id SERIAL PRIMARY KEY,
    session_number VARCHAR(50) UNIQUE NOT NULL,
    location_id INT NOT NULL REFERENCES locations(id),
    user_id INT NOT NULL REFERENCES users(id),
    opening_cash BIGINT NOT NULL DEFAULT 200000,
    closing_cash BIGINT,
    expected_cash BIGINT DEFAULT 200000,
    cash_variance BIGINT DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'opened'
);

INSERT INTO pos_sessions (id, session_number, location_id, user_id, opening_cash, expected_cash, status)
VALUES (1, 'POS-SESSION-0001', 2, 5, 200000, 200000, 'opened');

CREATE TABLE pos_orders (
    id SERIAL PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    session_id INT NOT NULL REFERENCES pos_sessions(id),
    location_id INT NOT NULL REFERENCES locations(id),
    total_amount BIGINT NOT NULL,
    tax_amount BIGINT NOT NULL,
    grand_total BIGINT NOT NULL,
    cash_tendered BIGINT NOT NULL,
    change_amount BIGINT NOT NULL,
    payment_method VARCHAR(20) NOT NULL DEFAULT 'cash',
    status VARCHAR(20) NOT NULL DEFAULT 'paid',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pos_order_lines (
    id SERIAL PRIMARY KEY,
    order_id INT NOT NULL REFERENCES pos_orders(id) ON DELETE CASCADE,
    product_id INT NOT NULL REFERENCES products(id),
    qty INT NOT NULL,
    unit_price BIGINT NOT NULL,
    tax_amount BIGINT NOT NULL,
    subtotal BIGINT NOT NULL
);

-- 14. Audit Log
CREATE TABLE audit_logs (
    id SERIAL PRIMARY KEY,
    actor_username VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    entity_name VARCHAR(50) NOT NULL,
    entity_id VARCHAR(50) NOT NULL,
    old_values TEXT,
    new_values TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO audit_logs (actor_username, action, entity_name, entity_id, new_values)
VALUES ('system', 'SEED_INIT', 'SYSTEM', 'BASELINE', 'Baseline 15 SKU, On Hand 1500, Valuation 9628900 initialized.');

COMMIT;
