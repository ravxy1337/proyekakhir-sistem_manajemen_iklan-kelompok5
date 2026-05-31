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
    $tipe_dengan_limit = $tipe_parameter . "ii";
    $parameter_dengan_limit = array_merge($parameter, [$jumlah_per_halaman, $mulai_dari]);
    $stmt_data->bind_param($tipe_dengan_limit, $parameter_dengan_limit);
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
                                    <button onclick="hapusPesanan(<?= $baris['id'] ?>)"
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

<script>
    function batalPesanan(id_pesanan) {
        var yakin = confirm("Apakah Anda yakin ingin membatalkan pesanan ini?");
        if (yakin == true) {
            window.location.href = 'batal.php?id=' + id_pesanan;
        }
    }

    function hapusPesanan(id_pesanan) {
        var yakin = confirm("Hapus pesanan? Data penyewaan akan dihapus permanen dan tidak dapat dikembalikan!");
        if (yakin == true) {
            window.location.href = 'delete.php?id=' + id_pesanan;
        }
    }

    <?php if (isset($_SESSION['success'])): ?>
        alert('<?= $_SESSION['success'] ?>');
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        alert('<?= $_SESSION['error'] ?>');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>
