<?php

// CLI Database Connection Checker & Diagnostic Script

require_once dirname(__DIR__) . '/app/Database.php';

use App\Database;

echo "=======================================================\n";
echo "ERP PT RantauMart Riau — Pengecekan Koneksi PostgreSQL\n";
echo "=======================================================\n\n";

Database::loadEnv(dirname(__DIR__) . '/.env');

$params = Database::getConnectionParams();
echo "Parameter Koneksi:\n";
echo " - Host     : " . ($params['host'] ?? 'localhost') . "\n";
echo " - Port     : " . ($params['port'] ?? '5432') . "\n";
echo " - Database : " . ($params['dbname'] ?? 'rantaumart_erp') . "\n";
echo " - User     : " . ($params['user'] ?? 'postgres') . "\n";
echo " - Password : " . ($params['password_masked'] ?? '(kosong)') . "\n";
echo " - File .env: " . (!empty($params['env_loaded']) ? "Ditemukan (.env)" : "Belum dibuat (menggunakan default)") . "\n\n";

echo "Mencoba menghubungkan ke PostgreSQL...\n";
$pdo = Database::getConnection();

if ($pdo !== null) {
    echo "✅ KONEKSI BERHASIL KE POSTGRESQL!\n\n";

    try {
        $viewCheck = $pdo->query("SELECT * FROM v_baseline_control_check LIMIT 1")->fetch();
        if ($viewCheck) {
            echo "Status Data Baseline di PostgreSQL:\n";
            echo " - Total On Hand Stock   : " . number_format($viewCheck['total_on_hand_qty'], 0, ',', '.') . " pcs\n";
            echo " - Nilai Persediaan      : Rp " . number_format($viewCheck['total_inventory_value'], 0, ',', '.') . "\n";
            echo " - Piutang Usaha (AR)    : Rp " . number_format($viewCheck['total_ar_balance'], 0, ',', '.') . "\n";
            echo " - Utang Usaha (AP)      : Rp " . number_format($viewCheck['total_ap_balance'], 0, ',', '.') . "\n";
            echo " - Saldo Kas Bank        : Rp " . number_format($viewCheck['bank_balance'], 0, ',', '.') . "\n";
            echo " - Kas Kasir POS Toko    : Rp " . number_format($viewCheck['pos_cash_opening'], 0, ',', '.') . "\n";
            echo " - Avail AIR-600 WH-PKU  : " . $viewCheck['available_air600_wh_pku'] . " pcs\n";
            echo "\nSemua tabel dan seed telah terpasang dengan sempurna di database!\n";
        }
    } catch (\Throwable $e) {
        echo "⚠️ Koneksi database berhasil, namun tabel atau view baseline belum ada.\n";
        echo "Jalankan seed berikut: psql -U postgres -d rantaumart_erp -f outputs/01_rantau_mart_schema_seed.sql\n";
    }
} else {
    echo "❌ KONEKSI GAGAL!\n";
    echo "Pesan Error: " . Database::getLastError() . "\n\n";
    echo "Solusi:\n";
    echo "1. Pastikan PostgreSQL service berjalan (cek via pgAdmin atau services.msc).\n";
    echo "2. Salin .env.example menjadi .env dan masukkan password PostgreSQL Anda.\n";
    echo "3. Buat database: CREATE DATABASE rantaumart_erp;\n";
    echo "4. Eksekusi seed: outputs/01_rantau_mart_schema_seed.sql\n";
}
echo "=======================================================\n";
