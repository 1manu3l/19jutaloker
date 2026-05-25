<?php
// ============================================================
//  admin/index.php — Dashboard Admin
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../functions.php';
cekRole('admin');

// Statistik utama
$totalLowongan   = hitungBaris($conn, "SELECT id FROM lowongan");
$lowonganAktif   = hitungBaris($conn, "SELECT id FROM lowongan WHERE status='aktif'");
$totalUser       = hitungBaris($conn, "SELECT id FROM users");
$totalMahasiswa  = hitungBaris($conn, "SELECT id FROM users WHERE role='mahasiswa'");
$totalPerusahaan = hitungBaris($conn, "SELECT id FROM users WHERE role='perusahaan'");
$totalLamaran    = hitungBaris($conn, "SELECT id FROM lamaran");
$lamaranPending  = hitungBaris($conn, "SELECT id FROM lamaran WHERE status='pending'");
$totalKategori   = hitungBaris($conn, "SELECT id FROM kategori");

// 5 lowongan terbaru
$lowonganTerbaru = ambilSemua($conn,
    "SELECT l.*, u.nama AS nama_perusahaan, k.nama_kategori
     FROM lowongan l
     JOIN users u ON l.user_id = u.id
     JOIN kategori k ON l.kategori_id = k.id
     ORDER BY l.created_at DESC LIMIT 5"
);

// 5 lamaran terbaru
$lamaranTerbaru = ambilSemua($conn,
    "SELECT la.*, u.nama AS nama_mahasiswa, lo.judul AS judul_lowongan,
            p.nama AS nama_perusahaan
     FROM lamaran la
     JOIN users u    ON la.user_id = u.id
     JOIN lowongan lo ON la.lowongan_id = lo.id
     JOIN users p    ON lo.user_id = p.id
     ORDER BY la.created_at DESC LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin — <?= APP_NAME ?></title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="dash-layout">

<?php
// Sidebar
$halamanAktif = 'index.php';
$namaUser = $_SESSION['nama'];
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <span class="sidebar-brand-icon">💼</span> <?= APP_NAME ?>
  </div>
  <p class="sidebar-role">Panel Admin</p>
  <nav class="sidebar-nav">
    <a href="index.php"    class="aktif"><span class="nav-icon">📊</span> Dashboard</a>
    <a href="lowongan.php"><span class="nav-icon">📋</span> Kelola Lowongan</a>
    <a href="kategori.php"><span class="nav-icon">📂</span> Kelola Kategori</a>
    <a href="users.php">   <span class="nav-icon">👥</span> Kelola Users</a>
    <a href="lamaran.php"> <span class="nav-icon">📨</span> Semua Lamaran
      <?php if ($lamaranPending > 0): ?>
        <span class="nav-badge"><?= $lamaranPending ?></span>
      <?php endif; ?>
    </a>
    <hr class="sidebar-divider">
    <a href="../index.php"><span class="nav-icon">🌐</span> Lihat Situs</a>
    <a href="../logout.php" class="nav-logout"><span class="nav-icon">🚪</span> Logout</a>
  </nav>
  <div class="sidebar-user">
    <div class="sidebar-avatar"><?= strtoupper(substr($namaUser, 0, 2)) ?></div>
    <div>
      <p class="sidebar-user-name"><?= bersihkan($namaUser) ?></p>
      <p class="sidebar-user-role">Administrator</p>
    </div>
  </div>
</aside>

<div class="dash-main">
  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar-left">
      <button class="topbar-mobile-toggle">☰</button>
      <span class="topbar-title">Dashboard</span>
    </div>
    <div class="topbar-right">
      <div class="topbar-date"><?= date('l, d F Y') ?></div>
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr($namaUser, 0, 2)) ?></div>
        <?= bersihkan($namaUser) ?>
      </div>
    </div>
  </header>

  <div class="dash-content">

    <!-- Greeting -->
    <div class="dash-greeting">
      <div>
        <h1 class="dash-greeting-title">Selamat datang, <?= bersihkan(explode(' ', $namaUser)[0]) ?>! 👋</h1>
        <p class="dash-greeting-sub">Berikut ringkasan aktivitas platform hari ini.</p>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-card-icon purple">📋</div>
        <p class="stat-num"><?= $totalLowongan ?></p>
        <p class="stat-label">Total Lowongan</p>
        <div class="stat-sub"><?= $lowonganAktif ?> aktif sekarang</div>
        <div class="stat-card-bar purple"></div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon blue">👥</div>
        <p class="stat-num"><?= $totalUser ?></p>
        <p class="stat-label">Total Pengguna</p>
        <div class="stat-sub"><?= $totalMahasiswa ?> mahasiswa · <?= $totalPerusahaan ?> perusahaan</div>
        <div class="stat-card-bar blue"></div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon green">📨</div>
        <p class="stat-num"><?= $totalLamaran ?></p>
        <p class="stat-label">Total Lamaran</p>
        <div class="stat-sub"><?= $lamaranPending ?> menunggu review</div>
        <div class="stat-card-bar green"></div>
      </div>
      <div class="stat-card">
        <div class="stat-card-icon yellow">📂</div>
        <p class="stat-num"><?= $totalKategori ?></p>
        <p class="stat-label">Kategori</p>
        <div class="stat-sub">Bidang pekerjaan tersedia</div>
        <div class="stat-card-bar yellow"></div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <a href="lowongan.php" class="qa-btn qa-purple">
        <span class="qa-icon">➕</span>
        <span>Tambah Lowongan</span>
      </a>
      <a href="kategori.php" class="qa-btn qa-blue">
        <span class="qa-icon">📂</span>
        <span>Tambah Kategori</span>
      </a>
      <a href="users.php" class="qa-btn qa-green">
        <span class="qa-icon">👤</span>
        <span>Kelola Users</span>
      </a>
      <a href="lamaran.php" class="qa-btn qa-yellow">
        <span class="qa-icon">📨</span>
        <span>Lihat Lamaran</span>
      </a>
    </div>

    <!-- Tabel dua kolom -->
    <div class="dash-grid-2">

      <!-- Lowongan Terbaru -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">📋 Lowongan Terbaru</h3>
          <a href="lowongan.php" class="card-link">Lihat semua →</a>
        </div>
        <div class="card-body p0">
          <table class="data-table">
            <thead>
              <tr><th>Judul</th><th>Perusahaan</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php if (empty($lowonganTerbaru)): ?>
                <tr class="empty-row"><td colspan="3">Belum ada lowongan</td></tr>
              <?php else: ?>
                <?php foreach ($lowonganTerbaru as $l): ?>
                <tr>
                  <td>
                    <p class="td-title"><?= bersihkan($l['judul']) ?></p>
                    <p class="td-sub"><?= bersihkan($l['nama_kategori']) ?></p>
                  </td>
                  <td><?= bersihkan($l['nama_perusahaan']) ?></td>
                  <td>
                    <span class="badge <?= $l['status']==='aktif' ? 'badge-aktif' : 'badge-tutup' ?>">
                      <?= ucfirst($l['status']) ?>
                    </span>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Lamaran Terbaru -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">📨 Lamaran Terbaru</h3>
          <a href="lamaran.php" class="card-link">Lihat semua →</a>
        </div>
        <div class="card-body p0">
          <table class="data-table">
            <thead>
              <tr><th>Pelamar</th><th>Lowongan</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php if (empty($lamaranTerbaru)): ?>
                <tr class="empty-row"><td colspan="3">Belum ada lamaran</td></tr>
              <?php else: ?>
                <?php foreach ($lamaranTerbaru as $la): ?>
                <tr>
                  <td>
                    <p class="td-title"><?= bersihkan($la['nama_mahasiswa']) ?></p>
                    <p class="td-sub"><?= bersihkan($la['nama_perusahaan']) ?></p>
                  </td>
                  <td><?= potongTeks(bersihkan($la['judul_lowongan']), 30) ?></td>
                  <td>
                    <span class="badge badge-<?= $la['status'] ?>">
                      <?= ucfirst($la['status']) ?>
                    </span>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- end dash-grid-2 -->

  </div><!-- end dash-content -->
