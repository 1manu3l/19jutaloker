<?php
// ============================================================
//  detail_lowongan.php — Detail satu lowongan (?id=)
//  ROOT: portal-lowongan/detail_lowongan.php
// ============================================================
session_start();
require_once 'config.php';
require_once 'auth_check.php';
require_once 'functions.php';

// Ambil dan validasi id dari URL
$id = sanitasiInt($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('lowongan.php');
}

// Ambil data lowongan beserta nama perusahaan dan kategori
$lowongan = ambilSatu($conn,
    "SELECT l.*, u.nama AS nama_perusahaan, u.no_hp AS hp_perusahaan,
            u.alamat AS alamat_perusahaan, k.nama_kategori
     FROM lowongan l
     JOIN users u    ON l.user_id     = u.id
     JOIN kategori k ON l.kategori_id = k.id
     WHERE l.id = {$id}"
);

// Jika lowongan tidak ditemukan, kembalikan ke daftar
if (!$lowongan) {
    redirect('lowongan.php');
}

// Cek apakah mahasiswa yang login sudah melamar
$sudahLamar = false;
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'mahasiswa') {
    $cek = ambilSatu($conn,
        "SELECT id FROM lamaran
         WHERE user_id = {$_SESSION['user_id']} AND lowongan_id = {$id}"
    );
    $sudahLamar = !empty($cek);
}

// Ambil lowongan lain dari perusahaan yang sama (maks 3)
$lowonganLain = ambilSemua($conn,
    "SELECT l.id, l.judul, l.tipe_kerja, l.gaji, l.deadline
     FROM lowongan l
     WHERE l.user_id = {$lowongan['user_id']}
       AND l.id != {$id}
       AND l.status = 'aktif'
     LIMIT 3"
);

$judulHalaman = bersihkan($lowongan['judul']);
$baseCSS = '';

require_once 'includes/header.php';
?>

<div class="container" style="padding-top:30px; padding-bottom:40px;">

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php">Beranda</a> &rsaquo;
        <a href="lowongan.php">Lowongan</a> &rsaquo;
        <span><?= bersihkan($lowongan['judul']) ?></span>
    </div>

    <div class="layout-detail">

        <!-- ====================================================
             KONTEN UTAMA (kiri)
             ==================================================== -->
        <div class="detail-main">

            <!-- Header lowongan -->
            <div class="detail-header">
                <div class="detail-logo">
                    <?= strtoupper(substr($lowongan['nama_perusahaan'], 0, 2)) ?>
                </div>
                <div class="detail-title-wrap">
                    <h1 class="detail-judul"><?= bersihkan($lowongan['judul']) ?></h1>
                    <a href="profil_perusahaan.php?id=<?= $lowongan['user_id'] ?>" class="detail-perusahaan">
                        <?= bersihkan($lowongan['nama_perusahaan']) ?>
                    </a>
                    <div class="detail-tags">
                        <?= badgeTipeKerja($lowongan['tipe_kerja']) ?>
                        <span class="tag-kategori"><?= bersihkan($lowongan['nama_kategori']) ?></span>
                        <?php if ($lowongan['status'] === 'ditutup'): ?>
                            <span class="tag-tutup">Ditutup</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Info singkat -->
            <div class="detail-info-grid">
                <div class="detail-info-item">
                    <span class="detail-info-label">📍 Lokasi</span>
                    <span><?= bersihkan($lowongan['lokasi']) ?></span>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">💰 Gaji</span>
                    <span><?= bersihkan($lowongan['gaji']) ?></span>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">🗓 Deadline</span>
                    <span><?= formatTanggal($lowongan['deadline']) ?> · <?= sisaHari($lowongan['deadline']) ?></span>
                </div>
                <div class="detail-info-item">
                    <span class="detail-info-label">📅 Diposting</span>
                    <span><?= formatTanggal(date('Y-m-d', strtotime($lowongan['created_at']))) ?></span>
                </div>
            </div>

            <!-- Deskripsi pekerjaan -->
            <div class="detail-section">
                <h2>Deskripsi Pekerjaan</h2>
                <div class="detail-text">
                    <?= nl2br(bersihkan($lowongan['deskripsi'])) ?>
                </div>
            </div>

            <!-- Kualifikasi -->
            <?php if (!empty($lowongan['kualifikasi'])): ?>
            <div class="detail-section">
                <h2>Kualifikasi</h2>
                <div class="detail-text">
                    <?= nl2br(bersihkan($lowongan['kualifikasi'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tombol lamar -->
            <div class="detail-lamar-wrap">
                <?php if ($lowongan['status'] === 'ditutup' || $lowongan['deadline'] < date('Y-m-d')): ?>
                    <button class="btn-lamar-disabled" disabled>Lowongan Sudah Ditutup</button>
                <?php elseif (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="btn-lamar-utama">Login untuk Melamar</a>
                <?php elseif ($_SESSION['role'] === 'mahasiswa'): ?>
                    <?php if ($sudahLamar): ?>
                        <button class="btn-lamar-disabled" disabled>✓ Sudah Dilamar</button>
                        <a href="mahasiswa/riwayat.php" class="btn-lamar-sekunder">Lihat Riwayat</a>
                    <?php else: ?>
                        <a href="mahasiswa/lamar.php?lowongan_id=<?= $id ?>" class="btn-lamar-utama">
                            Lamar Sekarang
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ====================================================
             SIDEBAR (kanan)
             ==================================================== -->
        <aside class="detail-sidebar">

            <!-- Info perusahaan -->
            <div class="sidebar-box">
                <h3>Tentang Perusahaan</h3>
                <div class="sidebar-perusahaan">
                    <div class="sidebar-logo">
                        <?= strtoupper(substr($lowongan['nama_perusahaan'], 0, 2)) ?>
                    </div>
                    <strong><?= bersihkan($lowongan['nama_perusahaan']) ?></strong>
                </div>
                <?php if (!empty($lowongan['alamat_perusahaan'])): ?>
                    <p class="sidebar-info">📍 <?= bersihkan($lowongan['alamat_perusahaan']) ?></p>
                <?php endif; ?>
                <?php if (!empty($lowongan['hp_perusahaan'])): ?>
                    <p class="sidebar-info">📞 <?= bersihkan($lowongan['hp_perusahaan']) ?></p>
                <?php endif; ?>
                <a href="profil_perusahaan.php?id=<?= $lowongan['user_id'] ?>" class="btn-lihat-profil">
                    Lihat Profil Perusahaan →
                </a>
            </div>

            <!-- Lowongan lain dari perusahaan ini -->
            <?php if (!empty($lowonganLain)): ?>
            <div class="sidebar-box">
                <h3>Lowongan Lain dari Perusahaan Ini</h3>
                <?php foreach ($lowonganLain as $ll): ?>
                <div class="sidebar-job-item">
                    <a href="detail_lowongan.php?id=<?= $ll['id'] ?>" class="sidebar-job-title">
                        <?= bersihkan($ll['judul']) ?>
                    </a>
                    <div style="margin-top:3px;">
                        <?= badgeTipeKerja($ll['tipe_kerja']) ?>
                    </div>
                    <p class="sidebar-info">🗓 <?= sisaHari($ll['deadline']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </aside>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>