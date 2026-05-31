<?php
require_once '../config/database.php';
require_once '../config/auth.php';

$id_penyewaan = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$query_cek = $conn->prepare("
    SELECT p.status_penyewaan, pb.status_pembayaran 
    FROM penyewaan p 
    LEFT JOIN pembayaran pb ON p.id = pb.id_penyewaan 
    WHERE p.id = ?
");
$query_cek->bind_param("i", $id_penyewaan);
$query_cek->execute();
$data_penyewaan = $query_cek->get_result()->fetch_assoc();

if (!$data_penyewaan) {
    $_SESSION['error'] = "Data penyewaan tidak ditemukan.";
    header("Location: index.php");
    exit;
}

if ($data_penyewaan['status_pembayaran'] == 'Lunas') {
    $_SESSION['error'] = "Pesanan yang sudah lunas tidak dapat dihapus. Harap gunakan fitur pembatalan jika diperlukan, atau hubungi administrator.";
    header("Location: index.php");
    exit;
}

if ($data_penyewaan['status_penyewaan'] == 'Aktif') {
    $_SESSION['error'] = "Pesanan yang sedang aktif berjalan tidak dapat dihapus.";
    header("Location: index.php");
    exit;
}

try {
    $query_hapus = $conn->prepare("DELETE FROM penyewaan WHERE id = ?");
    $query_hapus->bind_param("i", $id_penyewaan);

    if ($query_hapus->execute()) {
        $_SESSION['success'] = "Data penyewaan berhasil dihapus secara permanen.";
    } else {
        $_SESSION['error'] = "Gagal menghapus data penyewaan.";
    }
} catch (mysqli_sql_exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan sistem: Tidak dapat menghapus data penyewaan.";
}

header("Location: index.php");
exit;
