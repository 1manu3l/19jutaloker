<?php
// ============================================================
//  lowongan.php — Daftar semua lowongan aktif + filter & cari
//  ROOT: portal-lowongan/lowongan.php
// ============================================================
session_start();
require_once 'config.php';
require_once 'functions.php';

$judulHalaman = 'Daftar Lowongan';
$baseCSS = '';

// Ambil semua kategori untuk dropdown filter
$semuaKategori = ambilSemua($conn, "SELECT * FROM kategori ORDER BY nama_kategori");

// ============================================================
//  BACA FILTER DARI URL (?keyword=...&kategori=...&tipe=...)
// ============================================================
$keyword   = trim($_GET['keyword']  ?? '');
$kategoriId = sanitasiInt($_GET['kategori'] ?? 0);
$tipeKerja  = bersihkan($_GET['tipe']       ?? '');

// Bangun kondisi WHERE secara dinamis
$kondisi = ["l.status = 'aktif'", "l.deadline >= CURDATE()"];

if (!empty($keyword)) {
    $kw = escape($conn, $keyword);
    $kondisi[] = "(l.judul LIKE '%{$kw}%' OR u.nama LIKE '%{$kw}%' OR l.deskripsi LIKE '%{$kw}%')";
}
if ($kategoriId > 0) {
    $kondisi[] = "l.kategori_id = {$kategoriId}";
}
if (in_array($tipeKerja, ['remote', 'onsite', 'hybrid'])) {
    $tipeEsc   = escape($conn, $tipeKerja);
    $kondisi[] = "l.tipe_kerja = '{$tipeEsc}'";
}

$where = implode(' AND ', $kondisi);

// Ambil lowongan sesuai filter
$semuaLowongan = ambilSemua($conn,
    "SELECT l.*, u.nama AS nama_perusahaan, k.nama_kategori
     FROM lowongan l
     JOIN users u    ON l.user_id     = u.id
     JOIN kategori k ON l.kategori_id = k.id
     WHERE {$where}
     ORDER BY l.created_at DESC"
);

$jumlahHasil = count($semuaLowongan);

require_once 'includes/header.php';
?>

<div class="page-banner">
    <div class="container">
        <h1>Daftar Lowongan</h1>
        <p>Temukan pekerjaan dan magang yang sesuai dengan keahlian kamu</p>
    </div>
</div>

<div class="container layout-filter">

    <!-- ========================================================
         SIDEBAR FILTER (kiri)
         ======================================================== -->
    <aside class="filter-sidebar">
        <form method="GET" action="lowongan.php" id="formFilter">
            <div class="filter-box">
                <h3 class="filter-judul">🔍 Cari Lowongan</h3>
                <input
                    type="text"
                    name="keyword"
                    class="filter-input"
                    placeholder="Posisi atau perusahaan..."
                    value="<?= bersihkan($keyword) ?>"
                >
            </div>

            <div class="filter-box">
                <h3 class="filter-judul">📂 Kategori</h3>
                <select name="kategori" class="filter-select">
                    <option value="0">Semua Kategori</option>
                    <?php foreach ($semuaKategori as $kat): ?>
                    <option value="<?= $kat['id'] ?>"
                        <?= $kategoriId == $kat['id'] ? 'selected' : '' ?>>
                        <?= bersihkan($kat['nama_kategori']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-box">
                <h3 class="filter-judul">🏢 Tipe Kerja</h3>
                <label class="filter-radio">
                    <input type="radio" name="tipe" value=""
                           <?= $tipeKerja === '' ? 'checked' : '' ?>> Semua
                </label>
                <label class="filter-radio">
                    <input type="radio" name="tipe" value="onsite"
                           <?= $tipeKerja === 'onsite' ? 'checked' : '' ?>> On-site
                </label>
                <label class="filter-radio">
                    <input type="radio" name="tipe" value="remote"
                           <?= $tipeKerja === 'remote' ? 'checked' : '' ?>> Remote
                </label>
                <label class="filter-radio">
                    <input type="radio" name="tipe" value="hybrid"
                           <?= $tipeKerja === 'hybrid' ? 'checked' : '' ?>> Hybrid
                </label>
            </div>

            <button type="submit" class="btn-filter">Terapkan Filter</button>
            <a href="lowongan.php" class="btn-reset-filter">Reset</a>
        </form>
    </aside>

    <!-- ========================================================
         DAFTAR LOWONGAN (kanan)
         ======================================================== -->
    <main class="lowongan-list">
        <div class="list-header">
            <p class="list-info">
                Menampilkan <strong><?= $jumlahHasil ?></strong> lowongan
                <?php if (!empty($keyword)): ?>
                    untuk "<strong><?= bersihkan($keyword) ?></strong>"
                <?php endif; ?>
            </p>
        </div>

        <?php if (empty($semuaLowongan)): ?>
            <div class="empty-state">
                <p>😕 Tidak ada lowongan yang sesuai filter.</p>
                <a href="lowongan.php">Lihat semua lowongan</a>
            </div>
        <?php else: ?>
            <?php foreach ($semuaLowongan as $low): ?>
            <div class="job-card-list">
                <div class="job-logo-list">
                    <?= strtoupper(substr($low['nama_perusahaan'], 0, 2)) ?>
                </div>
                <div class="job-detail">
                    <h3 class="job-judul-list">
                        <a href="detail_lowongan.php?id=<?= $low['id'] ?>">
                            <?= bersihkan($low['judul']) ?>
                        </a>
                    </h3>
                    <a href="profil_perusahaan.php?id=<?= $low['user_id'] ?>" class="job-perusahaan">
                        <?= bersihkan($low['nama_perusahaan']) ?>
                    </a>
                    <div class="job-meta-row">
                        <?= badgeTipeKerja($low['tipe_kerja']) ?>
                        <span class="tag-kategori"><?= bersihkan($low['nama_kategori']) ?></span>
                        <span class="job-lokasi">📍 <?= bersihkan($low['lokasi']) ?></span>
                    </div>
                    <div class="job-meta-row" style="margin-top:6px;">
                        <span class="job-gaji">💰 <?= bersihkan($low['gaji']) ?></span>
                        <span class="job-deadline">🗓 <?= sisaHari($low['deadline']) ?></span>
                    </div>
                </div>
                <div class="job-action-list">
                    <a href="detail_lowongan.php?id=<?= $low['id'] ?>" class="btn-detail">Lihat Detail</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

</div>

<?php require_once 'includes/footer.php'; ?>