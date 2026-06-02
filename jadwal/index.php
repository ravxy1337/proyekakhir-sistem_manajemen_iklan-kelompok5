<?php
require_once '../includes/header.php';
$menu_aktif = 'jadwal';
require_once '../includes/sidebar.php';

$filter_lokasi = $_GET['lokasi'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_tgl = $_GET['tgl'] ?? '';
$search = $_GET['search'] ?? '';

$q_data = "SELECT p.*, l.nama_lokasi, l.kode_lokasi, j.nama_jenis
           FROM penyewaan p
           JOIN lokasi l ON p.id_lokasi = l.id
           JOIN jenis_iklan j ON p.id_jenis = j.id
           WHERE 1=1 ";

if ($filter_lokasi != '') {
    $filter_lokasi = $conn->real_escape_string($filter_lokasi);
    $q_data .= "AND p.id_lokasi = '$filter_lokasi' ";
}
if ($filter_status != '') {
    $filter_status = $conn->real_escape_string($filter_status);
    $q_data .= "AND p.status_penyewaan = '$filter_status' ";
}
if ($filter_tgl != '') {
    $filter_tgl = $conn->real_escape_string($filter_tgl);
    $q_data .= "AND p.tgl_mulai <= '$filter_tgl' AND p.tgl_selesai >= '$filter_tgl' ";
}

if ($search != '') {
    $search = $conn->real_escape_string($search);
    $q_data .= "AND (p.nama_pt LIKE '%$search%' OR p.nama_pic LIKE '%$search%') ";
}

$q_data .= "ORDER BY p.tgl_mulai ASC";

$result = $conn->query($q_data);

$q_opt_lokasi = $conn->query("SELECT id, nama_lokasi FROM lokasi ORDER BY nama_lokasi ASC");
?>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden mb-6">
    <div class="p-4 md:p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">Jadwal Tayang Iklan</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau seluruh jadwal pemasangan iklan yang aktif maupun akan datang</p>
    </div>

    <div class="p-4 md:p-6 bg-gray-50 border-b border-gray-200">
        <form action="" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Cari Pelanggan</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nama PT / PIC..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Lokasi</label>
                <select name="lokasi" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                    <option value="">Semua Lokasi</option>
                    <?php while ($opt = $q_opt_lokasi->fetch_assoc()): ?>
                        <option value="<?= $opt['id'] ?>" <?= $filter_lokasi == $opt['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($opt['nama_lokasi']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                    <option value="">Semua Status</option>
                    <option value="Aktif" <?= $filter_status == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="Pending" <?= $filter_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Selesai" <?= $filter_status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Cari Tanggal Aktif</label>
                <div class="flex gap-2">
                    <input type="date" name="tgl" value="<?= htmlspecialchars($filter_tgl) ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                    <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                    </button>
                    <?php if ($filter_lokasi || $filter_status || $filter_tgl || $search): ?>
                        <a href="index.php"
                            class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-300 flex items-center justify-center">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <th class="px-6 py-3 font-semibold border-b">Lokasi & Media</th>
                    <th class="px-6 py-3 font-semibold border-b">Pelanggan</th>
                    <th class="px-6 py-3 font-semibold border-b">Jadwal Tayang</th>
                    <th class="px-6 py-3 font-semibold border-b">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50 border-b">
                            <td class="px-6 py-3">
                                <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($row['kode_lokasi']) ?> -
                                    <?= htmlspecialchars($row['nama_lokasi']) ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($row['nama_jenis']) ?></p>
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-sm font-medium text-gray-800">
                                    <?= htmlspecialchars($row['nama_pt'] ?: $row['nama_pic']) ?>
                                </p>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center gap-1 text-sm font-medium text-gray-800">
                                    <span
                                        class="bg-blue-50 text-blue-800 px-2 py-0.5 rounded"><?= date('d M Y', strtotime($row['tgl_mulai'])) ?></span>
                                    <span class="text-gray-400 text-xs px-1">s/d</span>
                                    <span
                                        class="bg-red-50 text-red-800 px-2 py-0.5 rounded"><?= date('d M Y', strtotime($row['tgl_selesai'])) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <?php
                                $statusColor = 'bg-gray-100 text-gray-800';
                                if ($row['status_penyewaan'] == 'Pending')
                                    $statusColor = 'bg-orange-100 text-orange-800';
                                else if ($row['status_penyewaan'] == 'Aktif')
                                    $statusColor = 'bg-green-100 text-green-800';
                                else if ($row['status_penyewaan'] == 'Selesai')
                                    $statusColor = 'bg-blue-100 text-blue-800';
                                ?>
                                <span class="px-2 py-1 rounded text-xs font-semibold <?= $statusColor ?>">
                                    <?= $row['status_penyewaan'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <p class="text-sm">Tidak ada jadwal ditemukan.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>