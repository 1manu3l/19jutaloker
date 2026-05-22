<?php

// ============================================================
//  config.php
//  Koneksi ke database MySQL
//  File ini di-include di SETIAP halaman PHP lainnya
// ============================================================

// Informasi koneksi database
define('DB_HOST', 'localhost');   // server database (jangan diubah untuk XAMPP lokal)
define('DB_USER', 'root');        // username MySQL default XAMPP
define('DB_PASS', '');            // password MySQL default XAMPP (kosong)
define('DB_NAME', 'portal_lowongan'); // nama database yang sudah dibuat

// Membuat koneksi ke MySQL
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek apakah koneksi berhasil
if (!$conn) {
    die("
        <div style='font-family:sans-serif; background:#fee; border:1px solid #f00; 
                    padding:20px; margin:20px; border-radius:8px;'>
            <h3 style='color:red; margin:0 0 8px'>Koneksi Database Gagal</h3>
            <p style='margin:0'>Error: " . mysqli_connect_error() . "</p>
            <p style='margin:8px 0 0; font-size:13px; color:#666'>
                Pastikan XAMPP sudah berjalan dan MySQL aktif.
            </p>
        </div>
    ");
}

// Set charset ke UTF-8 agar karakter Indonesia tampil dengan benar
mysqli_set_charset($conn, 'utf8mb4');

// ============================================================
//  Pengaturan tampilan error (aktifkan saat development)
// ============================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ============================================================
//  Pengaturan timezone ke WIB/WITA
// ============================================================
date_default_timezone_set('Asia/Makassar'); // WITA (UTC+8)

// ============================================================
//  Konstanta umum aplikasi
// ============================================================
define('APP_NAME', 'KerjaCampus');
define('BASE_URL', 'http://localhost/portal-lowongan/');
define('UPLOAD_CV',   'assets/uploads/cv/');
define('UPLOAD_FOTO', 'assets/uploads/foto/');