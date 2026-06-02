<?php
require_once '../config/database.php';
require_once '../config/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$cek = $conn->prepare("SELECT p.status_penyewaan, pb.status_pembayaran 
    FROM penyewaan p 
    LEFT JOIN pembayaran pb ON p.id = pb.id_penyewaan 
    WHERE p.id = ?");
$cek->bind_param("i", $id);
$cek->execute();
$data = $cek->get_result()->fetch_assoc();

if (!$data) {
    $_SESSION['error'] = "Data penyewaan tidak ditemukan.";
    header("Location: index.php");
    exit;
}

if ($data['status_pembayaran'] == 'Lunas') {
    $_SESSION['error'] = "Pesanan yang sudah lunas tidak dapat dihapus.";
    header("Location: index.php");
    exit;
}

if ($data['status_penyewaan'] == 'Aktif') {
    $_SESSION['error'] = "Pesanan yang sedang aktif berjalan tidak dapat dihapus.";
    header("Location: index.php");
    exit;
}

$hapus = $conn->prepare("DELETE FROM penyewaan WHERE id = ?");
$hapus->bind_param("i", $id);

if ($hapus->execute()) {
    $_SESSION['success'] = "Data penyewaan berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus data penyewaan.";
}

header("Location: index.php");
exit;
