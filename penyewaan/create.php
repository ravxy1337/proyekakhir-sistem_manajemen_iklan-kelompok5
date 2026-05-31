<?php
require_once '../includes/header.php';
$menu_aktif = 'penyewaan';
require_once '../includes/sidebar.php';

// Ambil semua jenis iklan yang masih aktif untuk pilihan dropdown
$query_jenis = $conn->query("SELECT id, nama_jenis, harga_per_hari FROM jenis_iklan WHERE status_aktif = 'aktif'");
$daftar_jenis = [];
while ($baris = $query_jenis->fetch_assoc()) {
    $daftar_jenis[] = $baris;
}

// Ambil semua lokasi yang tidak sedang maintenance untuk pilihan dropdown
$query_lokasi = $conn->query("SELECT id, id_jenis, nama_lokasi, kode_lokasi, harga_per_hari FROM lokasi WHERE status_lokasi != 'maintenance'");
$daftar_lokasi = [];
while ($baris = $query_lokasi->fetch_assoc()) {
    $daftar_lokasi[] = $baris;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Ambil data pelanggan dari form
    $nama_pic = trim($_POST['nama_pic']);
    $nama_pt = trim($_POST['nama_pt']);
    $no_hp = trim($_POST['no_hp']);
    $email = trim($_POST['email']);
    $alamat = trim($_POST['alamat']);

    // Ambil data iklan dari form
    $id_jenis = (int) $_POST['id_jenis'];
    $id_lokasi = (int) $_POST['id_lokasi'];
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $total_hari = (int) $_POST['total_hari'];
    $total_harga = str_replace(['Rp', '.', ',', ' '], '', $_POST['total_harga']);

    // --- Validasi data satu per satu ---
    if (empty($nama_pic)) {
        $_SESSION['error'] = "Nama PIC wajib diisi.";
    } elseif (strlen($nama_pic) < 3) {
        $_SESSION['error'] = "Nama PIC minimal 3 karakter.";
    } elseif (empty($no_hp)) {
        $_SESSION['error'] = "Nomor HP wajib diisi.";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $no_hp)) {
        $_SESSION['error'] = "Nomor HP harus 10-15 digit angka.";
    } elseif (empty($email)) {
        $_SESSION['error'] = "Email wajib diisi.";
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $_SESSION['error'] = "Email wajib menggunakan domain @gmail.com.";
    } elseif (empty($alamat)) {
        $_SESSION['error'] = "Alamat wajib diisi.";
    } elseif (empty($id_jenis) || $id_jenis <= 0) {
        $_SESSION['error'] = "Jenis iklan wajib dipilih.";
    } elseif (empty($id_lokasi) || $id_lokasi <= 0) {
        $_SESSION['error'] = "Lokasi wajib dipilih.";
    } elseif (empty($tgl_mulai) || empty($tgl_selesai)) {
        $_SESSION['error'] = "Tanggal mulai dan selesai wajib diisi.";
    } elseif ($tgl_selesai < $tgl_mulai) {
        $_SESSION['error'] = "Tanggal selesai tidak boleh kurang dari tanggal mulai.";
    } elseif ($total_hari <= 0) {
        $_SESSION['error'] = "Durasi sewa tidak valid.";
    } elseif (!is_numeric($total_harga) || $total_harga <= 0) {
        $_SESSION['error'] = "Total harga tidak valid.";
    } else {

        // Cek apakah lokasi sudah dipakai di tanggal yang sama
        $query_cek_lokasi = $conn->prepare("SELECT id FROM penyewaan WHERE id_lokasi = ? AND status_penyewaan IN ('Pending', 'Aktif') AND tgl_mulai <= ? AND tgl_selesai >= ?");
        $query_cek_lokasi->bind_param("iss", $id_lokasi, $tgl_selesai, $tgl_mulai);
        $query_cek_lokasi->execute();

        if ($query_cek_lokasi->get_result()->num_rows > 0) {
            $_SESSION['error'] = "Lokasi sudah digunakan pada tanggal tersebut. Silakan pilih tanggal atau lokasi lain.";
        } else {

            // --- Proses upload file media iklan ---
            $upload_berhasil = true;
            $nama_file_media = null;

            if (isset($_FILES['file_media']) && $_FILES['file_media']['error'] != 4) {
                $tipe_file_diizinkan = ['image/jpeg', 'image/png', 'image/jpg', 'video/mp4'];
                $ukuran_maksimal = 10 * 1024 * 1024; // 10MB

                if (!in_array($_FILES['file_media']['type'], $tipe_file_diizinkan)) {
                    $_SESSION['error'] = "Format media hanya JPG, PNG, atau MP4.";
                    $upload_berhasil = false;
                } elseif ($_FILES['file_media']['size'] > $ukuran_maksimal) {
                    $_SESSION['error'] = "Ukuran media maksimal 10MB.";
                    $upload_berhasil = false;
                } else {
                    // Buat nama file unik agar tidak bentrok dengan file lain
                    $ekstensi_file = pathinfo($_FILES['file_media']['name'], PATHINFO_EXTENSION);
                    $nama_file_media = uniqid('media_') . '.' . $ekstensi_file;
                    move_uploaded_file($_FILES['file_media']['tmp_name'], '../uploads/media/' . $nama_file_media);
                }
            }

            if ($upload_berhasil) {
                // --- Buat nomor invoice otomatis ---
                $tanggal_invoice = date('Ymd');
                $query_hitung_invoice = $conn->query("SELECT COUNT(id) as jumlah FROM penyewaan WHERE DATE(created_at) = CURDATE()");
                $jumlah_hari_ini = $query_hitung_invoice->fetch_assoc()['jumlah'] + 1;
                $no_invoice = 'INV-' . $tanggal_invoice . '-' . str_pad($jumlah_hari_ini, 3, '0', STR_PAD_LEFT);

                // Simpan data penyewaan ke database
                $query_simpan = $conn->prepare("INSERT INTO penyewaan (no_invoice, id_lokasi, id_jenis, nama_pic, nama_pt, no_hp, email, alamat, file_media, tgl_mulai, tgl_selesai, total_hari, total_harga, status_penyewaan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
                $query_simpan->bind_param("siissssssssid", $no_invoice, $id_lokasi, $id_jenis, $nama_pic, $nama_pt, $no_hp, $email, $alamat, $nama_file_media, $tgl_mulai, $tgl_selesai, $total_hari, $total_harga);

                if ($query_simpan->execute()) {
                    $id_penyewaan_baru = $conn->insert_id;

                    // Buat juga data pembayaran awal secara otomatis
                    $query_pembayaran = $conn->prepare("INSERT INTO pembayaran (id_penyewaan, total_tagihan, dibayar, sisa_tagihan, status_pembayaran) VALUES (?, ?, 0, ?, 'Belum Bayar')");
                    $query_pembayaran->bind_param("idd", $id_penyewaan_baru, $total_harga, $total_harga);
                    $query_pembayaran->execute();

                    $_SESSION['success'] = "Penyewaan berhasil dibuat. Invoice: $no_invoice";
                    echo "<script>window.location.href='index.php';</script>";
                    exit;
                } else {
                    $_SESSION['error'] = "Gagal menyimpan transaksi.";
                }
            }
        }
    }
}
?>

