<?php
// ============================================================
//  admin/lowongan.php — Kelola Semua Lowongan (CRUD)
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../functions.php';
cekRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi === 'tambah' || $aksi === 'edit') {
        $userId   = sanitasiInt($_POST['user_id']);
        $katId    = sanitasiInt($_POST['kategori_id']);
        $judul    = escape($conn, $_POST['judul']       ?? '');
        $desk     = escape($conn, $_POST['deskripsi']   ?? '');
        $kual     = escape($conn, $_POST['kualifikasi'] ?? '');
        $lokasi   = escape($conn, $_POST['lokasi']      ?? '');
        $tipe     = escape($conn, $_POST['tipe_kerja']  ?? 'onsite');
        $gaji     = escape($conn, $_POST['gaji']        ?? '');
        $deadline = escape($conn, $_POST['deadline']    ?? '');
        $status   = escape($conn, $_POST['status']      ?? 'aktif');

        if (empty($judul) || !$userId || !$katId || empty($deadline)) {
            setPesan('error', 'Judul, perusahaan, kategori, dan deadline wajib diisi.');
        } else {
            if ($aksi === 'tambah') {
                query($conn, "INSERT INTO lowongan (user_id,kategori_id,judul,deskripsi,kualifikasi,lokasi,tipe_kerja,gaji,deadline,status)
                    VALUES ({$userId},{$katId},'{$judul}','{$desk}','{$kual}','{$lokasi}','{$tipe}','{$gaji}','{$deadline}','{$status}')");
                setPesan('sukses', '✅ Lowongan berhasil ditambahkan.');
            } else {
                $id = sanitasiInt($_POST['id']);
                query($conn, "UPDATE lowongan SET user_id={$userId},kategori_id={$katId},judul='{$judul}',
                    deskripsi='{$desk}',kualifikasi='{$kual}',lokasi='{$lokasi}',tipe_kerja='{$tipe}',
                    gaji='{$gaji}',deadline='{$deadline}',status='{$status}' WHERE id={$id}");
                setPesan('sukses', '✅ Lowongan berhasil diperbarui.');
            }
        }
    }
    if ($aksi === 'hapus') {
        $id = sanitasiInt($_POST['id']);
        query($conn, "DELETE FROM lowongan WHERE id={$id}");
        setPesan('sukses', '🗑️ Lowongan berhasil dihapus.');
    }
    if ($aksi === 'toggle_status') {
        $id = sanitasiInt($_POST['id']);
        $s  = escape($conn, $_POST['status_baru']);
        query($conn, "UPDATE lowongan SET status='{$s}' WHERE id={$id}");
        setPesan('sukses', '✅ Status lowongan diperbarui.');
    }
    redirect('lowongan.php');
}

// Filter
$filterStatus = $_GET['status'] ?? '';
$filterKat    = sanitasiInt($_GET['kategori'] ?? 0);
$kondisi = ['1=1'];
if ($filterStatus) $kondisi[] = "l.status='" . escape($conn, $filterStatus) . "'";
if ($filterKat)    $kondisi[] = "l.kategori_id={$filterKat}";
$where = implode(' AND ', $kondisi);

$semuaLowongan = ambilSemua($conn,
    "SELECT l.*, u.nama AS nama_perusahaan, k.nama_kategori
     FROM lowongan l JOIN users u ON l.user_id=u.id JOIN kategori k ON l.kategori_id=k.id
     WHERE {$where} ORDER BY l.created_at DESC"
);
$semuaPerusahaan = ambilSemua($conn, "SELECT id,nama FROM users WHERE role='perusahaan' ORDER BY nama");
$semuaKategori   = ambilSemua($conn, "SELECT * FROM kategori ORDER BY nama_kategori");

$editData = null;
if (isset($_GET['edit'])) {
    $editData = ambilSatu($conn, "SELECT * FROM lowongan WHERE id=".sanitasiInt($_GET['edit']));
}
$namaUser = $_SESSION['nama'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Lowongan — <?= APP_NAME ?></title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="dash-layout">

<aside class="sidebar">
  <div class="sidebar-brand"><span class="sidebar-brand-icon">💼</span> <?= APP_NAME ?></div>
  <p class="sidebar-role">Panel Admin</p>
  <nav class="sidebar-nav">
    <a href="index.php">   <span class="nav-icon">📊</span> Dashboard</a>
    <a href="lowongan.php" class="aktif"><span class="nav-icon">📋</span> Kelola Lowongan</a>
    <a href="kategori.php"><span class="nav-icon">📂</span> Kelola Kategori</a>
    <a href="users.php">   <span class="nav-icon">👥</span> Kelola Users</a>
    <a href="lamaran.php"> <span class="nav-icon">📨</span> Semua Lamaran</a>
    <hr class="sidebar-divider">
    <a href="../index.php"><span class="nav-icon">🌐</span> Lihat Situs</a>
    <a href="../logout.php" class="nav-logout"><span class="nav-icon">🚪</span> Logout</a>
  </nav>
  <div class="sidebar-user">
    <div class="sidebar-avatar"><?= strtoupper(substr($namaUser,0,2)) ?></div>
    <div>
      <p class="sidebar-user-name"><?= bersihkan($namaUser) ?></p>
      <p class="sidebar-user-role">Administrator</p>
    </div>
  </div>
</aside>

<div class="dash-main">
  <header class="topbar">
    <div class="topbar-left">
      <button class="topbar-mobile-toggle">☰</button>
      <span class="topbar-title">Kelola Lowongan</span>
    </div>
    <div class="topbar-right">
      <div class="topbar-user">
        <div class="topbar-avatar"><?= strtoupper(substr($namaUser,0,2)) ?></div>
        <?= bersihkan($namaUser) ?>
      </div>
    </div>
  </header>

  <div class="dash-content">

    <div class="page-header">
      <div>
        <h1>📋 Kelola Lowongan</h1>
        <p class="page-header-sub">Total <strong><?= count($semuaLowongan) ?></strong> lowongan ditemukan.</p>
      </div>
      <button class="btn-tambah" onclick="toggleForm()">+ Tambah Lowongan</button>
    </div>

    <?php tampilPesan(); ?>

    <!-- Form Tambah/Edit -->
    <div class="form-card" id="formPanel" style="<?= $editData ? '' : 'display:none' ?>">
      <h3><?= $editData ? '✏️ Edit Lowongan' : '➕ Tambah Lowongan Baru' ?></h3>
      <form method="POST">
        <input type="hidden" name="aksi" value="<?= $editData ? 'edit' : 'tambah' ?>">
        <?php if ($editData): ?>
          <input type="hidden" name="id" value="<?= $editData['id'] ?>">
        <?php endif; ?>
        <div class="form-grid-2">
          <div class="form-group">
            <label>Judul Lowongan <span class="wajib">*</span></label>
            <input type="text" name="judul" required placeholder="Contoh: UI/UX Designer"
                   value="<?= bersihkan($editData['judul'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Perusahaan <span class="wajib">*</span></label>
            <select name="user_id" required>
              <option value="">-- Pilih Perusahaan --</option>
              <?php foreach ($semuaPerusahaan as $p): ?>
              <option value="<?= $p['id'] ?>" <?= ($editData['user_id'] ?? 0) == $p['id'] ? 'selected':'' ?>>
                <?= bersihkan($p['nama']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Kategori <span class="wajib">*</span></label>
            <select name="kategori_id" required>
              <option value="">-- Pilih Kategori --</option>
              <?php foreach ($semuaKategori as $k): ?>
              <option value="<?= $k['id'] ?>" <?= ($editData['kategori_id'] ?? 0) == $k['id'] ? 'selected':'' ?>>
                <?= bersihkan($k['nama_kategori']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Lokasi</label>
            <input type="text" name="lokasi" placeholder="Contoh: Manado, Sulawesi Utara"
                   value="<?= bersihkan($editData['lokasi'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Tipe Kerja</label>
            <select name="tipe_kerja">
              <?php foreach (['onsite'=>'On-site','remote'=>'Remote','hybrid'=>'Hybrid'] as $v=>$lbl): ?>
              <option value="<?= $v ?>" <?= ($editData['tipe_kerja'] ?? 'onsite') === $v ? 'selected':'' ?>>
                <?= $lbl ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Gaji</label>
            <input type="text" name="gaji" placeholder="Contoh: Rp 3.000.000 - Rp 5.000.000"
                   value="<?= bersihkan($editData['gaji'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Deadline <span class="wajib">*</span></label>
            <input type="date" name="deadline" required value="<?= $editData['deadline'] ?? '' ?>">
          </div>
          <div class="form-group">
            <label>Status</label>
            <select name="status">
              <option value="aktif"   <?= ($editData['status'] ?? 'aktif') === 'aktif'   ? 'selected':'' ?>>Aktif</option>
              <option value="ditutup" <?= ($editData['status'] ?? '')      === 'ditutup' ? 'selected':'' ?>>Ditutup</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Deskripsi Pekerjaan</label>
          <textarea name="deskripsi" rows="4" placeholder="Jelaskan tanggung jawab dan detail pekerjaan..."><?= bersihkan($editData['deskripsi'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Kualifikasi</label>
          <textarea name="kualifikasi" rows="3" placeholder="Tuliskan syarat dan kualifikasi pelamar..."><?= bersihkan($editData['kualifikasi'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-submit"><?= $editData ? '💾 Simpan Perubahan' : '➕ Tambah Lowongan' ?></button>
          <a href="lowongan.php" class="btn-batal">Batal</a>
        </div>
      </form>
    </div>

    <!-- Filter bar -->
    <div class="filter-bar">
      <form method="GET" class="filter-bar-form">
        <select name="status" onchange="this.form.submit()" class="filter-select-sm">
          <option value="">Semua Status</option>
          <option value="aktif"   <?= $filterStatus==='aktif'   ? 'selected':'' ?>>Aktif</option>
          <option value="ditutup" <?= $filterStatus==='ditutup' ? 'selected':'' ?>>Ditutup</option>
        </select>
        <select name="kategori" onchange="this.form.submit()" class="filter-select-sm">
          <option value="0">Semua Kategori</option>
          <?php foreach ($semuaKategori as $k): ?>
          <option value="<?= $k['id'] ?>" <?= $filterKat==$k['id'] ? 'selected':'' ?>>
            <?= bersihkan($k['nama_kategori']) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <?php if ($filterStatus || $filterKat): ?>
          <a href="lowongan.php" class="btn-reset-sm">✕ Reset</a>
        <?php endif; ?>
      </form>
      <input type="text" id="tableSearch" placeholder="🔍 Cari lowongan..." class="search-input">
    </div>

    <!-- Tabel -->
    <div class="card">
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>#</th><th>Lowongan</th><th>Perusahaan</th><th>Tipe</th><th>Deadline</th><th>Status</th><th>Aksi</th></tr>
          </thead>
          <tbody>
          <?php if (empty($semuaLowongan)): ?>
            <tr class="empty-row"><td colspan="7">
              <div class="empty-state-inline">
                <span>📋</span><p>Tidak ada lowongan ditemukan.</p>
              </div>
            </td></tr>
          <?php else: ?>
            <?php foreach ($semuaLowongan as $i => $low): ?>
            <tr>
              <td><span class="row-num"><?= $i+1 ?></span></td>
              <td>
                <p class="td-title"><?= bersihkan($low['judul']) ?></p>
                <p class="td-sub"><?= bersihkan($low['nama_kategori']) ?> · <?= bersihkan($low['lokasi'] ?: '-') ?></p>
              </td>
              <td>
                <div class="td-company">
                  <div class="td-company-logo"><?= strtoupper(substr($low['nama_perusahaan'],0,2)) ?></div>
                  <span><?= bersihkan($low['nama_perusahaan']) ?></span>
                </div>
              </td>
              <td>
                <?php
                $tipeClass = ['remote'=>'tag-remote','onsite'=>'tag-onsite','hybrid'=>'tag-hybrid'];
                $tipeLabel = ['remote'=>'Remote','onsite'=>'On-site','hybrid'=>'Hybrid'];
                ?>
                <span class="tag <?= $tipeClass[$low['tipe_kerja']] ?? '' ?>"><?= $tipeLabel[$low['tipe_kerja']] ?? $low['tipe_kerja'] ?></span>
              </td>
              <td>
                <p class="td-title"><?= formatTanggal($low['deadline']) ?></p>
                <p class="td-sub"><?= sisaHari($low['deadline']) ?></p>
              </td>
              <td>
                <span class="badge <?= $low['status']==='aktif' ? 'badge-aktif':'badge-tutup' ?>">
                  <?= ucfirst($low['status']) ?>
                </span>
              </td>
              <td>
                <div class="aksi-col">
                  <a href="lowongan.php?edit=<?= $low['id'] ?>" class="btn-edit">✏️ Edit</a>
                  <!-- Toggle status -->
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="aksi" value="toggle_status">
                    <input type="hidden" name="id" value="<?= $low['id'] ?>">
                    <input type="hidden" name="status_baru" value="<?= $low['status']==='aktif' ? 'ditutup':'aktif' ?>">
                    <button type="submit" class="btn-toggle-<?= $low['status']==='aktif' ? 'off':'on' ?>">
                      <?= $low['status']==='aktif' ? '🔒 Tutup':'🔓 Buka' ?>
                    </button>
                  </form>
                  <form method="POST" style="display:inline"
                        onsubmit="return konfirmHapus('Hapus lowongan ini? Semua lamaran terkait juga akan terhapus.')">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id"   value="<?= $low['id'] ?>">
                    <button type="submit" class="btn-hapus">🗑️</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
</div>

<style>
.tag { font-size:11px; font-weight:600; padding:3px 10px; border-radius:999px; }
.tag-remote { background:#d1fae5; color:#065f46; }
.tag-onsite { background:#dbeafe; color:#1e40af; }
.tag-hybrid { background:#fef3c7; color:#92400e; }
.btn-toggle-off { font-size:11px; font-weight:600; padding:4px 10px; background:#fef3c7; color:#92400e; border:none; border-radius:6px; cursor:pointer; }
.btn-toggle-on  { font-size:11px; font-weight:600; padding:4px 10px; background:#d1fae5; color:#065f46; border:none; border-radius:6px; cursor:pointer; }
.btn-toggle-off:hover { background:#f59e0b; color:#fff; }
.btn-toggle-on:hover  { background:#10b981; color:#fff; }
</style>
<script src="../assets/js/dashboard.js"></script>
<script>
function toggleForm() {
  const p = document.getElementById('formPanel');
  p.style.display = p.style.display === 'none' || p.style.display === '' ? 'block' : 'none';
  if (p.style.display === 'block') p.scrollIntoView({behavior:'smooth'});
}
document.getElementById('tableSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.data-table tbody tr:not(.empty-row)').forEach(tr => {
    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
</body>
</html>