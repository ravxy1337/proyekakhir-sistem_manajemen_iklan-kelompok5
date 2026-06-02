<?php
require_once 'includes/header.php';
$menu_aktif = 'setting';
require_once 'includes/sidebar.php';

// Ambil data user yang sedang login
$id_user = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, nama, email FROM users WHERE id = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$pesan_sukses = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // === UPDATE PROFIL (nama & email) ===
    if ($aksi == 'update_profil') {
        $nama_baru = trim($_POST['nama']);
        $email_baru = trim($_POST['email']);

        // Validasi nama
        if (empty($nama_baru)) {
            $pesan_error = "Nama tidak boleh kosong.";
        } elseif (strlen($nama_baru) < 3) {
            $pesan_error = "Nama minimal 3 karakter.";
        } elseif (strlen($nama_baru) > 100) {
            $pesan_error = "Nama maksimal 100 karakter.";
        }
        // Validasi email
        elseif (empty($email_baru)) {
            $pesan_error = "Email tidak boleh kosong.";
        } elseif (!filter_var($email_baru, FILTER_VALIDATE_EMAIL)) {
            $pesan_error = "Format email tidak valid.";
        } elseif (!preg_match('/@gmail\.com$/', $email_baru)) {
            $pesan_error = "Hanya email @gmail.com yang diizinkan.";
        } else {
            // Cek email sudah dipakai user lain atau belum
            $cek = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $cek->bind_param("si", $email_baru, $id_user);
            $cek->execute();
            if ($cek->get_result()->num_rows > 0) {
                $pesan_error = "Email sudah digunakan oleh akun lain.";
            } else {
                $stmt_update = $conn->prepare("UPDATE users SET nama = ?, email = ? WHERE id = ?");
                $stmt_update->bind_param("ssi", $nama_baru, $email_baru, $id_user);
                if ($stmt_update->execute()) {
                    $_SESSION['nama'] = $nama_baru;
                    $user['nama'] = $nama_baru;
                    $user['email'] = $email_baru;
                    $pesan_sukses = "Profil berhasil diperbarui.";
                } else {
                    $pesan_error = "Gagal memperbarui profil.";
                }
            }
        }
    }

    // UPDATE PASSWORD 
    if ($aksi == 'update_password') {
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $konfirmasi_password = $_POST['konfirmasi_password'];

        // Ambil pass hash di db
        $stmt_pw = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt_pw->bind_param("i", $id_user);
        $stmt_pw->execute();
        $data_pw = $stmt_pw->get_result()->fetch_assoc();

        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
            $pesan_error = "Semua field password wajib diisi.";
        } elseif (!password_verify($password_lama, $data_pw['password'])) {
            $pesan_error = "Password lama salah.";
        } elseif (strlen($password_baru) < 6) {
            $pesan_error = "Password baru minimal 6 karakter.";
        } elseif ($password_baru !== $konfirmasi_password) {
            $pesan_error = "Konfirmasi password tidak cocok.";
        } elseif ($password_lama === $password_baru) {
            $pesan_error = "Password baru tidak boleh sama dengan password lama.";
        } else {
            $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt_upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_upd->bind_param("si", $hash_baru, $id_user);
            if ($stmt_upd->execute()) {
                $pesan_sukses = "Password berhasil diubah.";
            } else {
                $pesan_error = "Gagal mengubah password.";
            }
        }
    }
}
?>

<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Pengaturan Akun</h2>
        <p class="text-sm text-gray-500 mt-1">Ubah informasi profil dan password Anda</p>
    </div>

    <?php if ($pesan_sukses): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
            <p class="text-sm text-green-700"><?= htmlspecialchars($pesan_sukses) ?></p>
        </div>
    <?php endif; ?>

    <?php if ($pesan_error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
            <p class="text-sm text-red-700"><?= htmlspecialchars($pesan_error) ?></p>
        </div>
    <?php endif; ?>

    <!-- update profil -->
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Profil</h3>
        <form id="formUpdateProfil" method="POST" action="">
            <input type="hidden" name="aksi" value="update_profil">
            <div class="mb-4">
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" required minlength="3" maxlength="100"
                    value="<?= htmlspecialchars($user['nama']) ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">Hanya email @gmail.com yang diizinkan.</p>
            </div>
            <button type="button" onclick="bukaPopup('popupKonfirmasiUpdate')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium">
                Simpan Profil
            </button>
        </form>
    </div>

    <!-- ubah pass -->
    <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ubah Password</h3>
        <form id="formUbahPassword" method="POST" action="">
            <input type="hidden" name="aksi" value="update_password">
            <div class="mb-4">
                <label for="password_lama" class="block text-sm font-medium text-gray-700 mb-1">Password Lama</label>
                <input type="password" id="password_lama" name="password_lama" required minlength="6"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="password_baru" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                <input type="password" id="password_baru" name="password_baru" required minlength="6"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">Minimal 6 karakter.</p>
            </div>
            <div class="mb-4">
                <label for="konfirmasi_password" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi
                    Password Baru</label>
                <input type="password" id="konfirmasi_password" name="konfirmasi_password" required minlength="6"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="button" onclick="bukaPopup('popupKonfirmasiPassword')"
                class="bg-gray-800 hover:bg-gray-900 text-white px-6 py-2 rounded-lg text-sm font-medium">
                Ubah Password
            </button>
        </form>
    </div>
</div>

<div id="popupKonfirmasiUpdate" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center animate-fade-in">
        <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="help-circle" class="w-8 h-8"></i>
        </div>
        
        <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Simpan</h3>
        <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin data lokasi yang diinputkan sudah sesuai?</p>
        
        <div class="flex gap-3 justify-center">
            <button type="button" onclick="tutupPopup('popupKonfirmasiUpdate')" 
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                Batal
            </button>
            <button type="button" onclick="submitFormNyata('formUpdateProfil')" 
                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                Ya, Simpan
            </button>
        </div>
    </div>
</div>

<div id="popupKonfirmasiPassword" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-sm w-full p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="lock" class="w-8 h-8"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Ganti Password</h3>
        <p class="text-sm text-gray-500 mb-6">Tindakan ini akan merubah kunci keamanan akun Anda. Yakin ingin melanjutkan?</p>
        <div class="flex gap-3 justify-center">
            <button type="button" onclick="tutupPopup('popupKonfirmasiPassword')" 
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                Batal
            </button>
            <button type="button" onclick="submitFormNyata('formUbahPassword')" 
                class="w-full px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg text-sm font-medium">
                Ya, Ubah
            </button>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>

