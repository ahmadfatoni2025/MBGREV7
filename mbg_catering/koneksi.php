<?php
// === Pengaturan Konfigurasi Database ===
// Pastikan XAMPP (MySQL) sudah running
$host     = "localhost"; // Nama host server database 
$username = "root";      // Username database default XAMPP
$password = "";          // Password database default XAMPP (biasanya kosong)
$database = "inventaris_mbg"; // PASTIKAN NAMA DATABASE SAMA dengan yang kamu buat

// Buat koneksi baru menggunakan MySQLi Object-Oriented
$koneksi = new mysqli($host, $username, $password, $database);

// Cek apakah koneksi gagal
if ($koneksi->connect_error) {
    // Pesan ini akan muncul jika XAMPP belum dinyalakan atau nama database salah
    die("Koneksi ke database gagal: " . $koneksi->connect_error);
}
// Koneksi berhasil. Variabel $koneksi siap digunakan di semua file PHP.
?>
