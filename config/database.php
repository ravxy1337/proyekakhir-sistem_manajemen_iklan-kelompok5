<?php
$host = '127.0.0.1';

if (stripos(php_uname('r'), 'microsoft') !== false) {
    $host = gethostname() . '.mshome.net';
}

$user = 'root';
$pass = '';
$db = 'iklanku';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
define('BASE_URL', $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
