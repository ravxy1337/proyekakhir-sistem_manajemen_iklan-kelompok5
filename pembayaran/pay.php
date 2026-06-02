<?php
require_once '../includes/header.php';
$menu_aktif = 'pembayaran';
require_once '../includes/sidebar.php';

$id_penyewaan = isset($_GET['id_penyewaan']) ? (int) $_GET['id_penyewaan'] : 0;

$ambil_data = $conn->prepare("SELECT pb.*, p.no_invoice, p.nama_pt, p.nama_pic, p.status_penyewaan 
                        FROM pembayaran pb 
                        JOIN penyewaan p ON pb.id_penyewaan = p.id 
                        WHERE p.id = ?");
$ambil_data->bind_param("i", $id_penyewaan);
$ambil_data->execute();
$detail = $ambil_data->get_result()->fetch_assoc();

if (!$detail) {
    $_SESSION['error'] = "Data pembayaran tidak ditemukan.";
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$id_pembayaran = $detail['id'];

$ambil_riwayat = $conn->prepare("SELECT * FROM riwayat_pembayaran WHERE id_pembayaran = ? ORDER BY id DESC");
$ambil_riwayat->bind_param("i", $id_pembayaran);
$ambil_riwayat->execute();
$list_riwayat = $ambil_riwayat->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $detail['status_pembayaran'] != 'Lunas' && $detail['status_penyewaan'] != 'Dibatalkan') {
    $nominal = str_replace(['Rp', '.', ',', ' '], '', $_POST['nominal']);
    $metode = $_POST['metode'];

    $valid_methods = ['Transfer', 'Cash', 'QRIS'];
    if (empty($nominal) || !is_numeric($nominal) || $nominal <= 0) {
        $_SESSION['error'] = "Nominal pembayaran tidak valid.";
    } elseif ($nominal > $detail['sisa_tagihan']) {
        $_SESSION['error'] = "Nominal pembayaran melebihi sisa tagihan.";
    } elseif ($detail['status_penyewaan'] == 'Pending' && $nominal < ($detail['total_tagihan'] * 0.5)) {
        $_SESSION['error'] = "Pembayaran awal (DP) minimal 50% dari total tagihan.";
    } elseif (!in_array($metode, $valid_methods)) {
        $_SESSION['error'] = "Metode pembayaran tidak valid.";
    } else {
        $upload_ok = true;
        $bukti = null;
        if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $max_size = 5 * 1024 * 1024;

            if (!in_array($_FILES['bukti_pembayaran']['type'], $allowed_types)) {
                $_SESSION['error'] = "Format bukti pembayaran hanya JPG/PNG.";
                $upload_ok = false;
            } elseif ($_FILES['bukti_pembayaran']['size'] > $max_size) {
                $_SESSION['error'] = "Ukuran bukti maksimal 5MB.";
                $upload_ok = false;
            } else {
                $ext = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
                $bukti = uniqid('tf_') . '.' . $ext;
                move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], '../uploads/bukti/' . $bukti);
            }
        } else {
            $_SESSION['error'] = "Bukti pembayaran wajib diunggah dan tidak boleh error (ukuran file mungkin terlalu besar).";
            $upload_ok = false;
        }
        if ($upload_ok) {
            $stmt_hist = $conn->prepare("INSERT INTO riwayat_pembayaran (id_pembayaran, nominal, metode, bukti_pembayaran) VALUES (?, ?, ?, ?)");
            $stmt_hist->bind_param("idss", $id_pembayaran, $nominal, $metode, $bukti);
            if($stmt_hist->execute()) {
                // Update 
                $new_dibayar = $detail['dibayar'] + $nominal;
                $new_sisa = $detail['total_tagihan'] - $new_dibayar;
                $new_status = ($new_sisa <= 0) ? 'Lunas' : 'DP';

                $stmt_upd = $conn->prepare("UPDATE pembayaran SET dibayar = ?, sisa_tagihan = ?, status_pembayaran = ? WHERE id = ?");
                $stmt_upd->bind_param("ddsi", $new_dibayar, $new_sisa, $new_status, $id_pembayaran);
                if ($stmt_upd->execute()) {
                    // ubah status sewa
                    if ($detail['status_penyewaan'] == 'Pending') {
                        $batas_50_persen = $detail['total_tagihan'] * 0.5;
                        if ($new_dibayar >= $batas_50_persen) {
                            $stmt_sewa = $conn->prepare("UPDATE penyewaan SET status_penyewaan = 'Aktif' WHERE id = ?");
                            $stmt_sewa->bind_param("i", $id_penyewaan);
                            $stmt_sewa->execute();
                        }
                    }
                    $_SESSION['success'] = "Pembayaran berhasil diproses.";
                    echo "<script>window.location.href='pay.php?id_penyewaan=$id_penyewaan';</script>";
                    exit;
            } else {
                $_SESSION['error'] = "Gagal memperbarui data status pembayaran.";
            }
        } else {
            $_SESSION['error'] = "Gagal menyimpan riwayat pembayaran.";
            }
        }
    }
}
?>

