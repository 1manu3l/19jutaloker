<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$halamanAktif = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($judulHalaman) ? $judulHalaman . ' — ' : '' ?><?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= $baseCSS ?? '' ?>assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="<?= $baseCSS ?? '' ?>index.php" class="nav-logo">
            <span>💼</span> <?= APP_NAME ?>
        </a>

        <ul class="nav-menu" id="navMenu">
            <li><a href="<?= $baseCSS ?? '' ?>index.php"
                class="<?= $halamanAktif === 'index.php' ? 'aktif' : '' ?>">Beranda</a></li>
            <li><a href="<?= $baseCSS ?? '' ?>lowongan.php"
                class="<?= $halamanAktif === 'lowongan.php' ? 'aktif' : '' ?>">Lowongan</a></li>
            <li><a href="<?= $baseCSS ?? '' ?>about.php"
                class="<?= $halamanAktif === 'about.php' ? 'aktif' : '' ?>">Tentang</a></li>
        </ul>

        <div class="nav-action">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $dashLink = match($_SESSION['role']) {
                    'admin'      => 'admin/index.php',
                    'mahasiswa'  => 'mahasiswa/index.php',
                    'perusahaan' => 'perusahaan/index.php',
                    default      => 'login.php'
                };
                ?>
                <a href="<?= ($baseCSS ?? '') . $dashLink ?>" class="btn-nav-dash">Dashboard</a>
                <a href="<?= $baseCSS ?? '' ?>logout.php" class="btn-nav-logout">Logout</a>
            <?php else: ?>
                <a href="<?= $baseCSS ?? '' ?>login.php"    class="btn-nav-masuk">Masuk</a>
                <a href="<?= $baseCSS ?? '' ?>register.php" class="btn-nav-daftar">Daftar</a>
            <?php endif; ?>
        </div>

        <button class="nav-hamburger" onclick="
            const m = document.getElementById('navMenu');
            m.classList.toggle('open');
            this.textContent = m.classList.contains('open') ? '✕' : '☰';
        ">☰</button>
    </div>
</nav>