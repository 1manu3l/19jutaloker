<?php
// ============================================================
// includes/sidebar_prs.php
// Sidebar khusus perusahaan
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$halamanAktif = basename($_SERVER['PHP_SELF']);
?>

<div class="dash-layout">

<aside class="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <span class="sidebar-brand-icon">🏢</span>
        <?= APP_NAME ?>
    </div>

    <p class="sidebar-role">Panel Perusahaan</p>

    <!-- Menu -->
    <nav class="sidebar-nav">

        <div class="sidebar-section-label">
            Menu Utama
        </div>

        <a href="index.php"
           class="<?= $halamanAktif === 'index.php' ? 'aktif' : '' ?>">
            <span class="nav-icon">📊</span>
            Dashboard
        </a>

        <a href="lowongan.php"
           class="<?= $halamanAktif === 'lowongan.php' ? 'aktif' : '' ?>">
            <span class="nav-icon">💼</span>
            Kelola Lowongan
        </a>

        <a href="lamaran.php"
           class="<?= $halamanAktif === 'lamaran.php' ? 'aktif' : '' ?>">
            <span class="nav-icon">📨</span>
            Lamaran Masuk
        </a>

        <a href="profil.php"
           class="<?= $halamanAktif === 'profil.php' ? 'aktif' : '' ?>">
            <span class="nav-icon">👤</span>
            Profil Perusahaan
        </a>

        <hr class="sidebar-divider">

        <div class="sidebar-section-label">
            Lainnya
        </div>

        <a href="../index.php">
            <span class="nav-icon">🌐</span>
            Lihat Website
        </a>

        <a href="../logout.php" class="nav-logout">
            <span class="nav-icon">🚪</span>
            Logout
        </a>

    </nav>

    <!-- User -->
    <div class="sidebar-user">

        <div class="sidebar-avatar">
            <?= strtoupper(substr($_SESSION['nama'], 0, 2)) ?>
        </div>

        <div>
            <p class="sidebar-user-name">
                <?= htmlspecialchars($_SESSION['nama']) ?>
            </p>

            <p class="sidebar-user-role">
                Perusahaan
            </p>
        </div>

    </div>

</aside>