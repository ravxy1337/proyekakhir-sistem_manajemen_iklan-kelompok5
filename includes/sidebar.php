<?php

if (!isset($menu_aktif)) {
    $menu_aktif = '';
}
?>
<aside id="sidebar" class="w-64 bg-gray-900 text-white flex flex-col shrink-0">
    <div class="h-16 flex items-center justify-between px-6 border-b border-gray-800">
        <span class="text-xl font-bold text-white">iklanku<span class="text-blue-500">.</span></span>
        <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-white">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="px-3 space-y-1">
            <p class="px-3 text-xs font-semibold text-gray-500 uppercase mb-2">Menu Utama</p>
            <a href="<?= BASE_URL ?>/index.php"
                class="flex items-center px-3 py-2 text-sm rounded-md <?= $menu_aktif == 'dashboard' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i> Dashboard
            </a>

            <p class="px-3 text-xs font-semibold text-gray-500 uppercase mt-5 mb-2">Master Data</p>
            <a href="<?= BASE_URL ?>/jenis_iklan/index.php"
                class="flex items-center px-3 py-2 text-sm rounded-md <?= $menu_aktif == 'jenis_iklan' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' ?>">
                <i data-lucide="layers" class="w-5 h-5 mr-3"></i> Jenis Iklan
            </a>
            <a href="<?= BASE_URL ?>/lokasi/index.php"
                class="flex items-center px-3 py-2 text-sm rounded-md <?= $menu_aktif == 'lokasi' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' ?>">
                <i data-lucide="map-pin" class="w-5 h-5 mr-3"></i> Lokasi Baliho
            </a>

            <p class="px-3 text-xs font-semibold text-gray-500 uppercase mt-5 mb-2">Transaksi</p>
            <a href="<?= BASE_URL ?>/penyewaan/index.php"
                class="flex items-center px-3 py-2 text-sm rounded-md <?= $menu_aktif == 'penyewaan' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' ?>">
                <i data-lucide="shopping-cart" class="w-5 h-5 mr-3"></i> Penyewaan
            </a>
            <a href="<?= BASE_URL ?>/pembayaran/index.php"
                class="flex items-center px-3 py-2 text-sm rounded-md <?= $menu_aktif == 'pembayaran' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' ?>">
                <i data-lucide="credit-card" class="w-5 h-5 mr-3"></i> Pembayaran
            </a>

            <p class="px-3 text-xs font-semibold text-gray-500 uppercase mt-5 mb-2">Informasi</p>
            <a href="<?= BASE_URL ?>/jadwal/index.php"
                class="flex items-center px-3 py-2 text-sm rounded-md <?= $menu_aktif == 'jadwal' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' ?>">
                <i data-lucide="calendar" class="w-5 h-5 mr-3"></i> Jadwal Tayang
            </a>
            <a href="<?= BASE_URL ?>/laporan/index.php"
                class="flex items-center px-3 py-2 text-sm rounded-md <?= $menu_aktif == 'laporan' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' ?>">
                <i data-lucide="file-text" class="w-5 h-5 mr-3"></i> Laporan
            </a>
        </nav>
    </div>
    <div class="p-4 border-t border-gray-800">
        <div class="flex items-center justify-between">
            <a href="<?= BASE_URL ?>/setting.php" class="flex items-center hover:opacity-80" title="Pengaturan Akun">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold">
                    <?= substr($_SESSION['nama'], 0, 1) ?>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white"><?= htmlspecialchars($_SESSION['nama']) ?></p>
                    <p class="text-xs text-gray-400">
                        <i data-lucide="settings" class="w-3 h-3 inline"></i> Pengaturan
                    </p>
                </div>
            </a>
            <button type="button" onclick="bukaPopupLogout()" class="text-gray-400 hover:text-red-400 transition-colors" title="Logout">
                <i data-lucide="log-out" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
</aside>
<div class="flex-1 flex flex-col overflow-hidden">
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:px-8">
        <div class="flex items-center gap-3">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-600 hover:text-gray-900 p-1">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <div class="text-lg font-semibold text-gray-800 capitalize">
                <?php
                if ($menu_aktif == 'dashboard')
                    echo 'Dashboard Overview';
                else
                    echo str_replace('_', ' ', $menu_aktif);
                ?>
            </div>
        </div>
        <div class="text-sm text-gray-500 hidden sm:flex items-center">
            <i data-lucide="clock" class="w-4 h-4 mr-2"></i>
            <?= date('d M Y') ?>
        </div>
    </header>
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 md:p-8">

<div id="popupKonfirmasiLogout" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center animate-fade-in">
        <div class="w-12 h-12 rounded-full bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="log-out" class="w-6 h-6"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Keluar</h3>
        <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin keluar dari sistem aplikasi **iklanku**?</p>
        <div class="flex gap-3 justify-center">
            <button type="button" onclick="tutupPopupLogout()" 
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                Batal
            </button>
            <a href="<?= BASE_URL ?>/logout.php" 
                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-center rounded-lg text-sm font-medium transition-colors">
                Ya, Keluar
            </a>
        </div>
    </div>
</div>