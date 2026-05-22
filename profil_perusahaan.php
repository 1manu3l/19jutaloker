<?php
// ============================================================
//  profil_perusahaan.php — Profil perusahaan / UMKM (?id=)
//  ROOT: portal-lowongan/profil_perusahaan.php
// ============================================================
session_start();
require_once 'config.php';
require_once 'auth_check.php';
require_once 'functions.php';

$id = sanitasiInt($_GET['id'] ?? 0);
if ($id <= 0) redirect('lowongan.php');

// Ambil data perusahaan
$perusahaan = ambilSatu($conn,
    "SELECT * FROM users WHERE id = {$id} AND role = 'perusahaan'"
);
if (!$perusahaan) redirect('lowongan.php');

// Ambil semua lowongan aktif dari perusahaan ini
$lowonganAktif = ambilSemua($conn,
    "SELECT l.*, k.nama_kategori
     FROM lowongan l
     JOIN kategori k ON l.kategori_id = k.id
     WHERE l.user_id = {$id} AND l.status = 'aktif' AND l.deadline >= CURDATE()
     ORDER BY l.created_at DESC"
);

// Statistik perusahaan
$totalLowongan  = hitungBaris($conn, "SELECT id FROM lowongan WHERE user_id = {$id}");
$totalAktif     = hitungBaris($conn, "SELECT id FROM lowongan WHERE user_id = {$id} AND status='aktif'");
$totalLamaran   = hitungBaris($conn,
    "SELECT la.id FROM lamaran la
     JOIN lowongan lo ON la.lowongan_id = lo.id
     WHERE lo.user_id = {$id}"
);

$judulHalaman = 'Profil ' . bersihkan($perusahaan['nama']);
$baseCSS = '';
require_once 'includes/header.php';
?>

<div class="container" style="padding-top:30px; padding-bottom:40px;">

    <div class="breadcrumb">
        <a href="index.php">Beranda</a> &rsaquo;
        <a href="lowongan.php">Lowongan</a> &rsaquo;
        <span><?= bersihkan($perusahaan['nama']) ?></span>
    </div>

    <!-- ========================================================
         HERO PROFIL
         ======================================================== -->
    <div class="profil-hero">
        <div class="profil-logo-lg">
            <?= strtoupper(substr($perusahaan['nama'], 0, 2)) ?>
        </div>
        <div class="profil-info">
            <h1 class="profil-nama"><?= bersihkan($perusahaan['nama']) ?></h1>
            <p class="profil-tipe">Perusahaan / UMKM</p>
            <?php if (!empty($perusahaan['alamat'])): ?>
                <p class="profil-alamat">📍 <?= bersihkan($perusahaan['alamat']) ?></p>
            <?php endif; ?>
            <?php if (!empty($perusahaan['no_hp'])): ?>
                <p class="profil-alamat">📞 <?= bersihkan($perusahaan['no_hp']) ?></p>
            <?php endif; ?>
        </div>
        <div class="profil-stats">
            <div class="profil-stat">
                <span class="profil-stat-num"><?= $totalAktif ?></span>
                <span class="profil-stat-lbl">Lowongan Aktif</span>
            </div>
            <div class="profil-stat">
                <span class="profil-stat-num"><?= $totalLowongan ?></span>
                <span class="profil-stat-lbl">Total Lowongan</span>
            </div>
            <div class="profil-stat">
                <span class="profil-stat-num"><?= $totalLamaran ?></span>
                <span class="profil-stat-lbl">Total Pelamar</span>
            </div>
        </div>
    </div>

    <!-- ========================================================
         LOWONGAN AKTIF
         ======================================================== -->
    <div class="profil-section">
        <h2 class="profil-section-title">
            Lowongan Aktif
            <span class="badge-count"><?= count($lowonganAktif) ?></span>
        </h2>

        <?php if (empty($lowonganAktif)): ?>
            <div class="empty-state">
                <p>Belum ada lowongan aktif dari perusahaan ini.</p>
            </div>
        <?php else: ?>
            <div class="profil-jobs">
                <?php foreach ($lowonganAktif as $low): ?>
                <div class="profil-job-card">
                    <div class="profil-job-info">
                        <h3><a href="detail_lowongan.php?id=<?= $low['id'] ?>">
                            <?= bersihkan($low['judul']) ?>
                        </a></h3>
                        <div class="job-meta-row" style="margin-top:6px;">
                            <?= badgeTipeKerja($low['tipe_kerja']) ?>
                            <span class="tag-kategori"><?= bersihkan($low['nama_kategori']) ?></span>
                            <span class="job-lokasi">📍 <?= bersihkan($low['lokasi']) ?></span>
                        </div>
                        <div class="job-meta-row" style="margin-top:4px;">
                            <span class="job-gaji">💰 <?= bersihkan($low['gaji']) ?></span>
                            <span class="job-deadline">🗓 <?= sisaHari($low['deadline']) ?></span>
                        </div>
                    </div>
                    <a href="detail_lowongan.php?id=<?= $low['id'] ?>" class="btn-detail">
                        Lihat Detail
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>