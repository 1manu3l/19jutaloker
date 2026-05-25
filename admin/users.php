<?php
// ============================================================
//  admin/users.php — Kelola Semua User
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/  ../functions.php';
cekRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    if ($aksi === 'edit') {
        $id    = sanitasiInt($_POST['id']);
        $nama  = escape($conn, $_POST['nama']  ?? '');
        $email = escape($conn, $_POST['email'] ?? '');
        $role  = escape($conn, $_POST['role']  ?? '');
        $hp    = escape($conn, $_POST['no_hp'] ?? '');
        $alamat= escape($conn, $_POST['alamat'] ?? '');
        if (empty($nama) || empty($email)) {
            setPesan('error', 'Nama dan email wajib diisi.');
        } else {
            $cek = ambilSatu($conn, "SELECT id FROM users WHERE email='{$email}' AND id!={$id}");
            if ($cek) {
                setPesan('error', 'Email sudah digunakan user lain.');
            } else {
                query($conn, "UPDATE users SET nama='{$nama}',email='{$email}',role='{$role}',no_hp='{$hp}',alamat='{$alamat}' WHERE id={$id}");
                if (!empty($_POST['password_baru'])) {
                    $hash = escape($conn, password_hash($_POST['password_baru'], PASSWORD_BCRYPT));
                    query($conn, "UPDATE users SET password='{$hash}' WHERE id={$id}");
                }
                setPesan('sukses', '✅ Data user berhasil diperbarui.');
            }
        }
    }
    if ($aksi === 'hapus') {
        $id = sanitasiInt($_POST['id']);
        if ($id === (int)$_SESSION['user_id']) {
            setPesan('error', 'Tidak bisa menghapus akun sendiri.');
        } else {
            query($conn, "DELETE FROM users WHERE id={$id}");
            setPesan('sukses', '🗑️ User berhasil dihapus.');
        }
    }
    redirect('users.php');
}

$filterRole = $_GET['role'] ?? '';
$where = $filterRole ? "WHERE role='" . escape($conn, $filterRole) . "'" : '';
$semuaUsers = ambilSemua($conn, "SELECT * FROM users {$where} ORDER BY role, nama");

// Hitung per role
$jumlahAdmin      = hitungBaris($conn, "SELECT id FROM users WHERE role='admin'");
$jumlahMahasiswa  = hitungBaris($conn, "SELECT id FROM users WHERE role='mahasiswa'");
$jumlahPerusahaan = hitungBaris($conn, "SELECT id FROM users WHERE role='perusahaan'");

