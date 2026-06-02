<?php
require_once 'includes/header.php';
$menu_aktif = 'dashboard';
require_once 'includes/sidebar.php';

// Mengambil data statistik 
$total_order = $conn->query("SELECT COUNT(id) as total FROM penyewaan")->fetch_assoc()['total'];
$total_omset = $conn->query("SELECT SUM(total_harga) as total FROM penyewaan")->fetch_assoc()['total'] ?? 0;
$total_aktif = $conn->query("SELECT COUNT(id) as total FROM penyewaan WHERE status_penyewaan = 'Aktif'")->fetch_assoc()['total'];
$total_selesai = $conn->query("SELECT COUNT(id) as total FROM penyewaan WHERE status_penyewaan = 'Selesai'")->fetch_assoc()['total'];
$total_pending = $conn->query("SELECT COUNT(pb.id) as total FROM pembayaran pb JOIN penyewaan p ON pb.id_penyewaan = p.id WHERE pb.status_pembayaran != 'Lunas' AND p.status_penyewaan != 'Dibatalkan'")->fetch_assoc()['total'];
$total_lokasi = $conn->query("SELECT COUNT(id) as total FROM lokasi WHERE status_lokasi = 'tersedia'")->fetch_assoc()['total'];

// Logika Grafik Batang
$tahun = date('Y');
$labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$data_omset = array_fill(0, 12, 0);

// Transaksi Aktif dan Selesai 
$query_normal = $conn->query("SELECT MONTH(tgl_mulai) as bln, SUM(total_harga) as omset FROM penyewaan WHERE YEAR(tgl_mulai) = '$tahun' AND status_penyewaan IN ('Aktif', 'Selesai') GROUP BY bln");
while ($baris = $query_normal->fetch_assoc()) {
    $data_omset[$baris['bln'] - 1] += $baris['omset'];
}

// trx db batal masuk 40% dari total_harga
$query_batal = $conn->query("SELECT MONTH(p.tgl_mulai) as bln, SUM(p.total_harga * 0.4) as omset FROM penyewaan p JOIN pembayaran pb ON p.id = pb.id_penyewaan WHERE YEAR(p.tgl_mulai) = '$tahun' AND p.status_penyewaan = 'Dibatalkan' AND pb.status_pembayaran = 'DP' GROUP BY bln");
while ($baris = $query_batal->fetch_assoc()) {
    $data_omset[$baris['bln'] - 1] += $baris['omset'];
}

// Logika filter bulan dan page aktivitas terbaru
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
$halaman = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_halaman = 5;
$mulai = ($halaman - 1) * $per_halaman;

$total_data = $conn->query("SELECT COUNT(id) as total FROM penyewaan WHERE DATE_FORMAT(created_at, '%Y-%m') = '$bulan'")->fetch_assoc()['total'];
$total_halaman = ceil($total_data / $per_halaman);

// Ambil data aktivitas 5 per halaman
$aktivitas = $conn->query("SELECT p.*, l.nama_lokasi FROM penyewaan p JOIN lokasi l ON p.id_lokasi = l.id WHERE DATE_FORMAT(p.created_at, '%Y-%m') = '$bulan' ORDER BY p.id DESC LIMIT $per_halaman OFFSET $mulai");
?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Order</p>
                <h3 class="text-2xl font-bold text-gray-800"><?= number_format($total_order, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                <i data-lucide="shopping-cart" class="w-6 h-6"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Omset</p>
                <h3 class="text-2xl font-bold text-gray-800">Rp <?= number_format($total_omset, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-green-50 rounded-lg text-green-600">
                <i data-lucide="dollar-sign" class="w-6 h-6"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 mb-1">Iklan Aktif</p>
                <h3 class="text-2xl font-bold text-gray-800"><?= number_format($total_aktif, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                <i data-lucide="play-circle" class="w-6 h-6"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 mb-1">Iklan Selesai</p>
                <h3 class="text-2xl font-bold text-gray-800"><?= number_format($total_selesai, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-gray-100 rounded-lg text-gray-600">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 mb-1">Pembayaran Pending</p>
                <h3 class="text-2xl font-bold text-gray-800"><?= number_format($total_pending, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm text-gray-500 mb-1">Lokasi Tersedia</p>
                <h3 class="text-2xl font-bold text-gray-800"><?= number_format($total_lokasi, 0, ',', '.') ?></h3>
            </div>
            <div class="p-2 bg-green-50 rounded-lg text-green-600">
                <i data-lucide="map-pin" class="w-6 h-6"></i>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 bg-white rounded-lg shadow border border-gray-200 p-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Grafik Pemasukan Bulanan (<?= $tahun ?>)</h3>
        <div class="relative w-full" style="min-height:200px;"><canvas id="omsetChart"></canvas></div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-200 p-5 flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Aktivitas Terbaru</h3>

            <form method="GET" action="" id="formBulan">
                <input type="month" name="bulan" value="<?= $bulan ?>"
                    onchange="document.getElementById('formBulan').submit();"
                    class="px-2 py-1 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-gray-700 bg-gray-50 cursor-pointer">
            </form>
        </div>

        <div class="space-y-3 flex-1">
            <?php if ($aktivitas->num_rows > 0): ?>
                <?php while ($row = $aktivitas->fetch_assoc()): ?>
                    <div class="flex items-center p-3 hover:bg-gray-50 rounded-lg border border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-3">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars($row['nama_pt']) ?></p>
                            <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($row['nama_lokasi']) ?></p>
                        </div>
                        <div class="ml-2">
                            <?php
                            $warna = 'bg-gray-100 text-gray-800';
                            if ($row['status_penyewaan'] == 'Aktif')
                                $warna = 'bg-green-100 text-green-800';
                            elseif ($row['status_penyewaan'] == 'Pending')
                                $warna = 'bg-orange-100 text-orange-800';
                            ?>
                            <span class="px-2 py-1 rounded text-xs font-medium <?= $warna ?>">
                                <?= $row['status_penyewaan'] ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-8">
                    <p class="text-sm text-gray-500">Belum ada aktivitas pada bulan ini.</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_halaman > 1): ?>
            <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100">
                <?php if ($halaman > 1): ?>
                    <a href="?bulan=<?= $bulan ?>&page=<?= $halaman - 1 ?>"
                        class="px-3 py-1 text-sm border border-gray-200 rounded hover:bg-gray-50 text-gray-600">← Prev</a>
                <?php else: ?>
                    <span class="px-3 py-1 text-sm border border-gray-200 rounded text-gray-300 pointer-events-none">←
                        Prev</span>
                <?php endif; ?>

                <span class="text-sm text-gray-500">Hal <?= $halaman ?> / <?= $total_halaman ?></span>

                <?php if ($halaman < $total_halaman): ?>
                    <a href="?bulan=<?= $bulan ?>&page=<?= $halaman + 1 ?>"
                        class="px-3 py-1 text-sm border border-gray-200 rounded hover:bg-gray-50 text-gray-600">Next →</a>
                <?php else: ?>
                    <span class="px-3 py-1 text-sm border border-gray-200 rounded text-gray-300 pointer-events-none">Next
                        →</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    var ctx = document.getElementById('omsetChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Omset (Rp)',
                data: <?= json_encode($data_omset) ?>,
                backgroundColor: 'rgba(37, 99, 235, 0.7)',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true },
                x: { grid: { display: false } }
            }
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>