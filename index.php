<?php
// ============================================================
//  index.php — Beranda publik
//  ROOT: portal-lowongan/index.php
// ============================================================
session_start();
require_once 'config.php';
require_once 'functions.php';

$judulHalaman = 'Beranda';
$baseCSS = '';

// Ambil statistik untuk ditampilkan di hero section
$totalLowongan  = hitungBaris($conn, "SELECT id FROM lowongan WHERE status='aktif'");
$totalPerusahaan = hitungBaris($conn, "SELECT id FROM users WHERE role='perusahaan'");
$totalMahasiswa  = hitungBaris($conn, "SELECT id FROM users WHERE role='mahasiswa'");

// Ambil 6 lowongan terbaru yang masih aktif
$lowonganTerbaru = ambilSemua($conn,
    "SELECT l.*, u.nama AS nama_perusahaan, k.nama_kategori
     FROM lowongan l
     JOIN users u    ON l.user_id     = u.id
     JOIN kategori k ON l.kategori_id = k.id
     WHERE l.status = 'aktif' AND l.deadline >= CURDATE()
     ORDER BY l.created_at DESC
     LIMIT 6"
);

// Ambil semua kategori untuk filter
$semuaKategori = ambilSemua($conn, "SELECT * FROM kategori ORDER BY nama_kategori");

require_once 'includes/header.php';
?>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-judul">Temukan Lowongan Kerja & Magang Terbaik</h1>
            <p class="hero-sub">Platform khusus mahasiswa untuk mencari peluang karir dari perusahaan dan UMKM lokal.</p>

            <!-- Form pencarian cepat -->
            <form class="hero-search" method="GET" action="lowongan.php">
                <input type="text" name="keyword" placeholder="Cari posisi, perusahaan, atau kategori..." class="hero-input">
                <button type="submit" class="hero-btn">Cari Lowongan</button>
            </form>

            <!-- Statistik singkat -->
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-num"><?= $totalLowongan ?></span>
                    <span class="hero-stat-lbl">Lowongan Aktif</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-num"><?= $totalPerusahaan ?></span>
                    <span class="hero-stat-lbl">Perusahaan</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-num"><?= $totalMahasiswa ?></span>
                    <span class="hero-stat-lbl">Mahasiswa Terdaftar</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     FILTER KATEGORI
     ============================================================ -->
<section class="section-kategori">
    <div class="container">
        <h2 class="section-judul">Cari Berdasarkan Kategori</h2>
        <div class="kategori-grid">
            <a href="lowongan.php" class="kategori-card aktif-all">
                <span class="kategori-icon">🔍</span>
                <span>Semua</span>
            </a>
            <?php foreach ($semuaKategori as $kat): ?>
            <a href="lowongan.php?kategori=<?= $kat['id'] ?>" class="kategori-card">
                <span class="kategori-icon">📂</span>
                <span><?= bersihkan($kat['nama_kategori']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     LOWONGAN TERBARU
     ============================================================ -->
<section class="section-lowongan">
    <div class="container">
        <div class="section-head">
            <h2 class="section-judul">Lowongan Terbaru</h2>
            <a href="lowongan.php" class="link-lihat-semua">Lihat semua →</a>
        </div>

        <?php if (empty($lowonganTerbaru)): ?>
            <div class="empty-state">
                <p>Belum ada lowongan aktif saat ini.</p>
            </div>
        <?php else: ?>
        <div class="lowongan-grid">
            <?php foreach ($lowonganTerbaru as $low): ?>
            <div class="job-card">
                <div class="job-card-header">
                    <div class="job-logo">
                        <?= strtoupper(substr($low['nama_perusahaan'], 0, 2)) ?>
                    </div>
                    <div>
                        <h3 class="job-judul">
                            <a href="detail_lowongan.php?id=<?= $low['id'] ?>">
                                <?= bersihkan($low['judul']) ?>
                            </a>
                        </h3>
                        <a href="profil_perusahaan.php?id=<?= $low['user_id'] ?>" class="job-perusahaan">
                            <?= bersihkan($low['nama_perusahaan']) ?>
                        </a>
                    </div>
                </div>
                <div class="job-tags">
                    <?= badgeTipeKerja($low['tipe_kerja']) ?>
                    <span class="tag-kategori"><?= bersihkan($low['nama_kategori']) ?></span>
                </div>
                <div class="job-info">
                    <span class="job-lokasi">📍 <?= bersihkan($low['lokasi']) ?></span>
                    <span class="job-gaji">💰 <?= bersihkan($low['gaji']) ?></span>
                </div>
                <div class="job-footer">
                    <span class="job-deadline">🗓 <?= sisaHari($low['deadline']) ?></span>
                    <a href="detail_lowongan.php?id=<?= $low['id'] ?>" class="btn-lihat">Lihat Detail</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================================
     CTA — ajak daftar
     ============================================================ -->
<?php if (!isset($_SESSION['user_id'])): ?>
<section class="section-cta">
    <div class="container cta-inner">
        <h2>Siap Mulai Karir Kamu?</h2>
        <p>Daftar sekarang gratis dan mulai lamar lowongan impianmu.</p>
        <div class="cta-btns">
            <a href="register.php" class="btn-cta-utama">Daftar sebagai Mahasiswa</a>
            <a href="register.php" class="btn-cta-sekunder">Daftar sebagai Perusahaan</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>