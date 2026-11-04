<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'ERP RantauMart Riau' ?> — PT RantauMart Riau</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        odoo: {
                            purple: '#714B67',
                            darkPurple: '#593B52',
                            teal: '#017E84',
                            accent: '#F2F2F2',
                            gold: '#F0AD4E'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col antialiased">

    <!-- Top Odoo 17 Navigation Bar -->
    <header class="bg-[#714B67] text-white sticky top-0 z-50 shadow-md">
        <div class="max-w-[1600px] mx-auto px-3 sm:px-6">
            <div class="flex items-center justify-between h-13 py-2">
                
                <!-- Left: Odoo App Switcher Icon & Breadcrumb -->
                <div class="flex items-center space-x-3">
                    <!-- App Switcher Dropdown Toggle -->
                    <div class="relative group">
                        <button type="button" 
                                onclick="toggleAppMenu()" 
                                class="p-2 hover:bg-white/10 rounded-lg text-white font-bold transition-colors flex items-center space-x-1.5 focus:outline-none focus:ring-2 focus:ring-white/40">
                            <!-- 3x3 Grid Icon -->
                            <svg class="w-5 h-5 text-amber-300" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="4" cy="4" r="2.5"/>
                                <circle cx="12" cy="4" r="2.5"/>
                                <circle cx="20" cy="4" r="2.5"/>
                                <circle cx="4" cy="12" r="2.5"/>
                                <circle cx="12" cy="12" r="2.5"/>
                                <circle cx="20" cy="12" r="2.5"/>
                                <circle cx="4" cy="20" r="2.5"/>
                                <circle cx="12" cy="20" r="2.5"/>
                                <circle cx="20" cy="20" r="2.5"/>
                            </svg>
                            <span class="font-bold text-sm tracking-wide hidden sm:inline">Apps</span>
                        </button>

                        <!-- Apps Grid Modal / Dropdown -->
                        <div id="appDropdown" class="hidden absolute left-0 mt-2 w-84 sm:w-96 bg-white text-slate-800 rounded-2xl shadow-2xl border border-slate-200 p-4 z-50">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3 px-2">
                                Modul ERP PT RantauMart Riau
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                <a href="?page=dashboard" class="p-3 rounded-xl hover:bg-slate-100 flex flex-col items-center group transition">
                                    <span class="text-2xl mb-1 group-hover:scale-110 transition">📊</span>
                                    <span class="font-bold text-slate-700">Dashboard</span>
                                </a>
                                <a href="?page=crm" class="p-3 rounded-xl hover:bg-slate-100 flex flex-col items-center group transition">
                                    <span class="text-2xl mb-1 group-hover:scale-110 transition">📈</span>
                                    <span class="font-bold text-slate-700">CRM</span>
                                </a>
                                <a href="?page=sales" class="p-3 rounded-xl hover:bg-slate-100 flex flex-col items-center group transition">
                                    <span class="text-2xl mb-1 group-hover:scale-110 transition">💼</span>
                                    <span class="font-bold text-slate-700">Sales</span>
                                </a>
                                <a href="?page=purchase" class="p-3 rounded-xl hover:bg-slate-100 flex flex-col items-center group transition">
                                    <span class="text-2xl mb-1 group-hover:scale-110 transition">🛒</span>
                                    <span class="font-bold text-slate-700">Purchase</span>
                                </a>
                                <a href="?page=stock" class="p-3 rounded-xl hover:bg-slate-100 flex flex-col items-center group transition">
                                    <span class="text-2xl mb-1 group-hover:scale-110 transition">📦</span>
                                    <span class="font-bold text-slate-700">Inventory</span>
                                </a>
                                <a href="?page=accounting" class="p-3 rounded-xl hover:bg-slate-100 flex flex-col items-center group transition">
                                    <span class="text-2xl mb-1 group-hover:scale-110 transition">💳</span>
                                    <span class="font-bold text-slate-700">Invoicing</span>
                                </a>
                                <a href="?page=pos" class="p-3 rounded-xl hover:bg-emerald-50 border border-emerald-200 flex flex-col items-center group transition">
                                    <span class="text-2xl mb-1 group-hover:scale-110 transition">🏪</span>
                                    <span class="font-bold text-emerald-800">Point of Sale</span>
                                </a>
                                <a href="?page=products" class="p-3 rounded-xl hover:bg-slate-100 flex flex-col items-center group transition">
                                    <span class="text-2xl mb-1 group-hover:scale-110 transition">🏷️</span>
                                    <span class="font-bold text-slate-700">15 SKUs</span>
                                </a>
                                <a href="?page=contacts" class="p-3 rounded-xl hover:bg-slate-100 flex flex-col items-center group transition">
                                    <span class="text-2xl mb-1 group-hover:scale-110 transition">👥</span>
                                    <span class="font-bold text-slate-700">Contacts</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <span class="text-white/30 text-lg font-light">|</span>

                    <!-- Brand Title & Active Module Name -->
                    <div class="flex items-center space-x-2">
                        <span class="font-extrabold text-base tracking-tight">RantauMart</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-white/15 text-white/90 font-medium hidden sm:inline">
                            <?= $moduleBadge ?? 'Enterprise ERP' ?>
                        </span>
                    </div>

                    <!-- Top Sub-Navigation Links -->
                    <nav class="hidden lg:flex items-center space-x-1 pl-4 text-xs font-semibold text-white/80">
                        <a href="?page=dashboard" class="px-2.5 py-1.5 rounded hover:bg-white/10 hover:text-white transition <?= ($currentPage ?? '') === 'dashboard' ? 'bg-white/20 text-white' : '' ?>">Dashboard</a>
                        <a href="?page=sales" class="px-2.5 py-1.5 rounded hover:bg-white/10 hover:text-white transition <?= ($currentPage ?? '') === 'sales' ? 'bg-white/20 text-white' : '' ?>">Sales Order</a>
                        <a href="?page=delivery" class="px-2.5 py-1.5 rounded hover:bg-white/10 hover:text-white transition <?= ($currentPage ?? '') === 'delivery' ? 'bg-white/20 text-white' : '' ?>">Delivery</a>
                        <a href="?page=purchase" class="px-2.5 py-1.5 rounded hover:bg-white/10 hover:text-white transition <?= ($currentPage ?? '') === 'purchase' ? 'bg-white/20 text-white' : '' ?>">Purchase</a>
                        <a href="?page=receipt" class="px-2.5 py-1.5 rounded hover:bg-white/10 hover:text-white transition <?= ($currentPage ?? '') === 'receipt' ? 'bg-white/20 text-white' : '' ?>">Receipt</a>
                        <a href="?page=stock" class="px-2.5 py-1.5 rounded hover:bg-white/10 hover:text-white transition <?= ($currentPage ?? '') === 'stock' ? 'bg-white/20 text-white' : '' ?>">Stok per Lokasi</a>
                        <a href="?page=accounting" class="px-2.5 py-1.5 rounded hover:bg-white/10 hover:text-white transition <?= ($currentPage ?? '') === 'accounting' ? 'bg-white/20 text-white' : '' ?>">Invoice & Bills</a>
                        <a href="?page=pos" class="px-2.5 py-1.5 rounded bg-emerald-500 hover:bg-emerald-600 text-white transition font-bold shadow-sm">Buka Kasir POS</a>
                    </nav>
                </div>

                <!-- Right: Active Role, Location & Reset -->
                <div class="flex items-center space-x-3 text-xs">
                    <!-- Reset Baseline Fixture Button -->
                    <a href="?action=reset_baseline" 
                       onclick="return confirm('Kembalikan seluruh database ke data awal baseline RantauMart (1.500 pcs on-hand, Rp9.628.900 valuasi, dll)?');" 
                       class="hidden md:inline-flex items-center space-x-1 px-2.5 py-1 rounded bg-amber-400/20 hover:bg-amber-400/30 text-amber-200 border border-amber-400/30 font-medium transition" 
                       title="Reset data ke kondisi awal seed">
                        <span>↺ Reset Baseline</span>
                    </a>

                    <!-- User / Company Profile -->
                    <div class="flex items-center space-x-2 pl-2 border-l border-white/20">
                        <div class="w-7 h-7 rounded-full bg-amber-300 text-slate-900 font-extrabold flex items-center justify-center text-xs shadow-inner">
                            RM
                        </div>
                        <div class="text-right hidden sm:block">
                            <span class="block font-bold text-white leading-tight">PT RantauMart Riau</span>
                            <span class="block text-[10px] text-white/70">WH-PKU • PKP PPN 11%</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <script>
        function toggleAppMenu() {
            const menu = document.getElementById('appDropdown');
            menu.classList.toggle('hidden');
        }
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('button');
            const menu = document.getElementById('appDropdown');
            if (!e.target.closest('#appDropdown') && (!btn || !btn.contains(e.target))) {
                if (!menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                }
            }
        });
    </script>

    <!-- Main Content Container -->
    <main class="flex-grow max-w-[1600px] w-full mx-auto px-4 sm:px-6 py-6">
