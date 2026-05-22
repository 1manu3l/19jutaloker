<?php

// ============================================================
//  auth_check.php
//  Proteksi halaman — cek apakah user sudah login
//
//  CARA PAKAI:
//  Letakkan di baris PALING ATAS setiap halaman yang dilindungi,
//  SEBELUM output HTML apapun. Contoh:
//
//  <?php
//  require_once '../auth_check.php'; // untuk halaman di subfolder
//  require_once 'auth_check.php';    // untuk halaman di root
//  cekLogin();                       // wajib login, semua role boleh
//  cekRole('admin');                 // hanya role admin
//  cekRole('mahasiswa');             // hanya role mahasiswa
//  cekRole('perusahaan');            // hanya role perusahaan
// ============================================================

// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ------------------------------------------------------------
// Fungsi: cekLogin()
// Cek apakah user sudah login (ada session user_id)
// Jika belum login → redirect ke halaman login
// ------------------------------------------------------------
function cekLogin() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header("Location: " . getBaseUrl() . "login.php?pesan=silakan_login");
        exit;
    }
}

// ------------------------------------------------------------
// Fungsi: cekRole($role)
// Cek apakah user sudah login DAN memiliki role yang sesuai
// Jika role tidak cocok → redirect ke halaman yang sesuai
// ------------------------------------------------------------
function cekRole($role) {
    // Pastikan sudah login dulu
    cekLogin();

    // Cek apakah role cocok
    if ($_SESSION['role'] !== $role) {
        // Arahkan ke dashboard sesuai role yang dimiliki
        $base = getBaseUrl();
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: {$base}admin/index.php");
                break;
            case 'mahasiswa':
                header("Location: {$base}mahasiswa/index.php");
                break;
            case 'perusahaan':
                header("Location: {$base}perusahaan/index.php");
                break;
            default:
                header("Location: {$base}login.php");
                break;
        }
        exit;
    }
}

// ------------------------------------------------------------
// Fungsi: sudahLogin()
// Kembalikan true jika user sudah login, false jika belum
// Dipakai di login.php agar user yang sudah login
// tidak bisa membuka halaman login lagi
// ------------------------------------------------------------
function sudahLogin() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ------------------------------------------------------------
// Fungsi: getBaseUrl()
// Kembalikan base URL sesuai posisi file yang memanggilnya
// Mendeteksi otomatis apakah file ada di subfolder atau root
// ------------------------------------------------------------
function getBaseUrl() {
    return '/portal-lowongan/';
}

// ------------------------------------------------------------
// Fungsi: getUser()
// Kembalikan data user yang sedang login dari session
// ------------------------------------------------------------
function getUser() {
    if (!sudahLogin()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'nama'  => $_SESSION['nama']  ?? '',
        'role'  => $_SESSION['role']  ?? '',
        'foto'  => $_SESSION['foto']  ?? 'default.png',
        'email' => $_SESSION['email'] ?? '',
    ];
}