$editData = null;
if (isset($_GET['edit'])) {
    $editData = ambilSatu($conn, "SELECT * FROM users WHERE id=".sanitasiInt($_GET['edit']));
}
$namaUser = $_SESSION['nama'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Users — <?= APP_NAME ?></title>
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
    <a href="kategori.php"><span class="nav-icon">📂</span> Kelola Kategori</a>
    <a href="users.php" class="aktif"><span class="nav-icon">👥</span> Kelola Users</a>
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
      <span class="topbar-title">Kelola Users</span>
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
        <h1>👥 Kelola Users</h1>
        <p class="page-header-sub">Manajemen seluruh pengguna platform.</p>
      </div>
    </div>

    <!-- Stat mini role -->
    <div class="role-stats">
      <a href="users.php" class="role-stat-card <?= !$filterRole ? 'aktif':'' ?>">
        <span class="role-stat-num"><?= $jumlahAdmin + $jumlahMahasiswa + $jumlahPerusahaan ?></span>
        <span class="role-stat-lbl">Semua</span>
      </a>
      <a href="users.php?role=admin" class="role-stat-card <?= $filterRole==='admin' ? 'aktif purple':'' ?>">
        <span class="role-stat-num"><?= $jumlahAdmin ?></span>
        <span class="role-stat-lbl">Admin</span>
      </a>
      <a href="users.php?role=mahasiswa" class="role-stat-card <?= $filterRole==='mahasiswa' ? 'aktif blue':'' ?>">
        <span class="role-stat-num"><?= $jumlahMahasiswa ?></span>
        <span class="role-stat-lbl">Mahasiswa</span>
      </a>
      <a href="users.php?role=perusahaan" class="role-stat-card <?= $filterRole==='perusahaan' ? 'aktif yellow':'' ?>">
        <span class="role-stat-num"><?= $jumlahPerusahaan ?></span>
        <span class="role-stat-lbl">Perusahaan</span>
      </a>
    </div>

    <?php tampilPesan(); ?>

    <!-- Form Edit -->
    <?php if ($editData): ?>
    <div class="form-card">
      <h3>✏️ Edit User — <?= bersihkan($editData['nama']) ?></h3>
      <form method="POST">
        <input type="hidden" name="aksi" value="edit">
        <input type="hidden" name="id"   value="<?= $editData['id'] ?>">
        <div class="form-grid-2">
          <div class="form-group">
            <label>Nama Lengkap <span class="wajib">*</span></label>
            <input type="text" name="nama" required value="<?= bersihkan($editData['nama']) ?>">
          </div>
          <div class="form-group">
            <label>Email <span class="wajib">*</span></label>
            <input type="email" name="email" required value="<?= bersihkan($editData['email']) ?>">
          </div>
          <div class="form-group">
            <label>Role</label>
            <select name="role">
              <?php foreach (['admin','mahasiswa','perusahaan'] as $r): ?>
              <option value="<?= $r ?>" <?= $editData['role']===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>No. HP</label>
            <input type="text" name="no_hp" value="<?= bersihkan($editData['no_hp'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Alamat</label>
            <input type="text" name="alamat" value="<?= bersihkan($editData['alamat'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Password Baru <small style="font-weight:400;text-transform:none">(kosongkan jika tidak diubah)</small></label>
            <input type="password" name="password_baru" placeholder="Isi untuk ganti password">
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
          <a href="users.php" class="btn-batal">Batal</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- Filter + Search -->
    <div class="filter-bar">
      <span class="filter-bar-count">Menampilkan <strong><?= count($semuaUsers) ?></strong> user</span>
      <input type="text" id="tableSearch" placeholder="🔍 Cari nama atau email..." class="search-input">
    </div>

    <!-- Tabel -->
    <div class="card">
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>#</th><th>Pengguna</th><th>Email</th><th>Role</th><th>No. HP</th><th>Bergabung</th><th>Aksi</th></tr>
          </thead>
          <tbody>
          <?php if (empty($semuaUsers)): ?>
            <tr class="empty-row"><td colspan="7">
              <div class="empty-state-inline"><span>👥</span><p>Tidak ada user ditemukan.</p></div>
            </td></tr>
          <?php else: ?>
            <?php foreach ($semuaUsers as $i => $u): ?>
            <tr>
              <td><span class="row-num"><?= $i+1 ?></span></td>
              <td>
                <div class="td-user">
                  <div class="td-user-avatar <?= $u['role'] ?>"><?= strtoupper(substr($u['nama'],0,2)) ?></div>
                  <div>
                    <p class="td-title"><?= bersihkan($u['nama']) ?></p>
                    <?php if ($u['id'] == $_SESSION['user_id']): ?>
                      <span class="badge-you">Anda</span>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td class="text-muted"><?= bersihkan($u['email']) ?></td>
              <td>
                <span class="badge badge-<?= $u['role']==='admin'?'admin':($u['role']==='mahasiswa'?'mhs':'prs') ?>">
                  <?= ucfirst($u['role']) ?>
                </span>
              </td>
              <td class="text-muted"><?= bersihkan($u['no_hp'] ?? '-') ?></td>
              <td class="text-muted"><?= formatTanggal(date('Y-m-d', strtotime($u['created_at']))) ?></td>
              <td>
                <div class="aksi-col">
                  <a href="users.php?edit=<?= $u['id'] ?>" class="btn-edit">✏️ Edit</a>
                  <?php if ($u['id'] != $_SESSION['user_id']): ?>
                  <form method="POST" style="display:inline"
                        onsubmit="return konfirmHapus('Hapus user <?= bersihkan(addslashes($u['nama'])) ?>? Semua data terkait akan ikut terhapus.')">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id"   value="<?= $u['id'] ?>">
                    <button type="submit" class="btn-hapus">🗑️ Hapus</button>
                  </form>
                  <?php else: ?>
                  <span class="text-muted" style="font-size:11px">—</span>
                  <?php endif; ?>
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
.role-stats { display:flex; gap:12px; margin-bottom:24px; flex-wrap:wrap; }
.role-stat-card { display:flex; flex-direction:column; align-items:center; gap:3px; padding:14px 28px; background:#fff; border:1.5px solid var(--border); border-radius:var(--radius-lg); cursor:pointer; transition:all var(--transition); text-align:center; }
.role-stat-card:hover { border-color:var(--primary); }
.role-stat-card.aktif { background:var(--primary-light); border-color:var(--primary); }
.role-stat-card.aktif.purple { background:var(--primary-light); }
.role-stat-card.aktif.blue   { background:var(--info-bg); border-color:#93c5fd; }
.role-stat-card.aktif.yellow { background:var(--warning-bg); border-color:#fcd34d; }
.role-stat-num { font-size:22px; font-weight:800; color:var(--text-primary); }
.role-stat-lbl { font-size:12px; color:var(--text-muted); }
.td-user { display:flex; align-items:center; gap:10px; }
.td-user-avatar { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; flex-shrink:0; }
.td-user-avatar.admin      { background:#ede9fe; color:#5b21b6; }
.td-user-avatar.mahasiswa  { background:var(--info-bg); color:#1e40af; }
.td-user-avatar.perusahaan { background:var(--warning-bg); color:#92400e; }
.badge-you { font-size:10px; background:#d1fae5; color:#065f46; padding:1px 8px; border-radius:999px; font-weight:700; }
</style>
<script src="../assets/js/dashboard.js"></script>
<script>
document.getElementById('tableSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.data-table tbody tr:not(.empty-row)').forEach(tr => {
    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
</body>
</html>