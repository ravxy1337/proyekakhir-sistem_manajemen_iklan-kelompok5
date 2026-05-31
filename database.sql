CREATE DATABASE iklanku;
USE iklanku;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE jenis_iklan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_jenis VARCHAR(100) NOT NULL UNIQUE,
    kategori ENUM('Cetak', 'Digital') NOT NULL,
    harga_per_hari DECIMAL(15,2) NOT NULL,
    deskripsi TEXT,
    status_aktif ENUM('aktif', 'nonaktif') DEFAULT 'aktif'
);

CREATE TABLE lokasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_lokasi VARCHAR(50) NOT NULL UNIQUE,
    nama_lokasi VARCHAR(150) NOT NULL,
    alamat TEXT NOT NULL,
    id_jenis INT NOT NULL,
    ukuran VARCHAR(50),
    harga_per_hari DECIMAL(15,2) NOT NULL,
    status_lokasi ENUM('tersedia', 'digunakan', 'maintenance') DEFAULT 'tersedia',
    foto_lokasi VARCHAR(255),
    FOREIGN KEY (id_jenis) REFERENCES jenis_iklan(id) ON DELETE RESTRICT
);

CREATE TABLE penyewaan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_invoice VARCHAR(50) NOT NULL UNIQUE,
    id_lokasi INT NOT NULL,
    id_jenis INT NOT NULL,
    nama_pic VARCHAR(100) NOT NULL,
    nama_pt VARCHAR(150) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    file_media VARCHAR(255),
    tgl_mulai DATE NOT NULL,
    tgl_selesai DATE NOT NULL,
    total_hari INT NOT NULL,
    total_harga DECIMAL(15,2) NOT NULL,
    status_penyewaan ENUM('Pending', 'Aktif', 'Selesai', 'Dibatalkan') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_lokasi) REFERENCES lokasi(id) ON DELETE RESTRICT,
    FOREIGN KEY (id_jenis) REFERENCES jenis_iklan(id) ON DELETE RESTRICT
);

CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_penyewaan INT NOT NULL,
    total_tagihan DECIMAL(15,2) NOT NULL,
    dibayar DECIMAL(15,2) DEFAULT 0,
    sisa_tagihan DECIMAL(15,2) NOT NULL,
    status_pembayaran ENUM('Belum Bayar', 'DP', 'Lunas') DEFAULT 'Belum Bayar',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_penyewaan) REFERENCES penyewaan(id) ON DELETE CASCADE
);

CREATE TABLE riwayat_pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pembayaran INT NOT NULL,
    nominal DECIMAL(15,2) NOT NULL,
    metode ENUM('Transfer', 'Cash', 'QRIS') NOT NULL,
    bukti_pembayaran VARCHAR(255) NOT NULL,
    tgl_bayar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pembayaran) REFERENCES pembayaran(id) ON DELETE CASCADE
);

INSERT INTO users (nama, email, password) VALUES 
('Administrator', 'iklanku@gmail.com', '$2y$10$YrtcTVIhLnbyRmw7aROgCe43xdsPqUJVbXA7367YyKqAU03g7l2ke');

-- pass : iklanku@88
