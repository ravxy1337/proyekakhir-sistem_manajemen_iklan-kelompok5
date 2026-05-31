<?php
require_once 'config/database.php';
require_once 'config/auth.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // validasi input kosong
    if (empty($email) || empty($password)) {
        $error = "Email dan password wajib diisi.";
    }
    // validasi format email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    }
    // validasi harus @gmail.com
    elseif (!preg_match('/@gmail\.com$/', $email)) {
        $error = "Hanya email @gmail.com yang diizinkan.";
    }
    // validasi panjang password
    elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $stmt = $conn->prepare("SELECT id, nama, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                header("Location: index.php");
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Email tidak ditemukan.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Manajemen Penyewaan Iklan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-8 mx-4">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Sistem Manajemen Iklan</h1>
            <p class="text-gray-500 text-sm mt-2">Masuk ke akun Anda untuk melanjutkan</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-5">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="admin@gmail.com"
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                <p class="text-xs text-gray-500 mt-1">Gunakan email @gmail.com</p>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required minlength="6"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Minimal 6 karakter">
            </div>
            <button type="submit"
                class="w-full bg-gray-800 hover:bg-gray-900 text-white font-medium py-2 px-4 rounded-lg">
                Masuk
            </button>
        </form>
    </div>
</body>

</html>