</div><!-- end dash-main -->
</div><!-- end dash-layout -->

<style>
.topbar-date { font-size: 13px; color: var(--text-muted); }
.dash-greeting { margin-bottom: 24px; }
.dash-greeting-title { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 4px; }
.dash-greeting-sub { font-size: 14px; color: var(--text-muted); }
.stat-sub { font-size: 11px; color: var(--text-muted); margin-top: 4px; }
.quick-actions { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
.qa-btn {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 20px; border-radius: var(--radius-md);
  font-size: 13px; font-weight: 700; transition: all var(--transition);
  flex: 1; min-width: 140px;
}
.qa-btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.qa-icon { font-size: 18px; }
.qa-purple { background: var(--primary-light); color: var(--primary); border: 1.5px solid #c4b5fd; }
.qa-blue   { background: var(--info-bg);       color: #1e40af;         border: 1.5px solid #93c5fd; }
.qa-green  { background: var(--success-bg);    color: #065f46;         border: 1.5px solid #6ee7b7; }
.qa-yellow { background: var(--warning-bg);    color: #92400e;         border: 1.5px solid #fcd34d; }
.qa-purple:hover { background: var(--primary); color: #fff; }
.qa-blue:hover   { background: #3b82f6; color: #fff; }
.qa-green:hover  { background: var(--success); color: #fff; }
.qa-yellow:hover { background: var(--accent);  color: #fff; }
.dash-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.card-link { font-size: 13px; color: var(--primary); font-weight: 600; }
.card-link:hover { text-decoration: underline; }
.card-body.p0 { padding: 0; }
.td-title { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.td-sub   { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
@media (max-width: 900px) { .dash-grid-2 { grid-template-columns: 1fr; } }
@media (max-width: 600px) { .quick-actions { flex-direction: column; } .qa-btn { min-width: 100%; } }
</style>
<script src="../assets/js/dashboard.js"></script>
</body>
</html>