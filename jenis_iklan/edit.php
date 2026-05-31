<?php
require_once '../includes/header.php';
$menu_aktif = 'jenis_iklan';
require_once '../includes/sidebar.php';

$id_iklan = $_GET['id'] ?? 0;

$perintah_ambil = $conn->prepare("SELECT * FROM jenis_iklan WHERE id = ?");
$perintah_ambil->bind_param("i", $id_iklan);
$perintah_ambil->execute();
$hasil_ambil = $perintah_ambil->get_result();
$data_iklan = $hasil_ambil->fetch_assoc();

if (!$data_iklan) {
    $_SESSION['error'] = "Data tidak ditemukan.";
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama_jenis'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $status = $_POST['status_aktif'];
    $harga = 0;

    if ($nama == '') {
        $_SESSION['error'] = "Nama jenis iklan wajib diisi.";
    } else {
        $perintah_cek = $conn->prepare("SELECT id FROM jenis_iklan WHERE nama_jenis = ? AND id != ?");
        $perintah_cek->bind_param("si", $nama, $id_iklan);
        $perintah_cek->execute();
        $hasil_cek = $perintah_cek->get_result();

        if ($hasil_cek->num_rows > 0) {
            $_SESSION['error'] = "Nama jenis iklan sudah ada.";
        } else {
            $perintah_ubah = $conn->prepare("UPDATE jenis_iklan SET nama_jenis=?, kategori=?, harga_per_hari=?, deskripsi=?, status_aktif=? WHERE id=?");
            $perintah_ubah->bind_param("ssissi", $nama, $kategori, $harga, $deskripsi, $status, $id_iklan);

            if ($perintah_ubah->execute()) {
                $_SESSION['success'] = "Jenis iklan berhasil diperbarui.";
                echo "<script>window.location.href='index.php';</script>";
                exit;
            } else {
                $_SESSION['error'] = "Gagal memperbarui data.";
            }
        }
    }
}
?>

<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-800">Edit Jenis Iklan</h2>
        <a href="index.php" class="text-gray-500 hover:text-gray-700 transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </a>
    </div>

    <div class="p-6">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-md">
                <p class="text-sm text-red-700"><?= htmlspecialchars($_SESSION['error']) ?></p>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Iklan <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="nama_jenis" required
                        value="<?= htmlspecialchars($data_iklan['nama_jenis']) ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Media <span
                                class="text-red-500">*</span></label>
                        <select name="kategori" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="Cetak" <?= $data_iklan['kategori'] == 'Cetak' ? 'selected' : '' ?>>Cetak
                            </option>
                            <option value="Digital" <?= $data_iklan['kategori'] == 'Digital' ? 'selected' : '' ?>>Digital
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span
                                class="text-red-500">*</span></label>
                        <select name="status_aktif" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="aktif" <?= $data_iklan['status_aktif'] == 'aktif' ? 'selected' : '' ?>>Aktif
                            </option>
                            <option value="nonaktif" <?= $data_iklan['status_aktif'] == 'nonaktif' ? 'selected' : '' ?>>
                                Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 resize-none"><?= htmlspecialchars($data_iklan['deskripsi'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="index.php"
                    class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">Batal</a>
                <button type="submit"
                    class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors flex items-center">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Perbarui Data
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>