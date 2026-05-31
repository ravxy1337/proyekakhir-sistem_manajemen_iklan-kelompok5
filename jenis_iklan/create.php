<?php
require_once '../includes/header.php';
$menu_aktif = 'jenis_iklan';
require_once '../includes/sidebar.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama_jenis'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];
    $status = $_POST['status_aktif'];

    if ($nama == '') {
        $_SESSION['error'] = "Nama jenis iklan wajib diisi.";
    } else {
        $perintah_cek = $conn->prepare("SELECT id FROM jenis_iklan WHERE nama_jenis = ?");
        $perintah_cek->bind_param("s", $nama);
        $perintah_cek->execute();
        $hasil_cek = $perintah_cek->get_result();

        if ($hasil_cek->num_rows > 0) {
            $_SESSION['error'] = "Nama jenis iklan sudah ada.";
        } else {
            $perintah_simpan = $conn->prepare("INSERT INTO jenis_iklan (nama_jenis, kategori, deskripsi, status_aktif) VALUES (?, ?, ?, ?, ?)");
            $perintah_simpan->bind_param("ssiss", $nama, $kategori, $deskripsi, $status);

            if ($perintah_simpan->execute()) {
                $_SESSION['success'] = "Jenis iklan berhasil ditambahkan.";
                echo "<script>window.location.href='index.php';</script>";
                exit;
            } else {
                $_SESSION['error'] = "Gagal menyimpan data.";
            }
        }
    }
}
?>

<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-800">Tambah Jenis Iklan</h2>
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Misal: Billboard Frontlight">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Media <span
                                class="text-red-500">*</span></label>
                        <select name="kategori" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="Cetak">Cetak</option>
                            <option value="Digital">Digital</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span
                                class="text-red-500">*</span></label>
                        <select name="status_aktif" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 resize-none"
                        placeholder="Tuliskan detail jenis iklan..."></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="index.php"
                    class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">Batal</a>
                <button type="submit"
                    class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors flex items-center">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>