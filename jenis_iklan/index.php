<?php
require_once '../includes/header.php';
$menu_aktif = 'jenis_iklan';
require_once '../includes/sidebar.php';

$cari = $_GET['search'] ?? '';
$halaman = $_GET['page'] ?? 1;
$batas = 10;
$posisi = ($halaman - 1) * $batas;

if ($cari != '') {
    $kata_kunci = "%" . $cari . "%";
    $perintah_jumlah = $conn->prepare("SELECT COUNT(id) as total FROM jenis_iklan WHERE nama_jenis LIKE ?");
    $perintah_jumlah->bind_param("s", $kata_kunci);
} else {
    $perintah_jumlah = $conn->prepare("SELECT COUNT(id) as total FROM jenis_iklan");
}

$perintah_jumlah->execute();
$hasil_jumlah = $perintah_jumlah->get_result()->fetch_assoc();
$total_baris = $hasil_jumlah['total'];
$total_halaman = ceil($total_baris / $batas);

if ($cari != '') {
    $perintah_data = $conn->prepare("SELECT * FROM jenis_iklan WHERE nama_jenis LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?");
    $perintah_data->bind_param("sii", $kata_kunci, $batas, $posisi);
} else {
    $perintah_data = $conn->prepare("SELECT * FROM jenis_iklan ORDER BY id DESC LIMIT ? OFFSET ?");
    $perintah_data->bind_param("ii", $batas, $posisi);
}

$perintah_data->execute();
$data_iklan = $perintah_data->get_result();
?>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="p-4 md:p-6 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Manajemen Jenis Iklan</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola kategori dan tipe media iklan</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <form action="" method="GET" class="relative">
                <input type="text" name="search" value="<?= htmlspecialchars($cari) ?>"
                    placeholder="Cari jenis iklan..."
                    class="w-full sm:w-56 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-3"></i>
            </form>
            <a href="create.php"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Baru
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <th class="px-6 py-3 font-semibold border-b">No</th>
                    <th class="px-6 py-3 font-semibold border-b">Nama Jenis</th>
                    <th class="px-6 py-3 font-semibold border-b hidden sm:table-cell">Kategori</th>
                    <th class="px-6 py-3 font-semibold border-b">Status</th>
                    <th class="px-6 py-3 font-semibold border-b text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($data_iklan->num_rows > 0): ?>
                    <?php $no = $posisi + 1;
                    while ($row = $data_iklan->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 border-b">
                            <td class="px-6 py-3 text-sm text-gray-500"><?= $no++ ?></td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-800"><?= htmlspecialchars($row['nama_jenis']) ?>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500 hidden sm:table-cell">
                                <?= htmlspecialchars($row['kategori']) ?>
                            </td>
                            <td class="px-6 py-3 text-sm">
                                <span
                                    class="px-2 py-1 rounded text-xs font-medium <?= $row['status_aktif'] == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= ucfirst($row['status_aktif']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-right space-x-1">
                                <a href="edit.php?id=<?= $row['id'] ?>"
                                    class="text-blue-600 p-1.5 bg-blue-50 rounded inline-flex" title="Edit">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </a>
                                <button type="button" onclick="confirmDelete(<?= $row['id'] ?>, 'popupDeleteJenis')"
                                    class="text-red-600 p-1.5 bg-red-50 rounded inline-flex" title="Hapus">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <p class="text-sm">Tidak ada data jenis iklan.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_halaman > 1): ?>
        <div class="px-6 py-4 border-t flex items-center justify-between">
            <span class="text-sm text-gray-500"><?= min($posisi + 1, $total_baris) ?> sampai
                <?= min($posisi + $batas, $total_baris) ?>
                dari <?= $total_baris ?> data</span>
            <div class="flex gap-1">
                <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                    <a href="?page=<?= $i ?><?= $cari ? '&search=' . urlencode($cari) : '' ?>"
                        class="px-3 py-1 text-sm rounded <?= $i == $halaman ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="popupDeleteJenis" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="trash-2" class="w-6 h-6"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Hapus</h3>
        <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus data jenis iklan ini secara permanen?</p>
        <div class="flex gap-3 justify-center">
            <button type="button" onclick="tutupPopupDelete('popupDeleteJenis')" 
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                Batal
            </button>
            <a id="linkHapusNyata" href="#" 
                class="w-full px-4 py-2 bg-red-600 text-white text-center rounded-lg text-sm font-medium hover:bg-red-700">
                Ya, Hapus
            </a>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['error'])): ?>
<div id="popupGagalJenis" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center animate-fade-in">
        <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
        </div>
        
        <h3 class="text-lg font-bold text-gray-900 mb-2">Gagal</h3>
        <p class="text-sm text-gray-500 mb-6"><?= $_SESSION['error']; ?></p>
        
        <div class="flex justify-center">
            <button type="button" onclick="tutupPopupGagal('popupGagalJenis')" 
                class="w-full px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm font-medium transition-colors">
                Mengerti
            </button>
        </div>
    </div>
</div>
<?php 
    // Hapus session error agar pop-up tidak muncul lagi saat halaman di-refresh manual
    unset($_SESSION['error']); 
endif; 
?>

<?php if (isset($_SESSION['success'])): ?>
<div id="popupSuksesJenis" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center animate-fade-in">
        
        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="check-circle" class="w-6 h-6"></i>
        </div>
        
        <h3 class="text-lg font-bold text-gray-900 mb-2">Aksi Berhasil</h3>
        
        <p class="text-sm text-gray-500 mb-6"><?= $_SESSION['success']; ?></p>
        
        <div class="flex justify-center">
            <button type="button" onclick="tutupPopupSukses('popupSuksesJenis')" 
                class="w-full px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm font-medium transition-colors">
                Oke
            </button>
        </div>
    </div>
</div>

<?php 
    unset($_SESSION['success']); 
endif; 
?>

<?php require_once '../includes/footer.php'; ?>
