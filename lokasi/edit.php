<?php
require_once '../includes/header.php';
$menu_aktif = 'lokasi';
require_once '../includes/sidebar.php';

$id = $_GET['id'];
$perintah_ambil = $conn->prepare("SELECT * FROM lokasi WHERE id = ?");
$perintah_ambil->bind_param("i", $id);
$perintah_ambil->execute();
$data = $perintah_ambil->get_result()->fetch_assoc();

$perintah_jenis = $conn->query("SELECT id, nama_jenis FROM jenis_iklan WHERE status_aktif = 'aktif'");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lokasi = $_POST['nama_lokasi'];
    $alamat = $_POST['alamat'];
    $id_jenis = $_POST['id_jenis'];
    $ukuran = $_POST['ukuran'];
    $harga = $_POST['harga_per_hari'];
    $status = $_POST['status_lokasi'];
    $nama_foto = $data['foto_lokasi'];

    $status_lama = $data['status_lokasi'];
    $status_berubah = ($status != $status_lama);

    if ($status_berubah && ($status == 'tersedia' || $status == 'maintenance')) {
        $query_cek_penyewaan = $conn->prepare("SELECT COUNT(id) as jumlah FROM penyewaan WHERE id_lokasi = ? AND status_penyewaan IN ('Pending', 'Aktif')");
        $query_cek_penyewaan->bind_param("i", $id);
        $query_cek_penyewaan->execute();
        $jumlah_aktif = $query_cek_penyewaan->get_result()->fetch_assoc()['jumlah'];

        if ($jumlah_aktif > 0) {
            $pesan_error = "Status lokasi tidak dapat diubah. Masih ada $jumlah_aktif penyewaan yang sedang Pending atau Aktif di lokasi ini.";
            echo "<script>alert('$pesan_error'); window.history.back();</script>";
            exit;
        }
    }

    if (!empty($_FILES['foto_lokasi']['name'])) {
        $ekstensi = pathinfo($_FILES['foto_lokasi']['name'], PATHINFO_EXTENSION);
        $nama_foto = uniqid() . '.' . $ekstensi;
        move_uploaded_file($_FILES['foto_lokasi']['tmp_name'], '../uploads/lokasi/' . $nama_foto);
    }

    $perintah_ubah = $conn->prepare("UPDATE lokasi SET nama_lokasi=?, alamat=?, id_jenis=?, ukuran=?, harga_per_hari=?, status_lokasi=?, foto_lokasi=? WHERE id=?");
    $perintah_ubah->bind_param("ssisissi", $nama_lokasi, $alamat, $id_jenis, $ukuran, $harga, $status, $nama_foto, $id);
    $perintah_ubah->execute();

    echo "<script>window.location.href='index.php'</script>";
    exit;
}
?>

<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-800">Edit Lokasi Baliho</h2>
        <a href="index.php" class="text-gray-500 hover:text-gray-700 transition-colors">Tutup</a>
    </div>

    <div class="p-6">
        <form id="formEditLokasi" action="" method="POST" enctype="multipart/form-data" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Lokasi</label>
                <input type="text" value="<?= $data['kode_lokasi'] ?>" disabled
                    class="w-full px-4 py-2 border border-gray-200 bg-gray-50 rounded-lg text-gray-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi</label>
                <input type="text" name="nama_lokasi" required value="<?= $data['nama_lokasi'] ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                <textarea name="alamat" required rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"><?= $data['alamat'] ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Media</label>
                    <select name="id_jenis" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white">
                        <?php while ($j = $perintah_jenis->fetch_assoc()): ?>
                            <option value="<?= $j['id'] ?>" <?php if ($data['id_jenis'] == $j['id'])
                                echo 'selected'; ?>>
                                <?= $j['nama_jenis'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran (Opsional)</label>
                    <input type="text" name="ukuran" value="<?= $data['ukuran'] ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Per Hari (Rp)</label>
                    <input type="number" name="harga_per_hari" required min="0" value="<?= $data['harga_per_hari'] ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Lokasi</label>
                    <select name="status_lokasi" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white">
                        <option value="tersedia" <?php if ($data['status_lokasi'] == 'tersedia')
                            echo 'selected'; ?>>
                            Tersedia</option>
                        <option value="digunakan" <?php if ($data['status_lokasi'] == 'digunakan')
                            echo 'selected'; ?>>
                            Digunakan</option>
                        <option value="maintenance" <?php if ($data['status_lokasi'] == 'maintenance')
                            echo 'selected'; ?>>Maintenance</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Foto Lokasi</label>
                <?php if ($data['foto_lokasi']): ?>
                    <div class="mb-2">
                        <img src="<?= BASE_URL ?>/uploads/lokasi/<?= $data['foto_lokasi'] ?>" alt="Foto"
                            class="h-24 w-auto rounded border border-gray-200">
                    </div>
                <?php endif; ?>
                <input type="file" name="foto_lokasi" accept="image/jpeg, image/png"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak diubah</p>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="index.php" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg">Batal</a>
                <button type="button" onclick="bukaPopup('popupKonfirmasiEdit')"
                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Perbarui Data
                </button>
            </div>
        </form>
    </div>
</div>

<div id="popupKonfirmasiEdit" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center animate-fade-in">
        <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="help-circle" class="w-8 h-8"></i>
        </div>
        
        <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Simpan</h3>
        <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin data lokasi yang diinputkan sudah sesuai?</p>
        
        <div class="flex gap-3 justify-center">
            <button type="button" onclick="tutupPopup('popupKonfirmasiEdit')" 
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                Batal
            </button>
            <button type="button" onclick="submitFormNyata('formEditLokasi')" 
                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                Ya, Simpan
            </button>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>