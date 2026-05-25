<?php
// ============================================================
//  admin/lamaran.php — Semua Lamaran + Ubah Status
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../functions.php';
cekRole('admin');

// Ubah status lamaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'ubah_status') {
    $id     = sanitasiInt($_POST['id']);
    $status = escape($conn, $_POST['status'] ?? '');
    if (in_array($status, ['pending','diterima','ditolak'])) {
        query($conn, "UPDATE lamaran SET status='{$status}' WHERE id={$id}");
        setPesan('sukses', '✅ Status lamaran berhasil diperbarui.');
    }
    redirect('lamaran.php' . ($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''));
}

// Filter
$filterStatus = $_GET['status'] ?? '';
$filterKondisi = $filterStatus ? "AND la.status='" . escape($conn, $filterStatus) . "'" : '';

$semuaLamaran = ambilSemua($conn,
    "SELECT la.*, u.nama AS nama_mahasiswa, u.email AS email_mahasiswa,
            lo.judul AS judul_lowongan, lo.deadline,
            p.nama AS nama_perusahaan
     FROM lamaran la
     JOIN users u     ON la.user_id     = u.id
     JOIN lowongan lo ON la.lowongan_id = lo.id
     JOIN users p     ON lo.user_id     = p.id
     WHERE 1=1 {$filterKondisi}
     ORDER BY la.created_at DESC"
);

// Hitung per status
$jmlPending  = hitungBaris($conn, "SELECT id FROM lamaran WHERE status='pending'");
$jmlDiterima = hitungBaris($conn, "SELECT id FROM lamaran WHERE status='diterima'");
$jmlDitolak  = hitungBaris($conn, "SELECT id FROM lamaran WHERE status='ditolak'");

// Detail lamaran (modal)
$detailLamaran = null;
if (isset($_GET['detail'])) {
    $detailLamaran = ambilSatu($conn,
        "SELECT la.*, u.nama AS nama_mahasiswa, u.email AS email_mahasiswa,
                u.no_hp AS hp_mahasiswa,
                lo.judul AS judul_lowongan, lo.gaji, lo.lokasi, lo.tipe_kerja,
                p.nama AS nama_perusahaan
         FROM lamaran la
         JOIN users u     ON la.user_id     = u.id
         JOIN lowongan lo ON la.lowongan_id = lo.id
         JOIN users p     ON lo.user_id     = p.id
         WHERE la.id=".sanitasiInt($_GET['detail'])
    );
}

