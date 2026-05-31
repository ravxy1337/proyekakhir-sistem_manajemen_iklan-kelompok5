<?php
require_once '../includes/header.php';
$menu_aktif = 'lokasi';
require_once '../includes/sidebar.php';

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = "";
$params = [];
$types = "";

if ($search) {
    $where = "WHERE l.nama_lokasi LIKE ? OR l.kode_lokasi LIKE ?";
    $params = ["%$search%", "%$search%"];
    $types = "ss";
}

$q_total = "SELECT COUNT(l.id) as total FROM lokasi l $where";
$stmt_total = $conn->prepare($q_total);
if ($search) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$total_data = $stmt_total->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

$q_data = "SELECT l.*, j.nama_jenis FROM lokasi l 
           JOIN jenis_iklan j ON l.id_jenis = j.id 
           $where ORDER BY l.id DESC LIMIT ? OFFSET ?";

$stmt_data = $conn->prepare($q_data);

if ($search) {
    $types_data = $types . "ii";
    $params_data = array_merge($params, [$limit, $offset]);
    $stmt_data->bind_param($types_data, ...$params_data);
} else {
    $stmt_data->bind_param("ii", $limit, $offset);
}

$stmt_data->execute();
$result = $stmt_data->get_result();
?>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="p-4 md:p-6 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Manajemen Lokasi Baliho</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data titik lokasi pemasangan iklan</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <form action="" method="GET" class="relative">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                    placeholder="Cari lokasi/kode..."
                    class="w-full sm:w-56 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-3"></i>
            </form>
            <a href="create.php"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Lokasi
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <th class="px-6 py-3 font-semibold border-b">Kode</th>
                    <th class="px-6 py-3 font-semibold border-b">Lokasi</th>
                    <th class="px-6 py-3 font-semibold border-b hidden md:table-cell">Jenis</th>
                    <th class="px-6 py-3 font-semibold border-b hidden sm:table-cell">Harga/Hari</th>
                    <th class="px-6 py-3 font-semibold border-b">Status</th>
                    <th class="px-6 py-3 font-semibold border-b text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 border-b">
                            <td class="px-6 py-3 text-sm font-medium text-gray-900"><?= htmlspecialchars($row['kode_lokasi']) ?>
                            </td>
                            <td class="px-6 py-3 text-sm">
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($row['nama_lokasi']) ?></p>
                                <p class="text-gray-500 text-xs mt-1 truncate max-w-xs"><?= htmlspecialchars($row['alamat']) ?>
                                </p>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600 hidden md:table-cell">
                                <?= htmlspecialchars($row['nama_jenis']) ?><br>
                                <span class="text-xs text-gray-400">Uk: <?= htmlspecialchars($row['ukuran'] ?? '-') ?></span>
                            </td>
                            <td class="px-6 py-3 text-sm font-medium hidden sm:table-cell">
                                Rp <?= number_format($row['harga_per_hari'], 0, ',', '.') ?>
                            </td>
                            <td class="px-6 py-3 text-sm">
                                <?php
                                // Menentukan warna label berdasarkan status lokasi
                                $warna = 'bg-gray-100 text-gray-800';
                                if ($row['status_lokasi'] == 'tersedia')
                                    $warna = 'bg-green-100 text-green-800';
                                elseif ($row['status_lokasi'] == 'digunakan')
                                    $warna = 'bg-blue-100 text-blue-800';
                                elseif ($row['status_lokasi'] == 'maintenance')
                                    $warna = 'bg-orange-100 text-orange-800';
                                ?>
                                <span class="px-2 py-1 rounded text-xs font-medium <?= $warna ?>">
                                    <?= ucfirst($row['status_lokasi']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-right space-x-1">
                                <?php if ($row['foto_lokasi']): ?>
                                    <button onclick="showPhoto('<?= BASE_URL ?>/uploads/lokasi/<?= $row['foto_lokasi'] ?>')"
                                        class="text-blue-600 p-1.5 bg-blue-50 rounded inline-flex" title="Lihat Foto">
                                        <i data-lucide="image" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>
                                <a href="edit.php?id=<?= $row['id'] ?>"
                                    class="text-blue-600 p-1.5 bg-blue-50 rounded inline-flex" title="Edit">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </a>
                                <button type="button" onclick="confirmDelete(<?= $row['id'] ?>, 'popupDeleteLokasi')"
                                    class="text-red-600 p-1.5 bg-red-50 rounded inline-flex" title="Hapus">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Tidak ada data ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="px-6 py-4 border-t flex items-center justify-between">
            <span class="text-sm text-gray-500">Data <?= min($offset + 1, $total_data) ?> -
                <?= min($offset + $limit, $total_data) ?> dari <?= $total_data ?></span>
            <div class="flex gap-1">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                        class="px-3 py-1 text-sm rounded <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="popupDeleteLokasi" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="trash-2" class="w-6 h-6"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Hapus</h3>
        <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus data lokasi ini secara permanen?</p>
        <div class="flex gap-3 justify-center">
            <button type="button" onclick="tutupPopupDelete('popupDeleteLokasi')" 
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
<div id="popupGagalLokasi" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center animate-fade-in">
        <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
        </div>
        
        <h3 class="text-lg font-bold text-gray-900 mb-2">Gagal Menghapus</h3>
        <p class="text-sm text-gray-500 mb-6"><?= $_SESSION['error']; ?></p>
        
        <div class="flex justify-center">
            <button type="button" onclick="tutupPopupGagal('popupGagalLokasi')" 
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
<div id="popupSuksesLokasi" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center animate-fade-in">
        
        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="check-circle" class="w-6 h-6"></i>
        </div>
        
        <h3 class="text-lg font-bold text-gray-900 mb-2">Aksi Berhasil</h3>
        
        <p class="text-sm text-gray-500 mb-6"><?= $_SESSION['success']; ?></p>
        
        <div class="flex justify-center">
            <button type="button" onclick="tutupPopupSukses('popupSuksesLokasi')" 
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