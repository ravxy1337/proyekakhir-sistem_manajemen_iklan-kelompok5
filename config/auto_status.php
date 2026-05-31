<?php
if (isset($conn)) {
    // Set penyewaan yang sudah lewat tanggal selesai menjadi selesai
    $conn->query("UPDATE penyewaan SET status_penyewaan = 'Selesai' WHERE tgl_selesai < CURDATE() AND status_penyewaan IN ('Aktif', 'Pending')");

    // Set lokasi kembali tersedia jika tidak ada penyewaan 
    $conn->query("UPDATE lokasi SET status_lokasi = 'tersedia' WHERE id NOT IN (SELECT id_lokasi FROM penyewaan WHERE status_penyewaan IN ('Aktif', 'Pending')) AND status_lokasi = 'digunakan'");

    // Set lokasi menjadi digunakan jika ada penyewaan aktif 
    $conn->query("UPDATE lokasi SET status_lokasi = 'digunakan' WHERE id IN (SELECT id_lokasi FROM penyewaan WHERE status_penyewaan IN ('Aktif', 'Pending') AND tgl_mulai <= CURDATE() AND tgl_selesai >= CURDATE()) AND status_lokasi = 'tersedia'");
}
?>