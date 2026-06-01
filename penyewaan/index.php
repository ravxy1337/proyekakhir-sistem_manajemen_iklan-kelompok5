<?php
require_once '../includes/header.php';
$menu_aktif = 'penyewaan';
require_once '../includes/sidebar.php';

$kata_cari = $_GET['search'] ?? '';
$halaman_sekarang = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$jumlah_per_halaman = 10;
$mulai_dari = ($halaman_sekarang - 1) * $jumlah_per_halaman;

$kondisi_where = "";
$parameter = [];
$tipe_parameter = "";

if ($kata_cari) {
    $kondisi_where = "WHERE p.no_invoice LIKE ? OR p.nama_pt LIKE ? OR p.nama_pic LIKE ? OR l.nama_lokasi LIKE ?";
    $kata_cari_persen = "%$kata_cari%";
    $parameter = [$kata_cari_persen, $kata_cari_persen, $kata_cari_persen, $kata_cari_persen];
    $tipe_parameter = "ssss";
}

$query_hitung = "SELECT COUNT(p.id) as total FROM penyewaan p JOIN lokasi l ON p.id_lokasi = l.id $kondisi_where";
$stmt_hitung = $conn->prepare($query_hitung);
if ($kata_cari) {
    $stmt_hitung->bind_param($tipe_parameter, ...$parameter);
}
$stmt_hitung->execute();
$total_data = $stmt_hitung->get_result()->fetch_assoc()['total'];
$total_halaman = ceil($total_data / $jumlah_per_halaman);

$query_data = "SELECT p.*, l.nama_lokasi, j.nama_jenis, pb.status_pembayaran 
               FROM penyewaan p 
               JOIN lokasi l ON p.id_lokasi = l.id 
               JOIN jenis_iklan j ON p.id_jenis = j.id 
               LEFT JOIN pembayaran pb ON p.id = pb.id_penyewaan
               $kondisi_where ORDER BY p.id DESC LIMIT ? OFFSET ?";
