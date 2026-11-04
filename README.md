# ERP PT RantauMart Riau 🛒📦

Aplikasi Web Enterprise Resource Planning (ERP) orisinal untuk minimarket fiktif **PT RantauMart Riau**, terinspirasi oleh alur kerja dan integritas data **ERP Odoo** (Sales/CRM, Procurement/Purchase, Inventory, Double-Entry Accounting, Point of Sale, dan Audit Log).

---

## 💡 Tech Stack Pilihan & Alasan

Untuk proyek mahasiswa (tim 3 orang, target 4 minggu) dengan database **PostgreSQL**:
- **Bahasa & Backend:** **PHP 8.2 (Native Structured MVC + PDO PostgreSQL)**
  - *Alasan Pemilihan:* 
    1. **Tersedia Langsung di Sistem:** PHP 8.2 sudah terpasang di komputer pengguna (`C:\xampp\php`).
    2. **Zero-Dependency & Zero Bundler:** Tidak memerlukan `npm install`, compiler, atau bundler rumit (Webpack/Vite) yang rentan konflik dependensi pada tim pemula.
    3. **Integritas Transaksi ACID Jelas:** Pengendalian transaksi database (`BEGIN`, `COMMIT`, `ROLLBACK`) sangat transparan dan aman langsung pada level PDO.
    4. **Sangat Cepat & Ramah Pemula:** Pola MVC mudah dipahami oleh anggota tim mahasiswa tingkat awal.
- **Frontend:** **HTML5 + Tailwind CSS (via CDN) + Lucide Icons**
- **Database:** **PostgreSQL 14+** (dengan DDL skema relasional, check constraints, integer Rupiah, dan view baseline)

---

## ⚡ Panduan Menjalankan di Localhost

### 1. Salin Konfigurasi Environment
Salin file template `.env.example` menjadi `.env` (file `.env` sudah masuk `.gitignore` sehingga aman dari Git):
```bash
# Di Windows Command Prompt / PowerShell:
copy .env.example .env
```
Buka file `.env` dan masukkan password user PostgreSQL Anda jika menggunakan password:
```ini
DB_HOST=localhost
DB_PORT=5432
DB_NAME=rantaumart_erp
DB_USER=postgres
DB_PASSWORD=password_postgres_anda
```

### 2. Buat Database & Jalankan Seed PostgreSQL
Buka **pgAdmin** / **DBeaver** atau terminal **psql**:

#### Langkah A: Buat Database
```sql
CREATE DATABASE rantaumart_erp;
```

#### Langkah B: Eksekusi Seed Baseline
Jalankan file skrip SQL dari root proyek:
```bash
psql -U postgres -d rantaumart_erp -f outputs/01_rantau_mart_schema_seed.sql
```
*(Atau buka file `outputs/01_rantau_mart_schema_seed.sql` di pgAdmin / DBeaver lalu klik Execute/Run Script).*

### 3. Cek Status Koneksi Database (Opsional)
Anda dapat memverifikasi status koneksi dan data seed dari terminal dengan menjalankan:
```bash
php -d extension=pdo_pgsql -d extension=pgsql scripts/test_db_connection.php
```

### 4. Jalankan Web Server Localhost
Jalankan perintah berikut di folder proyek:
```bash
php -d extension=pdo_pgsql -d extension=pgsql -S localhost:8000 -t public
```
*(Pengguna Windows juga dapat langsung klik ganda file `run.bat`).*

Buka browser di:
👉 **`http://localhost:8000`**

---

## 🌐 Halaman Web yang Tersedia pada Versi Localhost Ini

1. **Dashboard Kontrol Baseline (`http://localhost:8000/?page=dashboard`)**
   - Menampilkan 7 parameter metrik kontrol baseline secara dinamis dari database PostgreSQL:
     - Total Stok On Hand: **1.500 pcs**
     - Nilai Persediaan: **Rp 9.628.900**
     - Piutang Usaha (AR): **Rp 888.000**
     - Utang Usaha (AP): **Rp 555.000**
     - Kas Bank Mandiri/BCA: **Rp 15.000.000**
     - Kas Kasir POS Toko: **Rp 200.000**
     - Available AIR-600 di WH-PKU: **72 pcs**
   - Indikator status live (*MATCH* / *SELISIH*).

