<?php
require_once '../includes/header.php';
$menu_aktif = 'laporan';
require_once '../includes/sidebar.php';

$tanggal_awal = $_GET['start_date'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['end_date'] ?? date('Y-m-t');

$perintah_sql = "SELECT p.no_invoice, p.nama_pt, p.nama_pic, p.tgl_mulai, p.tgl_selesai, p.total_harga, p.status_penyewaan, l.nama_lokasi, j.nama_jenis, pb.dibayar, pb.sisa_tagihan, pb.status_pembayaran
      FROM penyewaan p
      JOIN lokasi l ON p.id_lokasi = l.id
      JOIN jenis_iklan j ON p.id_jenis = j.id
      LEFT JOIN pembayaran pb ON p.id = pb.id_penyewaan
      WHERE DATE(p.created_at) BETWEEN ? AND ?
      ORDER BY p.id DESC";

$perintah_ambil = $conn->prepare($perintah_sql);
$perintah_ambil->bind_param("ss", $tanggal_awal, $tanggal_akhir);
$perintah_ambil->execute();
$hasil_ambil = $perintah_ambil->get_result();

$total_pendapatan = 0;
$total_dibayar = 0;
$total_piutang = 0;
$kumpulan_data = [];

while ($baris_data = $hasil_ambil->fetch_assoc()) {
    $total_pendapatan += $baris_data['total_harga'];
    $total_dibayar += $baris_data['dibayar'];
    $total_piutang += $baris_data['sisa_tagihan'];
    $kumpulan_data[] = $baris_data;
}
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden mb-6">
    <div class="p-4 md:p-6 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Laporan Rekapitulasi</h2>
            <p class="text-sm text-gray-500 mt-1">Laporan penyewaan dan omzet pada periode tertentu</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <button onclick="eksporKeExcel()"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-2"></i> Export Excel
            </button>
            <button onclick="eksporKePDF()"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center">
                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export PDF
            </button>
        </div>
    </div>

    <div class="p-4 md:p-6 bg-gray-50 border-b border-gray-200">
        <form action="" method="GET" class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-end">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($tanggal_awal) ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($tanggal_akhir) ?>" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            </div>
            <div>
                <button type="submit"
                    class="w-full bg-gray-800 text-white px-5 py-2.5 rounded-lg hover:bg-gray-900 text-sm font-medium">
                    Tampilkan
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 md:p-6 bg-white border-b border-gray-200">
        <div class="p-4 rounded-lg bg-blue-50 border border-blue-100">
            <p class="text-xs font-bold text-blue-600 uppercase mb-1">Total Omzet</p>
            <h3 class="text-xl font-bold text-gray-800">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h3>
        </div>
        <div class="p-4 rounded-lg bg-green-50 border border-green-100">
            <p class="text-xs font-bold text-green-600 uppercase mb-1">Total Penerimaan</p>
            <h3 class="text-xl font-bold text-gray-800">Rp <?= number_format($total_dibayar, 0, ',', '.') ?></h3>
        </div>
        <div class="p-4 rounded-lg bg-red-50 border border-red-100">
            <p class="text-xs font-bold text-red-600 uppercase mb-1">Total Piutang</p>
            <h3 class="text-xl font-bold text-gray-800">Rp <?= number_format($total_piutang, 0, ',', '.') ?></h3>
        </div>
    </div>

    <div id="wadahLaporan" class="overflow-x-auto p-4 md:p-6 bg-white">
        <div class="hidden" id="kopLaporan">
            <h2 style="font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 5px;">LAPORAN REKAPITULASI
                PENYEWAAN IKLAN</h2>
            <p style="font-size: 12px; text-align: center; color: #555; margin-bottom: 20px;">Periode:
                <?= date('d M Y', strtotime($tanggal_awal)) ?> s/d <?= date('d M Y', strtotime($tanggal_akhir)) ?>
            </p>
        </div>
        <table id="tabelLaporan" class="w-full text-left border-collapse" style="font-size: 12px;">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="px-4 py-2 border border-gray-200 font-semibold">No</th>
                    <th class="px-4 py-2 border border-gray-200 font-semibold">Invoice</th>
                    <th class="px-4 py-2 border border-gray-200 font-semibold">Pelanggan</th>
                    <th class="px-4 py-2 border border-gray-200 font-semibold">Layanan & Lokasi</th>
                    <th class="px-4 py-2 border border-gray-200 font-semibold text-right">Omzet (Rp)</th>
                    <th class="px-4 py-2 border border-gray-200 font-semibold text-right">Dibayar (Rp)</th>
                    <th class="px-4 py-2 border border-gray-200 font-semibold text-right">Piutang (Rp)</th>
                    <th class="px-4 py-2 border border-gray-200 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($kumpulan_data) > 0): ?>
                    <?php $no = 1;
                    foreach ($kumpulan_data as $baris): ?>
                        <tr>
                            <td class="px-4 py-2 border border-gray-200 text-center"><?= $no++ ?></td>
                            <td class="px-4 py-2 border border-gray-200"><?= $baris['no_invoice'] ?></td>
                            <td class="px-4 py-2 border border-gray-200"><?= $baris['nama_pt'] ?: $baris['nama_pic'] ?></td>
                            <td class="px-4 py-2 border border-gray-200"><?= $baris['nama_jenis'] ?> -
                                <?= $baris['nama_lokasi'] ?>
                            </td>
                            <td class="px-4 py-2 border border-gray-200 text-right">
                                <?= number_format($baris['total_harga'], 0, ',', '.') ?>
                            </td>
                            <td class="px-4 py-2 border border-gray-200 text-right">
                                <?= number_format($baris['dibayar'], 0, ',', '.') ?>
                            </td>
                            <td class="px-4 py-2 border border-gray-200 text-right">
                                <?= number_format($baris['sisa_tagihan'], 0, ',', '.') ?>
                            </td>
                            <td class="px-4 py-2 border border-gray-200"><?= $baris['status_pembayaran'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-4 py-8 border border-gray-200 text-center text-gray-500">Tidak ada data
                            transaksi pada periode ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (count($kumpulan_data) > 0): ?>
                <tfoot>
                    <tr class="bg-gray-50 font-bold">
                        <td colspan="4" class="px-4 py-2 border border-gray-200 text-right">TOTAL KESELURUHAN</td>
                        <td class="px-4 py-2 border border-gray-200 text-right">
                            <?= number_format($total_pendapatan, 0, ',', '.') ?>
                        </td>
                        <td class="px-4 py-2 border border-gray-200 text-right">
                            <?= number_format($total_dibayar, 0, ',', '.') ?>
                        </td>
                        <td class="px-4 py-2 border border-gray-200 text-right">
                            <?= number_format($total_piutang, 0, ',', '.') ?>
                        </td>
                        <td class="px-4 py-2 border border-gray-200"></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
    function eksporKeExcel() {
        var tabel = document.getElementById("tabelLaporan");
        var bukuExcel = XLSX.utils.table_to_book(tabel, { sheet: "Laporan" });
        XLSX.writeFile(bukuExcel, "Laporan_Penyewaan_<?= $tanggal_awal ?>_<?= $tanggal_akhir ?>.xlsx");
    }

    function eksporKePDF() {
        window.print();
    }
</script>

<?php require_once '../includes/footer.php'; ?>