$stmt_data = $conn->prepare($query_data);
if ($kata_cari) {
    $stmt_data->bind_param("ssssii", $kata_cari_persen, $kata_cari_persen, $kata_cari_persen, $kata_cari_persen, $jumlah_per_halaman, $mulai_dari);
} else {
    $stmt_data->bind_param("ii", $jumlah_per_halaman, $mulai_dari);
}
$stmt_data->execute();
$hasil_data = $stmt_data->get_result();
?>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="p-4 md:p-6 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Transaksi Penyewaan Iklan</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data penyewaan dan pantau statusnya</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <form action="" method="GET" class="relative">
                <input type="text" name="search" value="<?= htmlspecialchars($kata_cari) ?>"
                    placeholder="Cari invoice/pelanggan..."
                    class="w-full sm:w-56 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-3"></i>
            </form>
            <a href="create.php"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Transaksi Baru
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <th class="px-6 py-3 font-semibold border-b">Invoice</th>
                    <th class="px-6 py-3 font-semibold border-b">Pelanggan</th>
                    <th class="px-6 py-3 font-semibold border-b hidden md:table-cell">Layanan</th>
                    <th class="px-6 py-3 font-semibold border-b hidden lg:table-cell">Durasi</th>
                    <th class="px-6 py-3 font-semibold border-b">Status</th>
                    <th class="px-6 py-3 font-semibold border-b text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($hasil_data->num_rows > 0): ?>
                    <?php while ($baris = $hasil_data->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 border-b">
                            <td class="px-6 py-3">
                                <p class="text-sm font-bold text-blue-600"><?= htmlspecialchars($baris['no_invoice']) ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= date('d M Y', strtotime($baris['created_at'])) ?></p>
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-sm font-medium text-gray-800">
                                    <?= htmlspecialchars($baris['nama_pt'] ?: $baris['nama_pic']) ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($baris['no_hp']) ?></p>
                            </td>
                            <td class="px-6 py-3 hidden md:table-cell">
                                <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($baris['nama_jenis']) ?></p>
                                <p class="text-xs text-gray-500 mt-1 truncate max-w-xs">
                                    <?= htmlspecialchars($baris['nama_lokasi']) ?></p>
                            </td>
                            <td class="px-6 py-3 hidden lg:table-cell">
                                <p class="text-sm text-gray-800"><?= date('d/m/Y', strtotime($baris['tgl_mulai'])) ?> -
                                    <?= date('d/m/Y', strtotime($baris['tgl_selesai'])) ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= $baris['total_hari'] ?> Hari</p>
                            </td>
                            <td class="px-6 py-3">
                                <?php
                                $warna_status = 'bg-gray-100 text-gray-800';
                                if ($baris['status_penyewaan'] == 'Pending')
                                    $warna_status = 'bg-orange-100 text-orange-800';
                                elseif ($baris['status_penyewaan'] == 'Aktif')
                                    $warna_status = 'bg-green-100 text-green-800';
                                elseif ($baris['status_penyewaan'] == 'Selesai')
                                    $warna_status = 'bg-blue-100 text-blue-800';
                                elseif ($baris['status_penyewaan'] == 'Dibatalkan')
                                    $warna_status = 'bg-red-100 text-red-800';
                                ?>
                                <span class="px-2 py-1 rounded text-xs font-medium <?= $warna_status ?>">
                                    <?= $baris['status_penyewaan'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right space-x-1">
                                <a href="detail.php?id=<?= $baris['id'] ?>"
                                    class="text-gray-600 p-1.5 bg-gray-100 rounded inline-flex" title="Detail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <?php if (($baris['status_penyewaan'] == 'Pending' || $baris['status_penyewaan'] == 'Aktif') && $baris['status_pembayaran'] != 'Lunas'): ?>
                                    <button onclick="batalPesanan(<?= $baris['id'] ?>)"
                                        class="text-orange-600 p-1.5 bg-orange-50 rounded inline-flex" title="Batalkan">
                                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if ($baris['status_pembayaran'] != 'Lunas' && $baris['status_penyewaan'] != 'Aktif'): ?>
                                    <button onclick="confirmDelete(<?= $baris['id'] ?>, 'popupDeleteSewa')"
                                        class="text-red-600 p-1.5 bg-red-50 rounded inline-flex" title="Hapus Permanen">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <p class="text-sm">Tidak ada transaksi ditemukan.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_halaman > 1): ?>
        <div class="px-6 py-4 border-t flex items-center justify-between">
            <span class="text-sm text-gray-500"><?= min($mulai_dari + 1, $total_data) ?> - <?= min($mulai_dari + $jumlah_per_halaman, $total_data) ?>
                dari <?= $total_data ?></span>
            <div class="flex gap-1">
                <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                    <a href="?page=<?= $i ?><?= $kata_cari ? '&search=' . urlencode($kata_cari) : '' ?>"
                        class="px-3 py-1 text-sm rounded <?= $i == $halaman_sekarang ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="popupBatalPenyewaan" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center animate-fade-in">
        <div class="w-16 h-16 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="alert-circle" class="w-10 h-10"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Batalkan Pesanan?</h3>
        <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin membatalkan transaksi penyewaan ini?</p>
        <div class="flex gap-3 justify-center"> 
            <button type="button" onclick="tutupPopup('popupBatalPenyewaan')" 
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                Kembali
            </button>
            <a id="linkAksiBatal" href="#" 
                class="w-full px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-center rounded-lg text-sm font-medium transition-colors">
                Ya, Batalkan
            </a>
        </div>
    </div>
</div>

<div id="popupDeleteSewa" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="trash-2" class="w-6 h-6"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Hapus</h3>
        <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin menghapus data lokasi ini secara permanen?</p>
        <div class="flex gap-3 justify-center">
            <button type="button" onclick="tutupPopupDelete('popupDeleteSewa')" 
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