<div class="max-w-5xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Proses Pembayaran</h2>
            <p class="text-sm text-gray-500 mt-1">Invoice: <span
                    class="font-bold text-gray-700"><?= htmlspecialchars($detail['no_invoice']) ?></span> -
                <?= htmlspecialchars($detail['nama_pt'] ?: $detail['nama_pic']) ?>
            </p>
        </div>
        <a href="index.php"
            class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 text-center">
            Kembali
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
            <p class="text-sm text-red-700"><?= htmlspecialchars($_SESSION['error']) ?></p>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
            <p class="text-sm text-green-700"><?= htmlspecialchars($_SESSION['success']) ?></p>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-6">

            <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                <h3 class="text-sm font-bold text-gray-800 uppercase mb-4 pb-2 border-b">Ringkasan Tagihan</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Total Tagihan</span>
                        <span class="font-bold text-gray-800">Rp
                            <?= number_format($detail['total_tagihan'], 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Sudah Dibayar</span>
                        <span class="font-bold text-green-600">Rp
                            <?= number_format($detail['dibayar'], 0, ',', '.') ?></span>
                    </div>
                    <div class="pt-3 border-t flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-800">Sisa Tagihan</span>
                        <span class="text-lg font-bold text-red-600">Rp
                            <?= number_format($detail['sisa_tagihan'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>

            <?php if ($detail['status_pembayaran'] != 'Lunas' && $detail['status_penyewaan'] != 'Dibatalkan'): ?>
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-800 uppercase mb-4 pb-2 border-b">Input Pembayaran</h3>
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4" id="formBayar" onsubmit="return validasiPembayaran()">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp) <span
                                    class="text-red-500">*</span></label>
                            <input type="number" name="nominal" id="nominal" required max="<?= $detail['sisa_tagihan'] ?>"
                                min="1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-lg font-bold text-gray-800">
                            <div class="mt-2 flex gap-2">
                                <button type="button" onclick="setNominal(<?= $detail['sisa_tagihan'] ?>)"
                                    class="text-xs px-2.5 py-1 bg-blue-50 text-blue-600 rounded font-medium">Bayar
                                    Lunas</button>
                                <button type="button" onclick="setNominal(<?= $detail['sisa_tagihan'] / 2 ?>)"
                                    class="text-xs px-2.5 py-1 bg-gray-50 text-gray-600 rounded font-medium">Bayar
                                    50%</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metode <span
                                    class="text-red-500">*</span></label>
                            <select name="metode" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white">
                                <option value="Transfer">Transfer Bank</option>
                                <option value="Cash">Cash / Tunai</option>
                                <option value="QRIS">QRIS</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bukti Pembayaran <span
                                    class="text-red-500">*</span></label>
                            <input type="file" name="bukti_pembayaran" required accept=".jpg,.jpeg,.png"
                                class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm bg-white">
                        </div>
                        <button type="submit"
                            class="w-full py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            Proses Pembayaran
                        </button>
                    </form>
                </div>
            <?php elseif ($detail['status_pembayaran'] == 'Lunas'): ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                    <h3 class="text-green-800 font-bold text-lg">Tagihan Lunas</h3>
                    <p class="text-green-600 text-sm mt-1">Seluruh tagihan telah dibayar.</p>
                </div>
            <?php else: ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                    <h3 class="text-red-800 font-bold text-lg">Pesanan Dibatalkan</h3>
                    <p class="text-red-600 text-sm mt-1">Pembayaran tidak dapat diproses.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-2 bg-white rounded-lg shadow border border-gray-200 p-6">
            <h3 class="text-sm font-bold text-gray-800 uppercase mb-4 pb-2 border-b">Riwayat Pembayaran</h3>

            <?php if ($list_riwayat->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while ($baris = $list_riwayat->fetch_assoc()): ?>
                        <div
                            class="p-4 bg-gray-50 border border-gray-200 rounded-lg flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <p class="text-base font-bold text-gray-800">Rp
                                    <?= number_format($baris['nominal'], 0, ',', '.') ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Metode: <?= htmlspecialchars($baris['metode']) ?> | Waktu:
                                    <?= date('d M Y H:i', strtotime($baris['tgl_bayar'])) ?>
                                </p>
                            </div>
                            <div>
                                <a href="<?= BASE_URL ?>/uploads/bukti/<?= $baris['bukti_pembayaran'] ?>" target="_blank"
                                    class="inline-flex items-center text-xs font-medium text-blue-600 bg-white border border-gray-300 px-3 py-1.5 rounded hover:bg-gray-50">
                                    <i data-lucide="image" class="w-3.5 h-3.5 mr-1 text-gray-400"></i> Bukti Transfer
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-500 text-center py-8">Belum ada riwayat pembayaran.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const statusSewa = "<?= $detail['status_penyewaan'] ?>";
    const totalTagihan = <?= $detail['total_tagihan'] ?>;
</script>
<script src="/iklanku/assets/js/pembayaran.js"></script>

<?php require_once '../includes/footer.php'; ?>