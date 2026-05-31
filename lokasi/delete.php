<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt_cek = $conn->prepare("SELECT status_lokasi, foto_lokasi FROM lokasi WHERE id = ?");
$stmt_cek->bind_param("i", $id);
$stmt_cek->execute();
$data = $stmt_cek->get_result()->fetch_assoc();

if ($data) {
    if ($data['status_lokasi'] == 'digunakan') {
        $_SESSION['error'] = "Lokasi yang sedang digunakan tidak dapat dihapus.";
    } else {
        try {
            $stmt = $conn->prepare("DELETE FROM lokasi WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                if ($data['foto_lokasi'] && file_exists('../uploads/lokasi/' . $data['foto_lokasi'])) {
                    unlink('../uploads/lokasi/' . $data['foto_lokasi']);
                }
                $_SESSION['success'] = "Lokasi berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus data.";
            }
        } catch (mysqli_sql_exception $e) {
            $_SESSION['error'] = "Tidak dapat menghapus lokasi karena terhubung dengan data penyewaan.";
        }
    }
}

header("Location: index.php");
exit;

