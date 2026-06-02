<?php
require_once 'includes/header.php';
$menu_aktif = 'dashboard';
require_once 'includes/sidebar.php';

$ambil_order = $conn->query("SELECT COUNT(id) as total FROM penyewaan");
$data_order = $ambil_order->fetch_assoc();
$total_order = $data_order['total'];

$ambil_omset = $conn->query("SELECT SUM(total_harga) as total FROM penyewaan");
$data_omset_raw = $ambil_omset->fetch_assoc();
$total_omset = $data_omset_raw['total'] ?? 0;

$ambil_aktif = $conn->query("SELECT COUNT(id) as total FROM penyewaan WHERE status_penyewaan = 'Aktif'");
$data_aktif = $ambil_aktif->fetch_assoc();
$total_aktif = $data_aktif['total'];

$ambil_selesai = $conn->query("SELECT COUNT(id) as total FROM penyewaan WHERE status_penyewaan = 'Selesai'");
$data_selesai = $ambil_selesai->fetch_assoc();
$total_selesai = $data_selesai['total'];

$ambil_pending = $conn->query("SELECT COUNT(pb.id) as total FROM pembayaran pb JOIN penyewaan p ON pb.id_penyewaan = p.id WHERE pb.status_pembayaran != 'Lunas' AND p.status_penyewaan != 'Dibatalkan'");
$data_pending = $ambil_pending->fetch_assoc();
$total_pending = $data_pending['total'];

$ambil_lokasi = $conn->query("SELECT COUNT(id) as total FROM lokasi WHERE status_lokasi = 'tersedia'");
$data_lokasi = $ambil_lokasi->fetch_assoc();
$total_lokasi = $data_lokasi['total'];

$ambil_chart = $conn->query("SELECT DATE_FORMAT(tgl_mulai, '%Y-%m') as bulan, SUM(total_harga) as omset FROM penyewaan GROUP BY bulan ORDER BY bulan ASC LIMIT 12");
$labels = [];
$data_omset = [];
while ($row = $ambil_chart->fetch_assoc()) {
    $labels[] = date('F Y', strtotime($row['bulan'] . '-01'));
    $data_omset[] = $row['omset'];
}

$ambil_aktivitas = $conn->query("SELECT p.*, l.nama_lokasi FROM penyewaan p JOIN lokasi l ON p.id_lokasi = l.id ORDER BY p.id DESC LIMIT 5");
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
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Grafik Pemasukan Bulanan</h3>
        <div class="relative w-full" style="min-height:200px;"><canvas id="omsetChart"></canvas></div>
    </div>
    
    <div class="bg-white rounded-lg shadow border border-gray-200 p-5">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h3>
        <div class="space-y-3">
            <?php if ($ambil_aktivitas->num_rows > 0): ?>
                <?php while ($row = $ambil_aktivitas->fetch_assoc()): ?>
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
                            if ($row['status_penyewaan'] == 'Aktif') $warna = 'bg-green-100 text-green-800';
                            elseif ($row['status_penyewaan'] == 'Pending') $warna = 'bg-orange-100 text-orange-800';
                            ?>
                            <span class="px-2 py-1 rounded text-xs font-medium <?= $warna ?>">
                                <?= $row['status_penyewaan'] ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-sm text-gray-500 text-center py-4">Belum ada aktivitas penyewaan.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
var ctx = document.getElementById('omsetChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Omset (Rp)',
            data: <?= json_encode($data_omset) ?>,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
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
