-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 02 Jun 2026 pada 17.26
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `iklanku`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_iklan`
--

CREATE TABLE `jenis_iklan` (
  `id` int(11) NOT NULL,
  `nama_jenis` varchar(100) NOT NULL,
  `kategori` enum('Cetak','Digital') NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status_aktif` enum('aktif','nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jenis_iklan`
--

INSERT INTO `jenis_iklan` (`id`, `nama_jenis`, `kategori`, `deskripsi`, `status_aktif`) VALUES
(3, 'Video Tron', 'Digital', 'Ini merupakan videotron dengan menggunakan apagitu', 'aktif'),
(4, 'Neon Box', 'Digital', 'Iklan neon box yang menyala di malam hari', 'aktif'),
(5, 'Megatron', 'Digital', 'Megatron\r\n', 'aktif'),
(7, 'Mobile Videotron', 'Digital', 'lebih hd', 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `lokasi`
--

CREATE TABLE `lokasi` (
  `id` int(11) NOT NULL,
  `kode_lokasi` varchar(50) NOT NULL,
  `nama_lokasi` varchar(150) NOT NULL,
  `alamat` text NOT NULL,
  `id_jenis` int(11) NOT NULL,
  `ukuran` varchar(50) DEFAULT NULL,
  `status_lokasi` enum('tersedia','digunakan','maintenance') DEFAULT 'tersedia',
  `foto_lokasi` varchar(255) DEFAULT NULL,
  `harga_per_hari` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `lokasi`
--

INSERT INTO `lokasi` (`id`, `kode_lokasi`, `nama_lokasi`, `alamat`, `id_jenis`, `ukuran`, `status_lokasi`, `foto_lokasi`, `harga_per_hari`) VALUES
(2, 'LOC-001', 'Pertigaan UTM', 'Jl. Tellang nomor 10 pertigaan barat kampus', 3, '5x10', 'digunakan', '1780410004_41.-Videotron-Outdoor-Itu-Apa-Kenali-Lebih-Jauh-Mulai-Sekara.jpg', 100000.00),
(3, 'LOC-002', 'Jl. Mawar nomor 88', 'Jl. Mawar nomor 88, Kab Bantul', 4, '5x10', 'digunakan', '1780409874_Neon-Box.jpg', 100000.00),
(4, 'LOC-003', 'Pertigaan Socah', 'Jl. Socah nomor 15', 3, '4x6', 'tersedia', '1780409696_videotron.jpg', 100000.00),
(5, 'LOC-004', 'Jl. Sukarno Hata', 'Jl. Suhat nomor 19 ', 7, '4x9', 'digunakan', '1780410218_41.-Videotron-Outdoor-Itu-Apa-Kenali-Lebih-Jauh-Mulai-Sekara.jpg', 100000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL,
  `id_penyewaan` int(11) NOT NULL,
  `total_tagihan` decimal(15,2) NOT NULL,
  `dibayar` decimal(15,2) DEFAULT 0.00,
  `sisa_tagihan` decimal(15,2) NOT NULL,
  `status_pembayaran` enum('Belum Bayar','DP','Lunas') DEFAULT 'Belum Bayar',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `id_penyewaan`, `total_tagihan`, `dibayar`, `sisa_tagihan`, `status_pembayaran`, `updated_at`) VALUES
(2, 2, 300000.00, 300000.00, 0.00, 'Lunas', '2026-05-29 15:42:55'),
(6, 6, 300000.00, 150000.00, 150000.00, 'DP', '2026-06-02 10:23:34'),
(7, 7, 600000.00, 600000.00, 0.00, 'Lunas', '2026-06-02 12:18:35'),
(8, 8, 500000.00, 500000.00, 0.00, 'Lunas', '2026-05-10 03:05:00'),
(9, 9, 1200000.00, 1200000.00, 0.00, 'Lunas', '2026-05-15 04:45:00'),
(10, 10, 400000.00, 200000.00, 200000.00, 'DP', '2026-05-20 02:30:00'),
(12, 12, 600000.00, 0.00, 600000.00, 'Belum Bayar', '2026-06-01 09:45:00'),
(13, 13, 1200000.00, 1200000.00, 0.00, 'Lunas', '2026-05-05 01:45:00'),
(14, 14, 800000.00, 400000.00, 400000.00, 'DP', '2026-05-12 08:55:00'),
(15, 15, 1100000.00, 1100000.00, 0.00, 'Lunas', '2026-05-27 03:30:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penyewaan`
--

CREATE TABLE `penyewaan` (
  `id` int(11) NOT NULL,
  `no_invoice` varchar(50) NOT NULL,
  `id_lokasi` int(11) NOT NULL,
  `id_jenis` int(11) NOT NULL,
  `nama_pic` varchar(100) NOT NULL,
  `nama_pt` varchar(150) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `file_media` varchar(255) DEFAULT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `total_hari` int(11) NOT NULL,
  `total_harga` decimal(15,2) NOT NULL,
  `status_penyewaan` enum('Pending','Aktif','Selesai','Dibatalkan') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penyewaan`
--

INSERT INTO `penyewaan` (`id`, `no_invoice`, `id_lokasi`, `id_jenis`, `nama_pic`, `nama_pt`, `no_hp`, `email`, `alamat`, `file_media`, `tgl_mulai`, `tgl_selesai`, `total_hari`, `total_harga`, `status_penyewaan`, `created_at`) VALUES
(2, 'INV-20260529-001', 2, 3, 'Rava', 'Pt Doa Bapak Restu Ibu', '088999111229', 'ravxy@gmail.com', 'Jl. ini itu nomor 5', 'media_6a19b3a0f2227.png', '2026-05-29', '2026-05-31', 3, 300000.00, 'Selesai', '2026-05-29 15:41:20'),
(6, 'INV-20260602-001', 2, 3, 'Anu', 'Anu', '010203846571', 'anu@gmail.com', 'Jl. Tellang nomor 10 pertigaan barat kampus', 'media_6a1eac9a3a072.png', '2026-06-02', '2026-06-04', 3, 300000.00, 'Aktif', '2026-06-02 10:12:42'),
(7, 'INV-20260602-002', 5, 7, 'Bandi', 'PT Azril Mencari Posisi', '088999111229', 'podoae@gmail.com', 'Jl. Pancoran disana', 'media_6a1ec9f25ba31.png', '2026-06-02', '2026-06-05', 4, 600000.00, 'Aktif', '2026-06-02 12:17:54'),
(8, 'INV-20260510-001', 2, 3, 'Budi Santoso', 'CV. Maju Bersama', '081234567890', 'budi@gmail.com', 'Jl. Pemuda No. 45, Surabaya', NULL, '2026-05-10', '2026-05-15', 5, 500000.00, 'Selesai', '2026-05-10 03:00:00'),
(9, 'INV-20260515-001', 4, 3, 'Linda Wijaya', 'PT. Indofood Sukses Makmur', '085678901234', 'linda@gmail.com', 'Kawasan Industri Rungkut, Surabaya', NULL, '2026-05-15', '2026-05-25', 10, 1200000.00, 'Selesai', '2026-05-15 04:30:00'),
(10, 'INV-20260520-001', 3, 4, 'Doni Setiawan', 'CV. Citra Abadi', '089012345678', 'doni@gmail.com', 'Jl. Diponegoro No. 12, Sidoarjo', NULL, '2026-05-20', '2026-05-24', 4, 400000.00, 'Dibatalkan', '2026-05-20 02:15:00'),
(12, 'INV-20260601-001', 4, 3, 'H. Slamet', 'Toko Elektronik Berkah', '081398765432', 'slamet@gmail.com', 'Pasar Besar Blok A No. 3, Malang', NULL, '2026-06-05', '2026-06-10', 5, 600000.00, 'Pending', '2026-06-01 09:45:00'),
(13, 'INV-20260505-001', 4, 3, 'Ahmad Fauzi', 'CV. Surya Abadi', '081122334455', 'ahmad@gmail.com', 'Jl. Basuki Rahmat No. 78, Surabaya', NULL, '2026-05-05', '2026-05-15', 10, 1200000.00, 'Selesai', '2026-05-05 01:30:00'),
(14, 'INV-20260512-001', 2, 3, 'Hendra Wijaya', 'PT. Jaya Sentosa', '081987654321', 'hendra@gmail.com', 'Jl. Ahmad Yani No. 102, Sidoarjo', NULL, '2026-05-12', '2026-05-20', 8, 800000.00, 'Dibatalkan', '2026-05-12 08:40:00'),
(15, 'INV-20260527-001', 3, 4, 'Sarah Amelia', 'Kopi Kenangan Selalu', '085544332211', 'sarah@gmail.com', 'Tunjungan Plaza 3 Lt. 2, Surabaya', NULL, '2026-05-27', '2026-06-07', 11, 1100000.00, 'Aktif', '2026-05-27 03:20:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_pembayaran`
--

CREATE TABLE `riwayat_pembayaran` (
  `id` int(11) NOT NULL,
  `id_pembayaran` int(11) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `metode` enum('Transfer','Cash','QRIS') NOT NULL,
  `bukti_pembayaran` varchar(255) NOT NULL,
  `tgl_bayar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `riwayat_pembayaran`
--

INSERT INTO `riwayat_pembayaran` (`id`, `id_pembayaran`, `nominal`, `metode`, `bukti_pembayaran`, `tgl_bayar`) VALUES
(2, 2, 120000.00, 'Cash', 'bukti_transaksi.jpg', '2026-05-29 15:42:22'),
(3, 2, 180000.00, 'Transfer', 'bukti_transaksi.jpg', '2026-05-29 15:42:55'),
(6, 6, 150000.00, 'QRIS', 'bukti_transaksi.jpg', '2026-06-02 10:23:34'),
(7, 7, 600000.00, 'QRIS', 'bukti_transaksi.jpg', '2026-06-02 12:18:35'),
(8, 8, 500000.00, 'Cash', 'bukti_transaksi.jpg', '2026-05-10 03:05:00'),
(9, 9, 1200000.00, 'Transfer', 'bukti_transaksi.jpg', '2026-05-15 04:45:00'),
(10, 10, 200000.00, 'QRIS', 'bukti_transaksi.jpg', '2026-05-20 02:30:00'),
(12, 13, 1200000.00, 'Transfer', 'bukti_transaksi.jpg', '2026-05-05 01:45:00'),
(13, 14, 400000.00, 'QRIS', 'bukti_transaksi.jpg', '2026-05-12 08:55:00'),
(14, 15, 1100000.00, 'Transfer', 'bukti_transaksi.jpg', '2026-05-27 03:30:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `created_at`) VALUES
(1, 'Administrator', 'admin@gmail.com', '$2y$10$bLODycXhy8RCpNy9K6m4JORB4L/GkmaxrJULSnrY0tEYfd3slULZW', '2026-05-24 12:46:54');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `jenis_iklan`
--
ALTER TABLE `jenis_iklan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_jenis` (`nama_jenis`);

--
-- Indeks untuk tabel `lokasi`
--
ALTER TABLE `lokasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_lokasi` (`kode_lokasi`),
  ADD KEY `id_jenis` (`id_jenis`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_penyewaan` (`id_penyewaan`);

--
-- Indeks untuk tabel `penyewaan`
--
ALTER TABLE `penyewaan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_invoice` (`no_invoice`),
  ADD KEY `id_lokasi` (`id_lokasi`),
  ADD KEY `id_jenis` (`id_jenis`);

--
-- Indeks untuk tabel `riwayat_pembayaran`
--
ALTER TABLE `riwayat_pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pembayaran` (`id_pembayaran`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `jenis_iklan`
--
ALTER TABLE `jenis_iklan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `lokasi`
--
ALTER TABLE `lokasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `penyewaan`
--
ALTER TABLE `penyewaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `riwayat_pembayaran`
--
ALTER TABLE `riwayat_pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `lokasi`
--
ALTER TABLE `lokasi`
  ADD CONSTRAINT `lokasi_ibfk_1` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_iklan` (`id`);

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_penyewaan`) REFERENCES `penyewaan` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penyewaan`
--
ALTER TABLE `penyewaan`
  ADD CONSTRAINT `penyewaan_ibfk_1` FOREIGN KEY (`id_lokasi`) REFERENCES `lokasi` (`id`),
  ADD CONSTRAINT `penyewaan_ibfk_2` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_iklan` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_pembayaran`
--
ALTER TABLE `riwayat_pembayaran`
  ADD CONSTRAINT `riwayat_pembayaran_ibfk_1` FOREIGN KEY (`id_pembayaran`) REFERENCES `pembayaran` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
