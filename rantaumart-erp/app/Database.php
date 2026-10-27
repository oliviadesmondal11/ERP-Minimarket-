<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;
    private static string $activeDriver = 'sqlite';

    public static function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    public static function getConnection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $dbFile = dirname(__DIR__) . '/database/rantaumart.db';

        // Check if user specifically requested PostgreSQL and service is running
        $driver = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? '');
        if ($driver === 'pgsql' && extension_loaded('pdo_pgsql')) {
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '5432';
            $name = getenv('DB_NAME') ?: 'rantaumart_erp';
            $user = getenv('DB_USER') ?: 'postgres';
            $pass = getenv('DB_PASSWORD') ?: '';
            try {
                $dsn = "pgsql:host={$host};port={$port};dbname={$name};";
                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 2
                ]);
                self::$activeDriver = 'pgsql';
                return self::$pdo;
            } catch (\Throwable $e) {
                // Fall back to sqlite seamlessly so the app NEVER crashes
            }
        }

        // Embedded Persistent SQLite Database (Default, robust, zero-configuration)
        if (!file_exists($dbFile)) {
            self::initDatabase($dbFile);
        }

        self::$pdo = new PDO("sqlite:{$dbFile}", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        self::$activeDriver = 'sqlite';
        return self::$pdo;
    }

    public static function getActiveDriver(): string
    {
        return self::$activeDriver;
    }

    public static function resetFixture(): void
    {
        self::$pdo = null;
        $dbFile = dirname(__DIR__) . '/database/rantaumart.db';
        self::initDatabase($dbFile);
    }

    private static function initDatabase(string $dbFile): void
    {
        $pdo = new PDO("sqlite:{$dbFile}", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $sql = "
        DROP TABLE IF EXISTS audit_logs;
        DROP TABLE IF EXISTS pos_order_lines;
        DROP TABLE IF EXISTS pos_orders;
        DROP TABLE IF EXISTS pos_sessions;
        DROP TABLE IF EXISTS account_payments;
        DROP TABLE IF EXISTS account_journal_items;
        DROP TABLE IF EXISTS account_journal_entries;
        DROP TABLE IF EXISTS account_invoices;
        DROP TABLE IF EXISTS chart_of_accounts;
        DROP TABLE IF EXISTS stock_quants;
        DROP TABLE IF EXISTS purchase_order_lines;
        DROP TABLE IF EXISTS purchase_orders;
        DROP TABLE IF EXISTS sales_order_lines;
        DROP TABLE IF EXISTS sales_orders;
        DROP TABLE IF EXISTS crm_leads;
        DROP TABLE IF EXISTS products;
        DROP TABLE IF EXISTS product_categories;
        DROP TABLE IF EXISTS partners;
        DROP TABLE IF EXISTS locations;
        DROP TABLE IF EXISTS users;
        DROP TABLE IF EXISTS roles;

        CREATE TABLE roles (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT UNIQUE, name TEXT);
        INSERT INTO roles VALUES (1, 'sales', 'Sales / CRM'), (2, 'warehouse', 'Warehouse / Inventory'), (3, 'procurement', 'Procurement / Purchase'), (4, 'finance', 'Finance & Accounting'), (5, 'cashier', 'POS Cashier / Supervisor'), (6, 'owner', 'Owner / Management');

        CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, role_id INTEGER REFERENCES roles(id), username TEXT UNIQUE, full_name TEXT, email TEXT UNIQUE, password_hash TEXT, is_active INTEGER DEFAULT 1);
        INSERT INTO users VALUES (1, 1, 'sales.putri', 'Putri Aulia (Sales Manager)', 'putri@rantaumart.co.id', 'password123', 1), (2, 2, 'wh.deni', 'Deni Pratama (Kepala Gudang)', 'deni@rantaumart.co.id', 'password123', 1), (3, 3, 'proc.mira', 'Mira Salsabila (Manajer Procurement)', 'mira@rantaumart.co.id', 'password123', 1), (4, 4, 'fin.arif', 'Arif Firmansyah (Kepala Finance)', 'arif@rantaumart.co.id', 'password123', 1), (5, 5, 'pos.rani', 'Rani Oktaviani (Supervisor Kasir)', 'rani@rantaumart.co.id', 'password123', 1), (6, 6, 'owner.budi', 'Budi Haryanto (Direktur Operasional)', 'budi@rantaumart.co.id', 'password123', 1);

        CREATE TABLE locations (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT UNIQUE, name TEXT, city TEXT, function TEXT);
        INSERT INTO locations VALUES (1, 'WH-PKU', 'Gudang pusat Pekanbaru', 'Pekanbaru', 'B2B distribution dan vendor receipts'), (2, 'STORE-PKU', 'RantauMart Pekanbaru', 'Pekanbaru', 'POS sales, opening and closing cash'), (3, 'STORE-DUM', 'RantauMart Dumai', 'Dumai', 'POS inventory / store stock');

        CREATE TABLE partners (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT UNIQUE, name TEXT, partner_type TEXT, city TEXT, credit_limit INTEGER DEFAULT 0, payment_terms TEXT DEFAULT 'NET 30', goods TEXT);
        INSERT INTO partners VALUES (1, 'C001', 'Warung Kopi Pasar', 'customer', 'Pekanbaru', 500000, 'NET 14', NULL), (2, 'C002', 'CV Kantin Tunas', 'customer', 'Pekanbaru', 2000000, 'NET 30', NULL), (3, 'C003', 'TK Harapan Keluarga', 'customer', 'Siak', 1000000, 'NET 14', NULL), (4, 'C004', 'Grosir Mitra Dumai', 'customer', 'Dumai', 5000000, 'NET 30', NULL), (5, 'C005', 'Ibu Ratna Puspita', 'customer', 'Pekanbaru', 0, 'COD', NULL), (6, 'C006', 'PT Sudut Ritel', 'customer', 'Padang', 3000000, 'NET 30', NULL), (7, 'V01', 'PT Pangan Pilihan', 'vendor', 'Pekanbaru', 0, 'NET 30', 'Makanan kemasan'), (8, 'V02', 'CV Tirta Susu', 'vendor', 'Medan', 0, 'NET 14', 'Minuman UHT dan air mineral'), (9, 'V03', 'UD Sembako Bersama', 'vendor', 'Pekanbaru', 0, 'NET 30', 'Beras, gula, minyak'), (10, 'V04', 'PT Rumah Bersih', 'vendor', 'Batam', 0, 'NET 14', 'Produk rumah tangga');

        CREATE TABLE product_categories (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT UNIQUE, name TEXT);
        INSERT INTO product_categories VALUES (1, 'BEV', 'Minuman & Air'), (2, 'MILK', 'Susu UHT'), (3, 'SNK', 'Biskuit & Makanan Ringan'), (4, 'NOODLE', 'Mi Instan'), (5, 'COF', 'Kopi'), (6, 'STAPLE', 'Sembako (Beras, Gula, Minyak)'), (7, 'HPC', 'Kebutuhan Rumah Tangga & Pembersih');

        CREATE TABLE products (id INTEGER PRIMARY KEY AUTOINCREMENT, sku TEXT UNIQUE, name TEXT, size TEXT, flavor TEXT, unit TEXT DEFAULT 'pcs', sales_price INTEGER, cost_price INTEGER, default_vendor_id INTEGER REFERENCES partners(id), category_id INTEGER REFERENCES product_categories(id), barcode TEXT UNIQUE);
        INSERT INTO products VALUES (1, 'AIR-600', 'Air mineral Nusa', '600 ml', 'Original', 'pcs', 4000, 2500, 8, 1, '8991001006001'), (2, 'AIR-1500', 'Air mineral Nusa', '1,5 L', 'Original', 'pcs', 7500, 5000, 8, 1, '8991001015002'), (3, 'MIL-COK-200', 'Susu UHT Segar', '200 ml', 'Cokelat', 'pcs', 7000, 4200, 8, 2, '8991002002001'), (4, 'MIL-STR-200', 'Susu UHT Segar', '200 ml', 'Stroberi', 'pcs', 7000, 4200, 8, 2, '8991002002002'), (5, 'MIL-PLN-1L', 'Susu UHT Segar', '1 L', 'Plain', 'pcs', 25000, 17500, 8, 2, '8991002010003'), (6, 'BIS-COK-120', 'Biskuit Crispy', '120 g', 'Cokelat', 'pcs', 9500, 6000, 7, 3, '8991003001201'), (7, 'BIS-VAN-120', 'Biskuit Crispy', '120 g', 'Vanila', 'pcs', 9500, 6000, 7, 3, '8991003001202'), (8, 'MIE-GOR-80', 'Mi instan Hemat', '80 g', 'Goreng', 'pcs', 4200, 2800, 7, 4, '8991004000801'), (9, 'MIE-KUA-80', 'Mi instan Hemat', '80 g', 'Kuah', 'pcs', 4200, 2800, 7, 4, '8991004000802'), (10, 'KOP-SCH-20', 'Kopi instan sachet', '20 g', 'Original', 'pcs', 3500, 1900, 7, 5, '8991005000201'), (11, 'GUL-1KG', 'Gula kemasan', '1 kg', 'Putih', 'pcs', 18000, 13000, 9, 6, '8991006001001'), (12, 'BER-5KG', 'Beras premium', '5 kg', 'Premium', 'pcs', 76000, 60000, 9, 6, '8991006005002'), (13, 'MIN-1L', 'Minyak goreng', '1 L', 'Botol', 'pcs', 20000, 14500, 9, 6, '8991006001003'), (14, 'SAB-450', 'Sabun cair', '450 ml', 'Antibakteri', 'pcs', 22000, 14000, 10, 7, '8991007004501'), (15, 'DET-800', 'Detergen bubuk', '800 g', 'Lemon', 'pcs', 24000, 15500, 10, 7, '8991007008002');

        CREATE TABLE stock_quants (id INTEGER PRIMARY KEY AUTOINCREMENT, product_id INTEGER REFERENCES products(id), location_id INTEGER REFERENCES locations(id), on_hand_qty INTEGER DEFAULT 0, reserved_qty INTEGER DEFAULT 0);
        INSERT INTO stock_quants (id, product_id, location_id, on_hand_qty, reserved_qty) VALUES (1, 1, 1, 80, 8), (2, 1, 2, 40, 0), (3, 1, 3, 20, 0), (4, 2, 1, 50, 0), (5, 2, 2, 30, 0), (6, 2, 3, 20, 0), (7, 3, 1, 50, 0), (8, 3, 2, 24, 0), (9, 3, 3, 12, 0), (10, 4, 1, 50, 0), (11, 4, 2, 24, 0), (12, 4, 3, 12, 0), (13, 5, 1, 20, 0), (14, 5, 2, 10, 0), (15, 5, 3, 8, 0), (16, 6, 1, 50, 0), (17, 6, 2, 25, 0), (18, 6, 3, 10, 0), (19, 7, 1, 50, 0), (20, 7, 2, 25, 0), (21, 7, 3, 10, 0), (22, 8, 1, 120, 0), (23, 8, 2, 60, 0), (24, 8, 3, 40, 0), (25, 9, 1, 120, 0), (26, 9, 2, 50, 0), (27, 9, 3, 30, 0), (28, 10, 1, 100, 0), (29, 10, 2, 80, 0), (30, 10, 3, 40, 0), (31, 11, 1, 30, 0), (32, 11, 2, 20, 0), (33, 11, 3, 10, 0), (34, 12, 1, 15, 0), (35, 12, 2, 10, 0), (36, 12, 3, 5, 0), (37, 13, 1, 30, 0), (38, 13, 2, 20, 0), (39, 13, 3, 10, 0), (40, 14, 1, 20, 0), (41, 14, 2, 15, 0), (42, 14, 3, 10, 0), (43, 15, 1, 20, 0), (44, 15, 2, 15, 0), (45, 15, 3, 10, 0);

        CREATE TABLE chart_of_accounts (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT UNIQUE, name TEXT, type TEXT);
        INSERT INTO chart_of_accounts VALUES (1, '1010', 'Bank', 'Asset'), (2, '1020', 'POS Cash Drawer', 'Asset'), (3, '1100', 'Trade Receivables', 'Asset'), (4, '1130', 'Stock Valuation', 'Asset'), (5, '1140', 'Input VAT', 'Asset'), (6, '1150', 'Stock Interim Received', 'Liability / clearing'), (7, '1190', 'POS Settlement Clearing', 'Asset / clearing'), (8, '2010', 'Accounts Payable', 'Liability'), (9, '2030', 'Output VAT', 'Liability'), (10, '3010', 'Opening Equity', 'Equity'), (11, '4010', 'B2B Sales Revenue', 'Revenue'), (12, '4020', 'POS Sales Revenue', 'Revenue'), (13, '4030', 'Sales Returns', 'Contra Revenue'), (14, '5010', 'COGS', 'Expense'), (15, '5110', 'Purchase Price Variance', 'Expense'), (16, '5130', 'Cash Over / Short', 'Expense or income'), (17, '5140', 'Stock Write-off', 'Expense');

        CREATE TABLE account_journal_entries (id INTEGER PRIMARY KEY AUTOINCREMENT, entry_number TEXT UNIQUE, entry_date TEXT, ref_doc TEXT, memo TEXT, is_posted INTEGER DEFAULT 1);
        CREATE TABLE account_journal_items (id INTEGER PRIMARY KEY AUTOINCREMENT, entry_id INTEGER REFERENCES account_journal_entries(id), account_id INTEGER REFERENCES chart_of_accounts(id), partner_id INTEGER REFERENCES partners(id), debit INTEGER DEFAULT 0, credit INTEGER DEFAULT 0);
        INSERT INTO account_journal_entries VALUES (1, 'JRNL/2026/00001', date('now'), 'OPENING_BALANCE', 'Jurnal Saldo Awal PT RantauMart Riau', 1);
        INSERT INTO account_journal_items VALUES (1, 1, 4, NULL, 9628900, 0), (2, 1, 3, 2, 888000, 0), (3, 1, 1, NULL, 15000000, 0), (4, 1, 2, NULL, 200000, 0), (5, 1, 8, 7, 0, 555000), (6, 1, 10, NULL, 0, 25161900);

        CREATE TABLE account_invoices (id INTEGER PRIMARY KEY AUTOINCREMENT, invoice_number TEXT UNIQUE, invoice_type TEXT, partner_id INTEGER REFERENCES partners(id), reference_doc TEXT, invoice_date TEXT, due_date TEXT, status TEXT DEFAULT 'posted', amount_untaxed INTEGER DEFAULT 0, amount_tax INTEGER DEFAULT 0, amount_total INTEGER DEFAULT 0, amount_paid INTEGER DEFAULT 0, amount_residual INTEGER DEFAULT 0);
        INSERT INTO account_invoices VALUES (1, 'INV/2026/00001', 'customer_invoice', 2, 'SALDO-AWAL-AR', date('now', '-10 days'), date('now', '+20 days'), 'posted', 800000, 88000, 888000, 0, 888000), (2, 'BILL/2026/00001', 'vendor_bill', 7, 'SALDO-AWAL-AP', date('now', '-5 days'), date('now', '+25 days'), 'posted', 500000, 55000, 555000, 0, 555000);

        CREATE TABLE crm_leads (id INTEGER PRIMARY KEY AUTOINCREMENT, lead_number TEXT UNIQUE, partner_id INTEGER REFERENCES partners(id), name TEXT, expected_revenue INTEGER DEFAULT 0, stage TEXT DEFAULT 'lead');
        INSERT INTO crm_leads VALUES (1, 'LEAD-0001', 1, 'Pesanan Rutin Warung Kopi Pasar', 88800, 'lead');

        CREATE TABLE sales_orders (id INTEGER PRIMARY KEY AUTOINCREMENT, order_number TEXT UNIQUE, partner_id INTEGER REFERENCES partners(id), location_id INTEGER REFERENCES locations(id), order_date TEXT, status TEXT DEFAULT 'confirmed', subtotal INTEGER DEFAULT 32000, discount_amount INTEGER DEFAULT 0, tax_amount INTEGER DEFAULT 3520, grand_total INTEGER DEFAULT 35520);
        CREATE TABLE sales_order_lines (id INTEGER PRIMARY KEY AUTOINCREMENT, order_id INTEGER REFERENCES sales_orders(id), product_id INTEGER REFERENCES products(id), ordered_qty INTEGER, delivered_qty INTEGER DEFAULT 0, invoiced_qty INTEGER DEFAULT 0, unit_price INTEGER, discount_percent REAL DEFAULT 0, subtotal INTEGER, tax_amount INTEGER, total INTEGER);
        INSERT INTO sales_orders VALUES (1, 'S00007', 1, 1, date('now'), 'confirmed', 32000, 0, 3520, 35520);
        INSERT INTO sales_order_lines VALUES (1, 1, 1, 8, 0, 0, 4000, 0, 32000, 3520, 35520);

        CREATE TABLE purchase_orders (id INTEGER PRIMARY KEY AUTOINCREMENT, po_number TEXT UNIQUE, partner_id INTEGER REFERENCES partners(id), location_id INTEGER REFERENCES locations(id), order_date TEXT, status TEXT DEFAULT 'confirmed', subtotal INTEGER DEFAULT 90000, tax_amount INTEGER DEFAULT 9900, grand_total INTEGER DEFAULT 99900);
        CREATE TABLE purchase_order_lines (id INTEGER PRIMARY KEY AUTOINCREMENT, po_id INTEGER REFERENCES purchase_orders(id), product_id INTEGER REFERENCES products(id), ordered_qty INTEGER, received_qty INTEGER DEFAULT 0, accepted_qty INTEGER DEFAULT 0, rejected_qty INTEGER DEFAULT 0, billed_qty INTEGER DEFAULT 0, unit_price INTEGER, subtotal INTEGER, tax_amount INTEGER, total INTEGER);
        INSERT INTO purchase_orders VALUES (1, 'PO00007', 7, 1, date('now'), 'confirmed', 90000, 9900, 99900);
        INSERT INTO purchase_order_lines VALUES (1, 1, 8, 30, 0, 0, 0, 0, 3000, 90000, 9900, 99900);

        CREATE TABLE pos_sessions (id INTEGER PRIMARY KEY AUTOINCREMENT, session_number TEXT UNIQUE, location_id INTEGER REFERENCES locations(id), user_id INTEGER REFERENCES users(id), opening_cash INTEGER DEFAULT 200000, closing_cash INTEGER, expected_cash INTEGER DEFAULT 200000, cash_variance INTEGER DEFAULT 0, status TEXT DEFAULT 'opened');
        INSERT INTO pos_sessions (id, session_number, location_id, user_id, opening_cash, expected_cash, status) VALUES (1, 'POS-SESSION-0001', 2, 5, 200000, 200000, 'opened');

        CREATE TABLE pos_orders (id INTEGER PRIMARY KEY AUTOINCREMENT, order_number TEXT UNIQUE, session_id INTEGER REFERENCES pos_sessions(id), location_id INTEGER REFERENCES locations(id), total_amount INTEGER, tax_amount INTEGER, grand_total INTEGER, cash_tendered INTEGER, change_amount INTEGER, payment_method TEXT DEFAULT 'cash', status TEXT DEFAULT 'paid', created_at TEXT DEFAULT CURRENT_TIMESTAMP);
        CREATE TABLE pos_order_lines (id INTEGER PRIMARY KEY AUTOINCREMENT, order_id INTEGER REFERENCES pos_orders(id), product_id INTEGER REFERENCES products(id), qty INTEGER, unit_price INTEGER, tax_amount INTEGER, subtotal INTEGER);

        CREATE TABLE audit_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, actor_username TEXT, action TEXT, entity_name TEXT, entity_id TEXT, old_values TEXT, new_values TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP);
        INSERT INTO audit_logs (id, actor_username, action, entity_name, entity_id, old_values, new_values, created_at) VALUES (1, 'system', 'SEED_INIT', 'SYSTEM', 'BASELINE', NULL, 'Baseline 15 SKU, On Hand 1500, Valuation 9628900 initialized.', CURRENT_TIMESTAMP);
        ";

        $pdo->exec($sql);
    }
}
