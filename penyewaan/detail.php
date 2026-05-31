<?php
require_once '../includes/header.php';
$menu_aktif = 'penyewaan';
require_once '../includes/sidebar.php';

$id_penyewaan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query_detail = $conn->prepare("
    SELECT p.*, 
           l.nama_lokasi, l.alamat as alamat_lokasi, l.kode_lokasi,
           j.nama_jenis,
           b.total_tagihan, b.dibayar, b.sisa_tagihan, b.status_pembayaran
    FROM penyewaan p
    JOIN lokasi l ON p.id_lokasi = l.id
    JOIN jenis_iklan j ON p.id_jenis = j.id
    JOIN pembayaran b ON p.id = b.id_penyewaan
    WHERE p.id = ?
");
$query_detail->bind_param("i", $id_penyewaan);
$query_detail->execute();
$data_penyewaan = $query_detail->get_result()->fetch_assoc();

if (!$data_penyewaan) {
    $_SESSION['error'] = "Data tidak ditemukan.";
    echo "<script>window.location.href='index.php';</script>";
    exit;
}
?>

<div class="max-w-5xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Detail Transaksi</h2>
            <p class="text-sm text-gray-500 mt-1">Invoice: <span class="font-bold text-gray-700"><?= htmlspecialchars($data_penyewaan['no_invoice']) ?></span></p>
        </div>
        <a href="index.php" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 text-center">
            Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-3 bg-white rounded-lg shadow border border-gray-200 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-wrap gap-6 sm:gap-8">
                <div>
                    <p class="text-xs text-gray-500 font-semibold mb-1 uppercase">Status Penyewaan</p>
                    <?php
                    $warna_status_penyewaan = 'bg-gray-100 text-gray-800';
                    if ($data_penyewaan['status_penyewaan'] == 'Pending')     $warna_status_penyewaan = 'bg-orange-100 text-orange-800';
                    else if ($data_penyewaan['status_penyewaan'] == 'Aktif')      $warna_status_penyewaan = 'bg-green-100 text-green-800';
                    else if ($data_penyewaan['status_penyewaan'] == 'Selesai')    $warna_status_penyewaan = 'bg-blue-100 text-blue-800';
                    else if ($data_penyewaan['status_penyewaan'] == 'Dibatalkan') $warna_status_penyewaan = 'bg-red-100 text-red-800';
                    ?>
                    <span class="inline-block px-3 py-1 rounded text-sm font-bold <?= $warna_status_penyewaan ?>">
                        <?= strtoupper($data_penyewaan['status_penyewaan']) ?>
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-semibold mb-1 uppercase">Status Pembayaran</p>
                    <?php
                    $warna_status_bayar = 'text-gray-800';
                    if ($data_penyewaan['status_pembayaran'] == 'Belum Bayar') $warna_status_bayar = 'text-red-600 bg-red-50 px-2 py-0.5 rounded';
                    else if ($data_penyewaan['status_pembayaran'] == 'DP')     $warna_status_bayar = 'text-orange-600 bg-orange-50 px-2 py-0.5 rounded';
                    else if ($data_penyewaan['status_pembayaran'] == 'Lunas')  $warna_status_bayar = 'text-green-600 bg-green-50 px-2 py-0.5 rounded';
                    ?>
                    <span class="text-sm font-bold <?= $warna_status_bayar ?>">
                        <?= strtoupper($data_penyewaan['status_pembayaran']) ?>
                    </span>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-semibold mb-1 uppercase">Total Tagihan</p>
                <p class="text-2xl font-bold text-gray-900">Rp <?= number_format($data_penyewaan['total_tagihan'], 0, ',', '.') ?></p>
            </div>
        </div>

        <div class="md:col-span-1 bg-white rounded-lg shadow border border-gray-200 p-6">
            <h3 class="text-sm font-bold text-gray-800 uppercase mb-4 pb-2 border-b">Informasi Pelanggan</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1">PIC / Perusahaan</p>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data_penyewaan['nama_pic']) ?></p>
                    <?php if($data_penyewaan['nama_pt']): ?>
                        <p class="text-xs text-gray-600"><?= htmlspecialchars($data_penyewaan['nama_pt']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Kontak</p>
                    <p class="text-sm text-gray-900"><i data-lucide="phone" class="w-3.5 h-3.5 mr-2 inline text-gray-400"></i> <?= htmlspecialchars($data_penyewaan['no_hp']) ?></p>
                    <p class="text-sm text-gray-900 mt-1"><i data-lucide="mail" class="w-3.5 h-3.5 mr-2 inline text-gray-400"></i> <?= htmlspecialchars($data_penyewaan['email']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Alamat</p>
                    <p class="text-sm text-gray-900"><?= nl2br(htmlspecialchars($data_penyewaan['alamat'])) ?></p>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 bg-white rounded-lg shadow border border-gray-200 p-6">
            <h3 class="text-sm font-bold text-gray-800 uppercase mb-4 pb-2 border-b">Detail Layanan Iklan</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Jenis Media</p>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data_penyewaan['nama_jenis']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Lokasi Pemasangan</p>
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($data_penyewaan['kode_lokasi']) ?> - <?= htmlspecialchars($data_penyewaan['nama_lokasi']) ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($data_penyewaan['alamat_lokasi']) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Periode Sewa</p>
                    <p class="text-sm font-medium text-gray-900"><?= date('d F Y', strtotime($data_penyewaan['tgl_mulai'])) ?> - <?= date('d F Y', strtotime($data_penyewaan['tgl_selesai'])) ?></p>
                    <p class="text-xs text-blue-600 font-medium mt-1"><?= $data_penyewaan['total_hari'] ?> Hari</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">File Media Promosi</p>
                    <?php if($data_penyewaan['file_media']): ?>
                        <a href="<?= BASE_URL ?>/uploads/media/<?= $data_penyewaan['file_media'] ?>" target="_blank" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                            <i data-lucide="external-link" class="w-4 h-4 mr-1"></i> Lihat Media
                        </a>
                    <?php else: ?>
                        <p class="text-sm text-gray-400 italic">Belum ada file media</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t">
                <div class="flex justify-between items-center text-sm mb-2">
                    <span class="text-gray-600">Total Harga Sewa</span>
                    <span class="font-medium text-gray-900">Rp <?= number_format($data_penyewaan['total_harga'], 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between items-center text-sm mb-2">
                    <span class="text-gray-600">Total Dibayar</span>
                    <span class="font-medium text-green-600">Rp <?= number_format($data_penyewaan['dibayar'], 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between items-center text-sm font-bold mt-3 pt-3 border-t">
                    <span class="text-gray-800">Sisa Tagihan</span>
                    <span class="text-red-600">Rp <?= number_format($data_penyewaan['sisa_tagihan'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