<div class="max-w-5xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Buat Penyewaan Baru</h2>
            <p class="text-sm text-gray-500 mt-1">Isi form di bawah untuk membuat transaksi penyewaan iklan</p>
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

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
        <!-- Data Pelanggan -->
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-3 border-b">1. Data Pelanggan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama PIC <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="nama_pic" required minlength="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Nama penanggung jawab">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama PT / Perusahaan</label>
                    <input type="text" name="nama_pt" class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                        placeholder="Opsional">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="no_hp" required pattern="[0-9]{10,15}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="08xxxx (10-15 digit)">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span
                            class="text-red-500">*</span></label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                        placeholder="email@contoh.com">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap <span
                            class="text-red-500">*</span></label>
                    <textarea name="alamat" required rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg resize-none"
                        placeholder="Alamat pelanggan"></textarea>
                </div>
            </div>
        </div>

        <!-- Data Iklan -->
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-3 border-b">2. Data Iklan &amp; Lokasi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Iklan <span
                            class="text-red-500">*</span></label>
                    <select name="id_jenis" id="id_jenis" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white" onchange="filterLokasi()">
                        <option value="">-- Pilih Jenis Iklan --</option>
                        <?php foreach ($daftar_jenis as $jenis): ?>
                            <option value="<?= $jenis['id'] ?>"><?= htmlspecialchars($jenis['nama_jenis']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Baliho <span
                            class="text-red-500">*</span></label>
                    <select name="id_lokasi" id="id_lokasi" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white" disabled
                        onchange="hitungHarga()">
                        <option value="">-- Pilih Lokasi --</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Media Iklan</label>
                    <input type="file" name="file_media" accept=".jpg,.jpeg,.png,.mp4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, MP4. Maksimal 10MB.</p>
                </div>
            </div>
        </div>

        <!-- Jadwal Sewa -->
        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-3 border-b">3. Jadwal &amp; Biaya</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="tgl_mulai" id="tgl_mulai" required min="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg" onchange="hitungHarga()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="tgl_selesai" id="tgl_selesai" required min="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg" onchange="hitungHarga()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (Hari)</label>
                    <input type="text" name="total_hari" id="total_hari" readonly
                        class="w-full px-4 py-2 border border-gray-200 bg-gray-50 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Harga (Rp)</label>
                    <input type="text" name="total_harga" id="total_harga" readonly
                        class="w-full px-4 py-2 border border-gray-200 bg-blue-50 text-blue-800 rounded-lg font-bold">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 font-medium">
                Buat Pesanan Penyewaan
            </button>
        </div>
    </form>
</div>

<script>
    // Data semua lokasi yang diambil dari PHP (dalam format JSON)
    var semuaLokasi = <?= json_encode($daftar_lokasi) ?>;

    // Ambil elemen-elemen form yang akan dipakai
    var pilihanJenis = document.getElementById('id_jenis');
    var pilihanLokasi = document.getElementById('id_lokasi');
    var inputTglMulai = document.getElementById('tgl_mulai');
    var inputTglSelesai = document.getElementById('tgl_selesai');
    var inputTotalHari = document.getElementById('total_hari');
    var inputTotalHarga = document.getElementById('total_harga');

    // Fungsi untuk menyaring lokasi berdasarkan jenis iklan yang dipilih
    function filterLokasi() {
        var idJenisDipilih = pilihanJenis.value;

        // Reset pilihan lokasi
        pilihanLokasi.innerHTML = '<option value="">-- Pilih Lokasi --</option>';

        if (idJenisDipilih) {
            pilihanLokasi.disabled = false;

            // Tampilkan hanya lokasi yang sesuai jenis iklannya
            var lokasiSesuai = semuaLokasi.filter(function (lokasi) {
                return lokasi.id_jenis == idJenisDipilih;
            });

            lokasiSesuai.forEach(function (lokasi) {
                var opsi = document.createElement('option');
                opsi.value = lokasi.id;
                opsi.dataset.harga = lokasi.harga_per_hari;
                opsi.textContent = lokasi.kode_lokasi + ' - ' + lokasi.nama_lokasi + ' (Rp ' + parseInt(lokasi.harga_per_hari).toLocaleString('id-ID') + '/hari)';
                pilihanLokasi.appendChild(opsi);
            });
        } else {
            pilihanLokasi.disabled = true;
        }

        hitungHarga();
    }

    // Fungsi untuk menghitung total hari dan total harga secara otomatis
    function hitungHarga() {
        var tanggalMulai = new Date(inputTglMulai.value);
        var tanggalSelesai = new Date(inputTglSelesai.value);

        if (inputTglMulai.value && inputTglSelesai.value) {
            // Cek apakah tanggal selesai lebih awal dari tanggal mulai
            if (tanggalSelesai < tanggalMulai) {
                alert('Tanggal selesai tidak boleh kurang dari tanggal mulai');
                inputTglSelesai.value = '';
                inputTotalHari.value = '';
                inputTotalHarga.value = '';
                return;
            }

            // Hitung selisih hari
            var selisihWaktu = Math.abs(tanggalSelesai - tanggalMulai);
            var jumlahHari = Math.ceil(selisihWaktu / (1000 * 60 * 60 * 24)) + 1;
            inputTotalHari.value = jumlahHari;

            // Hitung total harga kalau lokasi sudah dipilih
            if (pilihanLokasi.value) {
                var opsiTerpilih = pilihanLokasi.options[pilihanLokasi.selectedIndex];
                var hargaPerHari = opsiTerpilih.dataset.harga;
                var totalHarga = jumlahHari * hargaPerHari;
                inputTotalHarga.value = totalHarga.toLocaleString('id-ID');
            } else {
                inputTotalHarga.value = '';
            }
        } else {
            inputTotalHari.value = '';
            inputTotalHarga.value = '';
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>