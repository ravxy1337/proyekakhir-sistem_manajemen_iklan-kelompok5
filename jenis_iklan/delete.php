<?php
require_once '../config/database.php';
require_once '../config/auth.php';

$id_iklan = $_GET['id'] ?? 0;

$perintah_cek = $conn->prepare("SELECT id FROM penyewaan WHERE id_jenis = ? LIMIT 1");
$perintah_cek->bind_param("i", $id_iklan);
$perintah_cek->execute();
$hasil_cek = $perintah_cek->get_result();

if ($hasil_cek->num_rows > 0) {
    $_SESSION['error'] = "Data tidak dapat dihapus karena masih terhubung dengan jadwal penyewaan.";
} else {
    $perintah_hapus = $conn->prepare("DELETE FROM jenis_iklan WHERE id = ?");
    $perintah_hapus->bind_param("i", $id_iklan);

    if ($perintah_hapus->execute()) {
        $_SESSION['success'] = "Jenis iklan berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus data.";
    }
}

header("Location: index.php");
exit;
?>