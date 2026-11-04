<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koneksi PostgreSQL Belum Siap — ERP RantauMart Riau</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-3xl w-full bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <!-- Error Banner Header -->
        <div class="bg-amber-500 text-white p-6 sm:p-8 flex items-start space-x-4">
            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm shrink-0">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Koneksi PostgreSQL Belum Tersedia</h1>
                <p class="text-amber-100 text-sm mt-1">
                    Server web ERP PT RantauMart Riau aktif di localhost, namun layanan PostgreSQL di port 5432 belum merespons atau belum dipasang di laptop.
                </p>
            </div>
        </div>

        <!-- Quick One-Click Solution for Students -->
        <div class="bg-emerald-50 border-b border-emerald-200 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-200 text-emerald-800 mb-1">
                    Solusi Instan untuk Demo / Presentasi
                </span>
                <h2 class="text-sm font-bold text-emerald-950">Ingin Langsung Melihat Tampilan Dashboard & Data Produk?</h2>
                <p class="text-xs text-emerald-700 mt-0.5">
                    Gunakan database lokal bawaan (SQLite). Seluruh 7 data baseline (1.500 pcs on-hand, Rp9.628.900 valuasi, dll) langsung tampil tanpa perlu menginstal PostgreSQL saat ini.
                </p>
            </div>
            <a href="?demo=1" 
               class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition-all shrink-0 flex items-center space-x-2">
                <span>⚡ Buka Mode Demo Sekarang</span>
                <span>→</span>
            </a>
        </div>

        <!-- Body Diagnostics -->
        <div class="p-6 sm:p-8 space-y-6">

            <!-- Technical Error Message -->
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-4">
                <h2 class="text-xs font-bold text-rose-800 uppercase tracking-wider mb-1 flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                    <span>Pesan Kesalahan Teknis (PDO PostgreSQL Driver)</span>
                </h2>
                <div class="bg-white/80 p-3 rounded-lg border border-rose-100 font-mono text-xs text-rose-900 break-all select-all">
                    <?= htmlspecialchars($errorMessage ?? 'Tidak dapat membuka koneksi ke host database.'); ?>
                </div>
            </div>

            <!-- Parameters Attempted -->
            <div>
                <h2 class="text-sm font-bold text-slate-900 mb-3">Parameter Koneksi yang Dicoba:</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="text-slate-500 block">Host</span>
                        <span class="font-bold text-slate-800"><?= htmlspecialchars($params['host'] ?? 'localhost'); ?></span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="text-slate-500 block">Port</span>
                        <span class="font-bold text-slate-800"><?= htmlspecialchars($params['port'] ?? '5432'); ?></span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="text-slate-500 block">Database</span>
                        <span class="font-bold text-slate-800"><?= htmlspecialchars($params['dbname'] ?? 'rantaumart_erp'); ?></span>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="text-slate-500 block">User</span>
                        <span class="font-bold text-slate-800"><?= htmlspecialchars($params['user'] ?? 'postgres'); ?></span>
                    </div>
                </div>
                <div class="mt-2 text-xs text-slate-500 flex items-center justify-between">
                    <span>File <code>.env</code> lokal: <strong><?= !empty($params['env_loaded']) ? '✅ Ditemukan' : '⚠️ Belum Ada (menggunakan default)'; ?></strong></span>
                    <span>Password: <strong><?= htmlspecialchars($params['password_masked'] ?? '••••••••'); ?></strong></span>
                </div>
            </div>

            <!-- Step by Step Setup Guide for Real PostgreSQL -->
            <div class="border-t border-slate-200 pt-6">
                <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-full bg-slate-800 text-white flex items-center justify-center text-xs">🛠️</span>
                    <span>Cara Setup PostgreSQL untuk Tugas Kuliah Resmi:</span>
                </h2>

                <div class="space-y-4 text-xs text-slate-700">
                    <!-- Step 1 -->
                    <div class="flex items-start space-x-3">
                        <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
                        <div>
                            <p class="font-semibold text-slate-900">Install PostgreSQL di Laptop Anda</p>
                            <p class="text-slate-500 mt-0.5">Jika di laptop belum ada PostgreSQL, unduh installer resmi dari <a href="https://www.enterprisedb.com/downloads/postgres-postgresql-downloads" target="_blank" class="text-blue-600 underline font-bold">enterprisedb.com (PostgreSQL for Windows)</a>. Saat instalasi, ingat password user <code>postgres</code> yang Anda masukkan.</p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex items-start space-x-3">
                        <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
                        <div>
                            <p class="font-semibold text-slate-900">Buat Database <code>rantaumart_erp</code></p>
                            <p class="text-slate-500 mt-0.5">Buka <strong>pgAdmin 4</strong> atau terminal psql, lalu buat database baru bernama <code>rantaumart_erp</code>.</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex items-start space-x-3">
                        <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
                        <div>
                            <p class="font-semibold text-slate-900">Eksekusi File Seed Baseline</p>
                            <p class="text-slate-500 mt-0.5">Jalankan file <code>outputs/01_rantau_mart_schema_seed.sql</code> di pgAdmin (Query Tool) atau via terminal psql.</p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="flex items-start space-x-3">
                        <span class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center shrink-0 mt-0.5">4</span>
                        <div>
                            <p class="font-semibold text-slate-900">Sesuaikan Password di File <code>.env</code></p>
                            <p class="text-slate-500 mt-0.5">Buka file <code>.env</code> di root proyek dan ganti <code>DB_PASSWORD=...</code> sesuai password PostgreSQL laptop Anda.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="pt-4 flex items-center justify-between border-t border-slate-200">
                <span class="text-xs text-slate-500">Setelah PostgreSQL aktif, silakan muat ulang halaman:</span>
                <button onclick="window.location.reload();" 
                        class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold shadow-md transition-all flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Coba Sambungkan Ulang (Refresh)</span>
                </button>
            </div>

        </div>
    </div>

</body>
</html>