$namaUser = $_SESSION['nama'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Semua Lamaran — <?= APP_NAME ?></title>
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
    <a href="users.php">   <span class="nav-icon">👥</span> Kelola Users</a>
    <a href="lamaran.php" class="aktif"><span class="nav-icon">📨</span> Semua Lamaran
      <?php if ($jmlPending > 0): ?>
        <span class="nav-badge"><?= $jmlPending ?></span>
      <?php endif; ?>
    </a>
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
      <span class="topbar-title">Semua Lamaran</span>
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
        <h1>📨 Semua Lamaran</h1>
        <p class="page-header-sub">Monitor dan kelola status seluruh lamaran masuk.</p>
      </div>
    </div>

    <!-- Status tabs -->
    <div class="status-tabs">
      <a href="lamaran.php"                  class="stab <?= !$filterStatus   ? 'aktif':'' ?>">Semua <span><?= $jmlPending+$jmlDiterima+$jmlDitolak ?></span></a>
      <a href="lamaran.php?status=pending"   class="stab <?= $filterStatus==='pending'  ?'aktif yellow':'' ?>">Menunggu <span class="pill-yellow"><?= $jmlPending ?></span></a>
      <a href="lamaran.php?status=diterima"  class="stab <?= $filterStatus==='diterima' ?'aktif green':'' ?>">Diterima <span class="pill-green"><?= $jmlDiterima ?></span></a>
      <a href="lamaran.php?status=ditolak"   class="stab <?= $filterStatus==='ditolak'  ?'aktif red':'' ?>">Ditolak <span class="pill-red"><?= $jmlDitolak ?></span></a>
    </div>

    <?php tampilPesan(); ?>

    <!-- Filter + Search -->
    <div class="filter-bar">
      <span class="filter-bar-count">Menampilkan <strong><?= count($semuaLamaran) ?></strong> lamaran</span>
      <input type="text" id="tableSearch" placeholder="🔍 Cari pelamar atau lowongan..." class="search-input">
    </div>

    <!-- Tabel -->
    <div class="card">
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>#</th><th>Pelamar</th><th>Lowongan</th><th>Perusahaan</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
          </thead>
          <tbody>
          <?php if (empty($semuaLamaran)): ?>
            <tr class="empty-row"><td colspan="7">
              <div class="empty-state-inline"><span>📨</span><p>Tidak ada lamaran ditemukan.</p></div>
            </td></tr>
          <?php else: ?>
            <?php foreach ($semuaLamaran as $i => $la): ?>
            <tr>
              <td><span class="row-num"><?= $i+1 ?></span></td>
              <td>
                <div class="td-user">
                  <div class="td-user-avatar mahasiswa"><?= strtoupper(substr($la['nama_mahasiswa'],0,2)) ?></div>
                  <div>
                    <p class="td-title"><?= bersihkan($la['nama_mahasiswa']) ?></p>
                    <p class="td-sub"><?= bersihkan($la['email_mahasiswa']) ?></p>
                  </div>
                </div>
              </td>
              <td>
                <p class="td-title"><?= potongTeks(bersihkan($la['judul_lowongan']), 35) ?></p>
                <p class="td-sub">Deadline: <?= formatTanggal($la['deadline']) ?></p>
              </td>
              <td>
                <div class="td-company">
                  <div class="td-company-logo"><?= strtoupper(substr($la['nama_perusahaan'],0,2)) ?></div>
                  <span><?= bersihkan($la['nama_perusahaan']) ?></span>
                </div>
              </td>
              <td class="text-muted"><?= formatTanggal(date('Y-m-d', strtotime($la['created_at']))) ?></td>
              <td>
                <span class="badge badge-<?= $la['status'] ?>">
                  <?= $la['status']==='pending' ? 'Menunggu' : ucfirst($la['status']) ?>
                </span>
              </td>
              <td>
                <div class="aksi-col">
                  <!-- Lihat detail -->
                  <a href="lamaran.php?detail=<?= $la['id'] ?><?= $filterStatus ? '&status='.$filterStatus : '' ?>" class="btn-detail-sm">
                    👁️ Detail
                  </a>
                  <!-- Ubah status -->
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="aksi" value="ubah_status">
                    <input type="hidden" name="id"   value="<?= $la['id'] ?>">
                    <select name="status" class="status-select-sm"
                            onchange="if(confirm('Ubah status menjadi '+this.options[this.selectedIndex].text+'?')) this.form.submit(); else this.value='<?= $la['status'] ?>';">
                      <option value="pending"  <?= $la['status']==='pending'  ?'selected':'' ?>>⏳ Menunggu</option>
                      <option value="diterima" <?= $la['status']==='diterima' ?'selected':'' ?>>✅ Diterima</option>
                      <option value="ditolak"  <?= $la['status']==='ditolak'  ?'selected':'' ?>>❌ Ditolak</option>
                    </select>
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

<!-- Modal Detail Lamaran -->
<?php if ($detailLamaran): ?>
<div class="modal-overlay" id="modalDetail">
  <div class="modal-box">
    <div class="modal-header">
      <h3>📄 Detail Lamaran</h3>
      <a href="lamaran.php<?= $filterStatus ? '?status='.$filterStatus : '' ?>" class="modal-close">✕</a>
    </div>
    <div class="modal-body">
      <div class="modal-info-grid">
        <div class="modal-info-item">
          <span class="modal-info-lbl">Pelamar</span>
          <span class="modal-info-val"><?= bersihkan($detailLamaran['nama_mahasiswa']) ?></span>
        </div>
        <div class="modal-info-item">
          <span class="modal-info-lbl">Email</span>
          <span class="modal-info-val"><?= bersihkan($detailLamaran['email_mahasiswa']) ?></span>
        </div>
        <div class="modal-info-item">
          <span class="modal-info-lbl">No. HP</span>
          <span class="modal-info-val"><?= bersihkan($detailLamaran['hp_mahasiswa'] ?: '-') ?></span>
        </div>
        <div class="modal-info-item">
          <span class="modal-info-lbl">Posisi Dilamar</span>
          <span class="modal-info-val"><?= bersihkan($detailLamaran['judul_lowongan']) ?></span>
        </div>
        <div class="modal-info-item">
          <span class="modal-info-lbl">Perusahaan</span>
          <span class="modal-info-val"><?= bersihkan($detailLamaran['nama_perusahaan']) ?></span>
        </div>
        <div class="modal-info-item">
          <span class="modal-info-lbl">Tanggal Lamar</span>
          <span class="modal-info-val"><?= formatTanggal(date('Y-m-d', strtotime($detailLamaran['created_at']))) ?></span>
        </div>
        <div class="modal-info-item">
          <span class="modal-info-lbl">Status Saat Ini</span>
          <span class="badge badge-<?= $detailLamaran['status'] ?>">
            <?= $detailLamaran['status']==='pending' ? 'Menunggu' : ucfirst($detailLamaran['status']) ?>
          </span>
        </div>
        <?php if ($detailLamaran['cv_file']): ?>
        <div class="modal-info-item">
          <span class="modal-info-lbl">File CV</span>
          <a href="../<?= UPLOAD_CV . bersihkan($detailLamaran['cv_file']) ?>" target="_blank" class="btn-detail-sm" style="display:inline-block">
            📎 Download CV
          </a>
        </div>
        <?php endif; ?>
      </div>
      <div class="modal-surat">
        <p class="modal-info-lbl">Surat Lamaran</p>
        <div class="modal-surat-text"><?= nl2br(bersihkan($detailLamaran['surat_lamaran'])) ?></div>
      </div>
      <!-- Ubah status dari modal -->
      <form method="POST" class="modal-status-form">
        <input type="hidden" name="aksi" value="ubah_status">
        <input type="hidden" name="id"   value="<?= $detailLamaran['id'] ?>">
        <label class="modal-info-lbl">Ubah Status</label>
        <div style="display:flex; gap:10px; margin-top:8px;">
          <select name="status" class="filter-select-sm">
            <option value="pending"  <?= $detailLamaran['status']==='pending'  ?'selected':'' ?>>⏳ Menunggu</option>
            <option value="diterima" <?= $detailLamaran['status']==='diterima' ?'selected':'' ?>>✅ Diterima</option>
            <option value="ditolak"  <?= $detailLamaran['status']==='ditolak'  ?'selected':'' ?>>❌ Ditolak</option>
          </select>
          <button type="submit" class="btn-submit" style="padding:8px 20px;">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<style>
/* Status Tabs */
.status-tabs { display:flex; gap:4px; margin-bottom:20px; border-bottom:2px solid var(--border); }
.stab { padding:10px 18px; font-size:13px; font-weight:600; color:var(--text-muted); border-bottom:2px solid transparent; margin-bottom:-2px; display:flex; align-items:center; gap:7px; transition:all var(--transition); }
.stab:hover { color:var(--text-primary); }
.stab.aktif { color:var(--primary); border-bottom-color:var(--primary); }
.stab.aktif.yellow { color:#92400e; border-bottom-color:#f59e0b; }
.stab.aktif.green  { color:#065f46; border-bottom-color:#10b981; }
.stab.aktif.red    { color:#991b1b; border-bottom-color:#ef4444; }
.stab span { font-size:11px; font-weight:700; padding:1px 7px; border-radius:999px; background:var(--bg-section); color:var(--text-muted); }
.pill-yellow { background:#fef3c7; color:#92400e; }
.pill-green  { background:#d1fae5; color:#065f46; }
.pill-red    { background:#fee2e2; color:#991b1b; }
/* Select status */
.status-select-sm { font-size:12px; padding:4px 8px; border:1.5px solid var(--border); border-radius:var(--radius-sm); background:var(--bg-light); cursor:pointer; }
/* Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:500; display:flex; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(4px); }
.modal-box { background:#fff; border-radius:var(--radius-xl); width:100%; max-width:620px; max-height:90vh; overflow-y:auto; box-shadow:0 24px 60px rgba(0,0,0,0.2); }
.modal-header { display:flex; justify-content:space-between; align-items:center; padding:20px 24px; border-bottom:1px solid var(--border); }
.modal-header h3 { font-size:17px; font-weight:700; }
.modal-close { font-size:18px; color:var(--text-muted); padding:4px 8px; border-radius:var(--radius-sm); transition:all var(--transition); }
.modal-close:hover { background:var(--danger-bg); color:var(--danger); }
.modal-body { padding:24px; }
.modal-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:20px; }
.modal-info-item { display:flex; flex-direction:column; gap:4px; }
.modal-info-lbl { font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; }
.modal-info-val { font-size:14px; font-weight:500; color:var(--text-primary); }
.modal-surat { background:var(--bg-light); border-radius:var(--radius-md); padding:16px; margin-bottom:20px; }
.modal-surat-text { font-size:14px; color:var(--text-secondary); line-height:1.8; margin-top:8px; }
.modal-status-form { border-top:1px solid var(--border); padding-top:16px; }
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