2. **Daftar Produk & SKU (`http://localhost:8000/?page=products`)**
   - Menampilkan katalog 6 SKU (AIR-600, MIE-INS, MYK-01L, GUL-01K, BER-05K, SBN-LQD) lengkap dengan barcode, kategori, satuan, harga beli (HPP), harga jual, akumulasi On Hand, Available, dan total valuasi.

3. **Stok Persediaan per Lokasi (`http://localhost:8000/?page=stock`)**
   - Matriks persediaan riil per lokasi: `WH-PKU` (Gudang Pusat), `STORE-PKU` (Toko Pekanbaru), dan `STORE-DUM` (Toko Dumai).
   - Menampilkan rumus integritas Odoo: `Available = On Hand − Reserved`.
   - Menyorot baris baseline AIR-600 di WH-PKU (100 On Hand − 28 Reserved = 72 Available).

4. **Halaman Error Diagnostik Interaktif**
   - Jika layanan PostgreSQL belum aktif atau kredensial `.env` salah, sistem tidak akan crash, melainkan menampilkan halaman diagnostik ramah pengguna berbahasa Indonesia yang menunjukkan letak kesalahan dan panduan penyelesaiannya.

---

## 📁 Struktur File Proyek
```text
rantaumart-erp/
├── .env.example                     # Template kredensial PostgreSQL aman
├── .gitignore                       # Mencegah .env dan file sensitif ter-commit
├── README.md                        # Dokumentasi & panduan instalasi
├── run.bat                          # Launcher server localhost 1-klik untuk Windows
├── app/
│   ├── Database.php                 # Koneksi PDO PostgreSQL & loader .env aman
│   ├── Helpers.php                  # Format Rupiah integer & sanitasi data
│   └── Models/
│       ├── DashboardModel.php       # Query 7 metrik kontrol baseline dari DB
│       ├── ProductModel.php         # Query master produk & kategori dari DB
│       └── StockModel.php           # Query stok per lokasi & available stock dari DB
├── docs/
│   └── DECISION_LOG.md              # Log keputusan bisnis (SR-01 s/d SR-06)
├── outputs/
│   └── 01_rantau_mart_schema_seed.sql # Skema DDL & seed baseline lengkap
├── public/
│   └── index.php                    # Router & controller entry point
├── scripts/
│   ├── test_db_connection.php       # Diagnostik CLI koneksi PostgreSQL
│   └── verify_baseline.sql          # Query SQL verifikasi 7 parameter kontrol
├── tests/
│   └── test_pages.php               # Unit test logika baseline, integer Rupiah & view
└── views/
    ├── dashboard.php                # Template view Dashboard Baseline
    ├── products.php                 # Template view Katalog Produk
    ├── stock.php                    # Template view Stok per Lokasi
    ├── errors/
    │   └── db_error.php             # Halaman penanganan error koneksi DB
    └── layout/
        ├── header.php               # Navigasi & brand berbahasa Indonesia
        └── footer.php               # Informasi footer
```

---

## 🧪 Hasil Pengujian (Automated Verification)

Jalankan pengujian logika dan tampilan view dengan:
```bash
php tests/test_pages.php
```
Hasil pengujian:
```text
=== MEMULAI PENGUJIAN LOGIKA ERP & TAMPILAN VIEW ===

 [PASS] Total On Hand Stock == 1500 pcs => Aktual: 1500
 [PASS] Total Stock Valuation == Rp9.628.900 => Aktual: 9628900
 [PASS] Accounts Receivable (AR) == Rp888.000 => Aktual: 888000
 [PASS] Accounts Payable (AP) == Rp555.000 => Aktual: 555000
 [PASS] Bank Balance == Rp15.000.000 => Aktual: 15000000
 [PASS] POS Opening Cash == Rp200.000 => Aktual: 200000
 [PASS] Available AIR-600 WH-PKU == 72 pcs => Aktual: 72

--- Uji Kompilasi & Render View Templates ---
 [PASS] views/dashboard.php berhasil dirender (12868 bytes)
 [PASS] views/products.php berhasil dirender (14152 bytes, 6 produk)
 [PASS] views/stock.php berhasil dirender (42462 bytes, 18 baris quant)

=== SELURUH PENGUJIAN LOGIKA & VIEW BERHASIL (100% PASS) ===
```
