<?php
// ============================================================
//  includes/header.php
//  Navbar global untuk halaman PUBLIK (bukan dashboard)
//  Cara pakai: <?php require_once 'includes/header.php'; ?>
//              <?php require_once '../includes/header.php'; ?> (dari subfolder)
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();

// Tentukan halaman aktif untuk highlight menu
$halamanAktif = basename($_SERVER['PHP_SELF']);
?>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Tentukan halaman aktif untuk highlight menu
if (!isset($halamanAktif)) {
    $halamanAktif = basename($_SERVER['PHP_SELF']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($judulHalaman) ? $judulHalaman . ' — ' : '' ?><?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= isset($baseCSS) ? $baseCSS : '' ?>assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-inner">
        <!-- Logo -->
        <a href="<?= isset($baseCSS) ? $baseCSS : '' ?>index.php" class="nav-logo">
            💼 <?= APP_NAME ?>
        </a>

        <!-- Menu utama -->
        <ul class="nav-menu" id="navMenu">
            <li><a href="<?= isset($baseCSS) ? $baseCSS : '' ?>index.php"
                   class="<?= $halamanAktif === 'index.php' ? 'aktif' : '' ?>">Beranda</a></li>
            <li><a href="<?= isset($baseCSS) ? $baseCSS : '' ?>lowongan.php"
                   class="<?= $halamanAktif === 'lowongan.php' ? 'aktif' : '' ?>">Lowongan</a></li>
            <li><a href="<?= isset($baseCSS) ? $baseCSS : '' ?>about.php"
                   class="<?= $halamanAktif === 'about.php' ? 'aktif' : '' ?>">Tentang</a></li>
        </ul>

        <!-- Tombol login / dashboard -->
        <div class="nav-action">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $dashLink = '';
                switch ($_SESSION['role']) {
                    case 'admin':      $dashLink = 'admin/index.php';      break;
                    case 'mahasiswa':  $dashLink = 'mahasiswa/index.php';  break;
                    case 'perusahaan': $dashLink = 'perusahaan/index.php'; break;
                }
                ?>
                <a href="<?= isset($baseCSS) ? $baseCSS : '' ?><?= $dashLink ?>" class="btn-nav-dash">
                    Dashboard
                </a>
                <a href="<?= isset($baseCSS) ? $baseCSS : '' ?>logout.php" class="btn-nav-logout">Logout</a>
            <?php else: ?>
                <a href="<?= isset($baseCSS) ? $baseCSS : '' ?>login.php" class="btn-nav-login">Masuk</a>
                <a href="<?= isset($baseCSS) ? $baseCSS : '' ?>register.php" class="btn-nav-daftar">Daftar</a>
            <?php endif; ?>
        </div>

        <!-- Hamburger untuk mobile -->
        <button class="nav-hamburger" onclick="toggleMenu()">☰</button>
    </div>
</nav>

<script>
function toggleMenu() {
    document.getElementById('navMenu').classList.toggle('open');
}
</script>