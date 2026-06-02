<?php
require_once '../config/database.php';
require_once '../config/auth.php';

$id_penyewaan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query_cek = $conn->prepare("
    SELECT p.status_penyewaan, pb.status_pembayaran, pb.dibayar 
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
    $_SESSION['error'] = "Pesanan yang sudah lunas tidak dapat dibatalkan.";
    header("Location: index.php");
    exit;
}

$query_batal = $conn->prepare("UPDATE penyewaan SET status_penyewaan = 'Dibatalkan' WHERE id = ? AND status_penyewaan IN ('Pending', 'Aktif')");
$query_batal->bind_param("i", $id_penyewaan);
$query_batal->execute();

if ($query_batal->affected_rows > 0) {
    $pesan = "Pesanan berhasil dibatalkan.";
    if ($data_penyewaan['dibayar'] > 0) {
        $refund = $data_penyewaan['dibayar'] * 0.4;
        $pesan .= " Pengembalian dana ke pelanggan: Rp " . number_format($refund, 0, ',', '.') . " (40% dari DP).";
    }
    $_SESSION['success'] = $pesan;
} else {
    $_SESSION['error'] = "Gagal membatalkan pesanan. Status mungkin bukan Pending atau Aktif.";
}

header("Location: index.php");
exit;
