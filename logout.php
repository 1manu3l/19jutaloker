<?php
// ============================================================
//  logout.php
//  Hapus semua session dan arahkan kembali ke halaman login
//  Letakkan file ini di ROOT folder portal-lowongan/
// ============================================================

// Mulai session agar bisa dihapus
session_start();

// Hapus semua variabel session
$_SESSION = [];

// Hapus cookie session di browser pengunjung
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan session di server
session_destroy();

// Arahkan ke halaman login dengan pesan sukses logout
header("Location: login.php?pesan=logout");
exit;