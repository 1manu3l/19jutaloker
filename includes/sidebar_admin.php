<?php
// ============================================================
//  includes/sidebar_admin.php
//  Sidebar navigasi untuk semua halaman panel admin
// ============================================================
$halamanAktif = basename($_SERVER['PHP_SELF']);
?>
<div class="dashboard-wrap">
<aside class="sidebar">
    <div class="sidebar-brand">💼 <?= APP_NAME ?></div>
    <p class="sidebar-role">Panel Admin</p>
    <nav class="sidebar-nav">
        <a href="index.php"     class="<?= $halamanAktif==='index.php'     ? 'aktif':'' ?>">📊 Dashboard</a>
        <a href="lowongan.php"  class="<?= $halamanAktif==='lowongan.php'  ? 'aktif':'' ?>">📋 Kelola Lowongan</a>
        <a href="kategori.php"  class="<?= $halamanAktif==='kategori.php'  ? 'aktif':'' ?>">📂 Kelola Kategori</a>
        <a href="users.php"     class="<?= $halamanAktif==='users.php'     ? 'aktif':'' ?>">👥 Kelola Users</a>
        <a href="lamaran.php"   class="<?= $halamanAktif==='lamaran.php'   ? 'aktif':'' ?>">📨 Semua Lamaran</a>
        <hr class="sidebar-divider">
        <a href="../index.php">🌐 Lihat Situs</a>
        <a href="../logout.php" class="sidebar-logout">🚪 Logout</a>
    </nav>
    <div class="sidebar-user">
        <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['nama'],0,2)) ?></div>
        <div>
            <p class="sidebar-user-name"><?= bersihkan($_SESSION['nama']) ?></p>
            <p class="sidebar-user-role">Administrator</p>
        </div>
    </div>
</aside>
<main class="dashboard-main">