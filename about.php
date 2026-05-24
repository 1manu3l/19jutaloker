<?php
// ============================================================
//  about.php — Halaman statis profil anggota kelompok
//  ROOT: portal-lowongan/about.php
//  !! EDIT bagian DATA ANGGOTA sesuai kelompok kamu !!
// ============================================================
session_start();
require_once 'config.php';
require_once 'functions.php';

$judulHalaman = 'Tentang Tim';
$baseCSS = '';

// ============================================================
//  DATA ANGGOTA — EDIT SESUAI KELOMPOK KAMU
// ============================================================
$anggota = [
    [
        'nama'    => 'Imanuel Patandean',
        'nim'     => '240211060004',
        'peran'   => 'Database & Autentikasi',
        'tugas'   => 'config.php, auth_check.php, login, register, database SQL',
        'foto'    => 'assets/uploads/foto/default.png',
        'inisial' => 'A1',
    ],
    [
        'nama'    => 'Matthew Hyydemans',
        'nim'     => '240211060047',
        'peran'   => 'Halaman Publik & CSS',
        'tugas'   => 'index.php, lowongan.php, detail, profil perusahaan, style.css',
        'foto'    => 'assets/uploads/foto/default.png',
        'inisial' => 'A2',
    ],
    [
        'nama'    => 'Glory Manurung',
        'nim'     => '240211060037',
        'peran'   => 'Panel Admin',
        'tugas'   => 'admin/: dashboard, CRUD lowongan, user, kategori, lamaran; mahasiswa/: lowongan, lamar, riwayat. perusahaan/: lowongan, lamaran',
        'foto'    => 'assets/uploads/foto/default.png',
        'inisial' => 'A3',
    ]
];

require_once 'includes/header.php';
?>

<!-- Banner -->
<div class="page-banner">
    <div class="container">
        <h1>Tentang Tim Kami</h1>
        <p>Project Tugas Pemrograman Web — <?= date('Y') ?></p>
    </div>
</div>

<div class="container" style="padding-top:40px; padding-bottom:40px;">

    <!-- Info Aplikasi -->
    <div class="about-app">
        <h2>Tentang <?= APP_NAME ?></h2>
        <p>
            <strong><?= APP_NAME ?></strong> adalah portal lowongan kerja dan magang berbasis web
            yang dirancang khusus untuk mahasiswa. Platform ini menghubungkan mahasiswa
            dengan perusahaan dan UMKM lokal yang membuka kesempatan kerja atau magang.
        </p>
        <p>
            Aplikasi ini dibangun menggunakan <strong>HTML, CSS, JavaScript, PHP, dan MySQL</strong>
            tanpa menggunakan library atau framework apapun, sebagai bagian dari tugas
            project mata kuliah Pemrograman Web.
        </p>
        <div class="about-fitur">
            <div class="fitur-item">✅ Multi-user (Admin, Perusahaan, Mahasiswa)</div>
            <div class="fitur-item">✅ Sistem login & register dengan enkripsi password</div>
            <div class="fitur-item">✅ Manajemen lowongan (CRUD)</div>
            <div class="fitur-item">✅ Sistem lamaran online</div>
            <div class="fitur-item">✅ Filter & pencarian lowongan</div>
            <div class="fitur-item">✅ Profil perusahaan & mahasiswa</div>
        </div>
    </div>

    <!-- Kartu Anggota -->
    <h2 class="about-team-title">Anggota Kelompok</h2>
    <div class="about-grid">
        <?php foreach ($anggota as $idx => $org): ?>
        <div class="about-card">
            <div class="about-avatar"><?= $org['inisial'] ?></div>
            <h3 class="about-nama"><?= bersihkan($org['nama']) ?></h3>
            <p class="about-nim">NIM: <?= bersihkan($org['nim']) ?></p>
            <span class="about-peran"><?= bersihkan($org['peran']) ?></span>
            <p class="about-tugas"><?= bersihkan($org['tugas']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Info project -->
    <div class="about-info-box">
        <h3>Informasi Project</h3>
        <table class="about-table">
            <tr><td>Mata Kuliah</td><td>Pemrograman Web</td></tr>
            <tr><td>Tahun Akademik</td><td><?= date('Y') ?>/<?= date('Y') + 1 ?></td></tr>
            <tr><td>Teknologi</td><td>HTML, CSS, JavaScript, PHP 8, MySQL</td></tr>
            <tr><td>Deadline Submit</td><td>7 Juni 2026</td></tr>
        </table>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>