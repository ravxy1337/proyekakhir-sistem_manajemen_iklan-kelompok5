<?php
require_once '../includes/header.php';
$menu_aktif = 'lokasi';
require_once '../includes/sidebar.php';

$data_jenis = $conn->query("SELECT id, nama_jenis FROM jenis_iklan WHERE status_aktif = 'aktif'");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lokasi = $_POST['nama_lokasi'];
    $alamat = $_POST['alamat'];
    $id_jenis = $_POST['id_jenis'];
    $ukuran = $_POST['ukuran'];
    $harga = $_POST['harga_per_hari'];
    $status = $_POST['status_lokasi'];

    $data_terakhir = $conn->query("SELECT kode_lokasi FROM lokasi ORDER BY id DESC LIMIT 1");

    if ($data_terakhir->num_rows > 0) {
        $baris_data = $data_terakhir->fetch_assoc();
        $kode_terakhir = $baris_data['kode_lokasi'];
        $angka_baru = (int) substr($kode_terakhir, 4) + 1;
        $kode_otomatis = 'LOC-' . str_pad($angka_baru, 3, '0', STR_PAD_LEFT);
    } else {
        $kode_otomatis = 'LOC-001';
    }

    $foto_baru = '';
    if ($_FILES['foto_lokasi']['name'] != '') {
        $nama_foto = $_FILES['foto_lokasi']['name'];
        $tempat_sementara = $_FILES['foto_lokasi']['tmp_name'];
        $foto_baru = time() . '_' . $nama_foto;
        move_uploaded_file($tempat_sementara, '../uploads/lokasi/' . $foto_baru);
    }

    $perintah_simpan = "INSERT INTO lokasi (kode_lokasi, nama_lokasi, alamat, id_jenis, ukuran, harga_per_hari, status_lokasi, foto_lokasi) 
                        VALUES ('$kode_otomatis', '$nama_lokasi', '$alamat', '$id_jenis', '$ukuran', '$harga', '$status', '$foto_baru')";

    if ($conn->query($perintah_simpan)) {
        $_SESSION['success'] = "Lokasi berhasil ditambahkan.";
        echo "<script>window.location.href='index.php';</script>";
        exit;
    } else {
        $_SESSION['error'] = "Gagal menyimpan data lokasi.";
    }
}
?>

<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-800">Tambah Lokasi Baru</h2>
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

        <form id="formTambahLokasi" action="" method="POST" enctype="multipart/form-data">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="nama_lokasi" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Misal: Perempatan Sudirman">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap <span
                            class="text-red-500">*</span></label>
                    <textarea name="alamat" required rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 resize-none"
                        placeholder="Tuliskan alamat lengkap..."></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Media <span
                                class="text-red-500">*</span></label>
                        <select name="id_jenis" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <?php while ($jenis = $data_jenis->fetch_assoc()): ?>
                                <option value="<?= $jenis['id'] ?>"><?= htmlspecialchars($jenis['nama_jenis']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran (Opsional)</label>
                        <input type="text" name="ukuran"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Misal: 4x6 m">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Per Hari (Rp) <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="harga_per_hari" required min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Lokasi <span
                                class="text-red-500">*</span></label>
                        <select name="status_lokasi" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="tersedia">Tersedia</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto Lokasi</label>
                    <input type="file" name="foto_lokasi" accept=".jpg,.jpeg,.png"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG/PNG, Maks: 5MB</p>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="index.php"
                    class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">Batal</a>
                <button type="button" onclick="bukaPopup('popupKonfirmasiTambah')"
                    class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors flex items-center">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<div id="popupKonfirmasiTambah" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center animate-fade-in">
        <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="help-circle" class="w-8 h-8"></i>
        </div>
        
        <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Simpan</h3>
        <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin data lokasi yang diinputkan sudah sesuai?</p>
        
        <div class="flex gap-3 justify-center">
            <button type="button" onclick="tutupPopup('popupKonfirmasiTambah')" 
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                Batal
            </button>
            <button type="button" onclick="submitFormNyata('formTambahLokasi')" 
                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                Ya, Simpan
            </button>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>