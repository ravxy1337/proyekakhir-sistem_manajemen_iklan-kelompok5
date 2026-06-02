<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

$id_lokasi = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Cek data lokasi ada dan ambil status
$cek = $conn->prepare("SELECT status_lokasi, foto_lokasi FROM lokasi WHERE id = ?");
$cek->bind_param("i", $id_lokasi);
$cek->execute();
$data_lokasi = $cek->get_result()->fetch_assoc();

if ($data_lokasi) {

    // Lokasi digunakan tdk boleh dihapus
    if ($data_lokasi['status_lokasi'] == 'digunakan') {
        $_SESSION['error'] = "Lokasi yang sedang digunakan tidak dapat dihapus.";

    } else {
        $hapus = $conn->prepare("DELETE FROM lokasi WHERE id = ?");
        $hapus->bind_param("i", $id_lokasi);

        if ($hapus->execute()) {
            // berhasil dihapus, hapus foto
            if ($data_lokasi['foto_lokasi'] && file_exists('../uploads/lokasi/' . $data_lokasi['foto_lokasi'])) {
                unlink('../uploads/lokasi/' . $data_lokasi['foto_lokasi']);
            }
            $_SESSION['success'] = "Lokasi berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus data.";
        }
    }
}

header("Location: index.php");
exit;
