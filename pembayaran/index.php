<?php
require_once '../includes/header.php';
$menu_aktif = 'pembayaran';
require_once '../includes/sidebar.php';

$cari = $_GET['search'] ?? '';
$halaman = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$batas_data = 10;
$mulai_dari = ($halaman - 1) * $batas_data;

$filter_pencarian = "";
$data_parameter = [];
$tipe_parameter = "";

if ($cari) {
    $filter_pencarian = "WHERE p.no_invoice LIKE ? OR p.nama_pt LIKE ? OR p.nama_pic LIKE ?";
    $nilai_cari = "%$cari%";
    $data_parameter = [$nilai_cari, $nilai_cari, $nilai_cari];
    $tipe_parameter = "sss";
}

$perintah_jumlah = "SELECT COUNT(pb.id) as total FROM pembayaran pb JOIN penyewaan p ON pb.id_penyewaan = p.id $filter_pencarian";
$siapkan_jumlah = $conn->prepare($perintah_jumlah);
if ($cari) {
    $siapkan_jumlah->bind_param($tipe_parameter, ...$data_parameter);
}
$siapkan_jumlah->execute();
$total_semua_data = $siapkan_jumlah->get_result()->fetch_assoc()['total'];
$total_halaman = ceil($total_semua_data / $batas_data);

$perintah_data = "SELECT pb.*, p.no_invoice, p.nama_pt, p.nama_pic, p.status_penyewaan 
           FROM pembayaran pb 
           JOIN penyewaan p ON pb.id_penyewaan = p.id 
           $filter_pencarian 
           ORDER BY pb.id DESC LIMIT ? OFFSET ?";

$siapkan_data = $conn->prepare($perintah_data);

if ($cari) {
    $data_parameter[] = $batas_data;
    $data_parameter[] = $mulai_dari;
    $siapkan_data->bind_param($tipe_parameter . "ii", ...$data_parameter);
} else {
    $siapkan_data->bind_param("ii", $batas_data, $mulai_dari);
}

$siapkan_data->execute();
$hasil_data = $siapkan_data->get_result();
?>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <div class="p-4 md:p-6 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Manajemen Pembayaran</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola transaksi dan cicilan pembayaran pelanggan</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <form action="" method="GET" class="relative">
                <input type="text" name="search" value="<?= htmlspecialchars($cari) ?>"
                    placeholder="Cari invoice/pelanggan..."
                    class="w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-3"></i>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <th class="px-6 py-3 font-semibold border-b">Invoice</th>
                    <th class="px-6 py-3 font-semibold border-b">Pelanggan</th>
                    <th class="px-6 py-3 font-semibold border-b text-right hidden sm:table-cell">Total Tagihan</th>
                    <th class="px-6 py-3 font-semibold border-b text-right hidden md:table-cell">Dibayar</th>
                    <th class="px-6 py-3 font-semibold border-b text-right">Sisa Tagihan</th>
                    <th class="px-6 py-3 font-semibold border-b">Status</th>
                    <th class="px-6 py-3 font-semibold border-b text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($hasil_data->num_rows > 0): ?>
                    <?php while ($baris = $hasil_data->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 border-b">
                            <td class="px-6 py-3">
                                <a href="../penyewaan/detail.php?id=<?= $baris['id_penyewaan'] ?>"
                                    class="text-sm font-bold text-blue-600 hover:text-blue-800">
                                    <?= htmlspecialchars($baris['no_invoice']) ?>
                                </a>
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-800">
                                <?= htmlspecialchars($baris['nama_pt'] ?: $baris['nama_pic']) ?>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-800 font-medium text-right hidden sm:table-cell">
                                Rp <?= number_format($baris['total_tagihan'], 0, ',', '.') ?>
                            </td>
                            <td class="px-6 py-3 text-sm text-green-600 font-medium text-right hidden md:table-cell">
                                Rp <?= number_format($baris['dibayar'], 0, ',', '.') ?>
                            </td>
                            <td class="px-6 py-3 text-sm text-red-600 font-medium text-right">
                                Rp <?= number_format($baris['sisa_tagihan'], 0, ',', '.') ?>
                            </td>
                            <td class="px-6 py-3">
                                <?php
                                $warna_status = 'bg-gray-100 text-gray-800';
                                if ($baris['status_pembayaran'] == 'Belum Bayar')
                                    $warna_status = 'bg-red-100 text-red-800';
                                else if ($baris['status_pembayaran'] == 'DP')
                                    $warna_status = 'bg-orange-100 text-orange-800';
                                else if ($baris['status_pembayaran'] == 'Lunas')
                                    $warna_status = 'bg-green-100 text-green-800';
                                ?>
                                <span class="px-2 py-1 rounded text-xs font-semibold <?= $warna_status ?>">
                                    <?= htmlspecialchars($baris['status_pembayaran']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="pay.php?id_penyewaan=<?= $baris['id_penyewaan'] ?>"
                                    class="text-blue-600 p-1.5 bg-blue-50 rounded inline-flex">
                                    <i data-lucide="<?= $baris['status_pembayaran'] == 'Lunas' ? 'file-text' : 'credit-card' ?>"
                                        class="w-4 h-4"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">Tidak ada data pembayaran.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_halaman > 1): ?>
        <div class="px-6 py-4 border-t flex items-center justify-between">
            <span class="text-sm text-gray-500"><?= min($mulai_dari + 1, $total_semua_data) ?> -
                <?= min($mulai_dari + $batas_data, $total_semua_data) ?> dari <?= $total_semua_data ?></span>
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

<?php require_once '../includes/footer.php'; ?>