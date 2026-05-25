<?php
// ============================================================
//  admin/kategori.php — Kelola Kategori (CRUD)
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../functions.php';
cekRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi === 'tambah' || $aksi === 'edit') {
        $nama = escape($conn, $_POST['nama_kategori'] ?? '');
        $desk = escape($conn, $_POST['deskripsi']     ?? '');
        if (empty($nama)) {
            setPesan('error', 'Nama kategori wajib diisi.');
        } else {
            if ($aksi === 'tambah') {
                query($conn, "INSERT INTO kategori (nama_kategori, deskripsi) VALUES ('{$nama}','{$desk}')");
                setPesan('sukses', '✅ Kategori berhasil ditambahkan.');
            } else {
                $id = sanitasiInt($_POST['id']);
                query($conn, "UPDATE kategori SET nama_kategori='{$nama}', deskripsi='{$desk}' WHERE id={$id}");
                setPesan('sukses', '✅ Kategori berhasil diperbarui.');
            }
        }
    }
    if ($aksi === 'hapus') {
        $id = sanitasiInt($_POST['id']);
        query($conn, "DELETE FROM kategori WHERE id={$id}");
        setPesan('sukses', '🗑️ Kategori berhasil dihapus.');
    }
    redirect('kategori.php');
}

$semuaKategori = ambilSemua($conn,
    "SELECT k.*, COUNT(l.id) AS jml_lowongan
     FROM kategori k
     LEFT JOIN lowongan l ON k.id = l.kategori_id
     GROUP BY k.id ORDER BY k.nama_kategori"
);
$editData = null;
if (isset($_GET['edit'])) {
    $editData = ambilSatu($conn, "SELECT * FROM kategori WHERE id=".sanitasiInt($_GET['edit']));
}
$namaUser = $_SESSION['nama'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Kategori — <?= APP_NAME ?></title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="dash-layout">

<aside class="sidebar">
  <div class="sidebar-brand"><span class="sidebar-brand-icon">💼</span> <?= APP_NAME ?></div>
  <p class="sidebar-role">Panel Admin</p>
  <nav class="sidebar-nav">
    <a href="index.php">   <span class="nav-icon">📊</span> Dashboard</a>
    <a href="lowongan.php"><span class="nav-icon">📋</span> Kelola Lowongan</a>
    <a href="kategori.php" class="aktif"><span class="nav-icon">📂</span> Kelola Kategori</a>
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
      <span class="topbar-title">Kelola Kategori</span>
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
        <h1>📂 Kelola Kategori</h1>
        <p class="page-header-sub">Kelola kategori bidang pekerjaan untuk lowongan.</p>
      </div>
      <?php if (!$editData): ?>
      <button class="btn-tambah" onclick="toggleForm()">+ Tambah Kategori</button>
      <?php endif; ?>
    </div>

    <?php tampilPesan(); ?>

    <div class="layout-crud">

      <!-- Form -->
      <div class="form-card" id="formPanel" style="<?= $editData ? '' : 'display:none' ?>">
        <h3><?= $editData ? '✏️ Edit Kategori' : '➕ Tambah Kategori Baru' ?></h3>
        <form method="POST">
          <input type="hidden" name="aksi" value="<?= $editData ? 'edit' : 'tambah' ?>">
          <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?= $editData['id'] ?>">
          <?php endif; ?>
          <div class="form-group">
            <label>Nama Kategori <span class="wajib">*</span></label>
            <input type="text" name="nama_kategori" required
                   placeholder="Contoh: Teknologi Informasi"
                   value="<?= bersihkan($editData['nama_kategori'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="3"
                      placeholder="Deskripsi singkat kategori ini..."><?= bersihkan($editData['deskripsi'] ?? '') ?></textarea>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-submit">
              <?= $editData ? '💾 Simpan Perubahan' : '➕ Tambah Kategori' ?>
            </button>
            <a href="kategori.php" class="btn-batal">Batal</a>
          </div>
        </form>
      </div>

      <!-- Tabel -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Daftar Kategori
            <span class="count-badge"><?= count($semuaKategori) ?></span>
          </h3>
          <div class="card-header-search">
            <input type="text" id="tableSearch" placeholder="🔍 Cari kategori..." class="search-input">
          </div>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Kategori</th>
                <th>Deskripsi</th>
                <th>Lowongan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($semuaKategori)): ?>
              <tr class="empty-row"><td colspan="5">
                <div class="empty-state-inline">
                  <span>📂</span>
                  <p>Belum ada kategori. Tambahkan kategori pertama!</p>
                </div>
              </td></tr>
            <?php else: ?>
              <?php foreach ($semuaKategori as $i => $kat): ?>
              <tr>
                <td><span class="row-num"><?= $i+1 ?></span></td>
                <td>
                  <div class="td-with-icon">
                    <div class="td-icon-box purple">📂</div>
                    <span class="td-bold"><?= bersihkan($kat['nama_kategori']) ?></span>
                  </div>
                </td>
                <td class="text-muted"><?= potongTeks(bersihkan($kat['deskripsi'] ?? '-'), 55) ?></td>
                <td>
                  <span class="badge badge-count"><?= $kat['jml_lowongan'] ?> lowongan</span>
                </td>
                <td>
                  <div class="aksi-col">
                    <a href="kategori.php?edit=<?= $kat['id'] ?>" class="btn-edit">✏️ Edit</a>
                    <form method="POST" style="display:inline"
                          onsubmit="return konfirmHapus('Hapus kategori ini? Semua lowongan dalam kategori ini akan terpengaruh.')">
                      <input type="hidden" name="aksi" value="hapus">
                      <input type="hidden" name="id"   value="<?= $kat['id'] ?>">
                      <button type="submit" class="btn-hapus">🗑️ Hapus</button>
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
</div>

<script src="../assets/js/dashboard.js"></script>
<script>
function toggleForm() {
  const p = document.getElementById('formPanel');
  p.style.display = p.style.display === 'none' || p.style.display === '' ? 'block' : 'none';
  if (p.style.display === 'block') p.scrollIntoView({behavior:'smooth'});
}
// Search tabel
document.getElementById('tableSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.data-table tbody tr:not(.empty-row)').forEach(tr => {
    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
</body>
</html>