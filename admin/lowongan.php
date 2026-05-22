<?php
// ============================================================
//  admin/lowongan.php — Kelola semua lowongan (CRUD)
// ============================================================

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ .
 '../functions.php';
cekRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah' || $aksi === 'edit') {
        $userId    = sanitasiInt($_POST['user_id']);
        $katId     = sanitasiInt($_POST['kategori_id']);
        $judul     = escape($conn, $_POST['judul']      ?? '');
        $desk      = escape($conn, $_POST['deskripsi']  ?? '');
        $kual      = escape($conn, $_POST['kualifikasi']?? '');
        $lokasi    = escape($conn, $_POST['lokasi']     ?? '');
        $tipe      = escape($conn, $_POST['tipe_kerja'] ?? 'onsite');
        $gaji      = escape($conn, $_POST['gaji']       ?? '');
        $deadline  = escape($conn, $_POST['deadline']   ?? '');
        $status    = escape($conn, $_POST['status']     ?? 'aktif');

        if (empty($judul) || empty($userId) || empty($katId) || empty($deadline)) {
            setPesan('error', 'Judul, perusahaan, kategori, dan deadline wajib diisi.');
        } else {
            if ($aksi === 'tambah') {
                query($conn, "INSERT INTO lowongan (user_id,kategori_id,judul,deskripsi,kualifikasi,lokasi,tipe_kerja,gaji,deadline,status)
                              VALUES ({$userId},{$katId},'{$judul}','{$desk}','{$kual}','{$lokasi}','{$tipe}','{$gaji}','{$deadline}','{$status}')");
                setPesan('sukses', 'Lowongan berhasil ditambahkan.');
            } else {
                $id = sanitasiInt($_POST['id']);
                query($conn, "UPDATE lowongan SET user_id={$userId},kategori_id={$katId},
                              judul='{$judul}',deskripsi='{$desk}',kualifikasi='{$kual}',
                              lokasi='{$lokasi}',tipe_kerja='{$tipe}',gaji='{$gaji}',
                              deadline='{$deadline}',status='{$status}' WHERE id={$id}");
                setPesan('sukses', 'Lowongan berhasil diperbarui.');
            }
        }
    }

    if ($aksi === 'hapus') {
        $id = sanitasiInt($_POST['id']);
        query($conn, "DELETE FROM lowongan WHERE id={$id}");
        setPesan('sukses', 'Lowongan berhasil dihapus.');
    }
    redirect('lowongan.php');
}

$semuaLowongan = ambilSemua($conn,
    "SELECT l.*, u.nama AS nama_perusahaan, k.nama_kategori
     FROM lowongan l JOIN users u ON l.user_id=u.id JOIN kategori k ON l.kategori_id=k.id
     ORDER BY l.created_at DESC"
);
$semuaPerusahaan = ambilSemua($conn, "SELECT id,nama FROM users WHERE role='perusahaan' ORDER BY nama");
$semuaKategori   = ambilSemua($conn, "SELECT * FROM kategori ORDER BY nama_kategori");

$editData = null;
if (isset($_GET['edit'])) {
    $editData = ambilSatu($conn, "SELECT * FROM lowongan WHERE id=".sanitasiInt($_GET['edit']));
}
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola Lowongan — <?= APP_NAME ?></title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head><body>
<?php require_once '../includes/sidebar_admin.php'; ?>

<div class="page-header">
    <h1>📋 Kelola Lowongan</h1>
    <button class="btn-toggle-form" onclick="toggleForm()">+ Tambah Lowongan</button>
</div>

<?php tampilPesan(); ?>

<!-- Form Tambah/Edit -->
<div class="form-card" id="formPanel" style="<?= $editData ? '' : 'display:none' ?>">
    <h3><?= $editData ? 'Edit Lowongan' : 'Tambah Lowongan Baru' ?></h3>
    <form method="POST">
        <input type="hidden" name="aksi" value="<?= $editData ? 'edit' : 'tambah' ?>">
        <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?= $editData['id'] ?>">
        <?php endif; ?>

        <div class="form-grid-2">
            <div class="form-group">
                <label>Judul Lowongan <span class="wajib">*</span></label>
                <input type="text" name="judul" required value="<?= bersihkan($editData['judul'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Perusahaan <span class="wajib">*</span></label>
                <select name="user_id" required>
                    <option value="">-- Pilih --</option>
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
                    <option value="">-- Pilih --</option>
                    <?php foreach ($semuaKategori as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= ($editData['kategori_id'] ?? 0) == $k['id'] ? 'selected':'' ?>>
                        <?= bersihkan($k['nama_kategori']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Lokasi</label>
                <input type="text" name="lokasi" value="<?= bersihkan($editData['lokasi'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Tipe Kerja</label>
                <select name="tipe_kerja">
                    <?php foreach (['onsite','remote','hybrid'] as $t): ?>
                    <option value="<?= $t ?>" <?= ($editData['tipe_kerja'] ?? 'onsite') === $t ? 'selected':'' ?>>
                        <?= ucfirst($t) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Gaji</label>
                <input type="text" name="gaji" placeholder="Contoh: Rp 3.000.000"
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
                    <option value="ditutup" <?= ($editData['status'] ?? '') === 'ditutup' ? 'selected':'' ?>>Ditutup</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="4"><?= bersihkan($editData['deskripsi'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Kualifikasi</label>
            <textarea name="kualifikasi" rows="3"><?= bersihkan($editData['kualifikasi'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-submit"><?= $editData ? 'Simpan' : 'Tambah Lowongan' ?></button>
            <a href="lowongan.php" class="btn-batal">Batal</a>
        </div>
    </form>
</div>

<!-- Tabel -->
<div class="table-card">
    <table class="data-table">
        <thead><tr><th>#</th><th>Judul</th><th>Perusahaan</th><th>Kategori</th><th>Deadline</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if (empty($semuaLowongan)): ?>
            <tr><td colspan="7" class="empty-td">Belum ada lowongan.</td></tr>
        <?php else: ?>
            <?php foreach ($semuaLowongan as $i => $low): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= bersihkan($low['judul']) ?></td>
                <td><?= bersihkan($low['nama_perusahaan']) ?></td>
                <td><?= bersihkan($low['nama_kategori']) ?></td>
                <td><?= formatTanggal($low['deadline']) ?></td>
                <td><?= $low['status']==='aktif'
                    ? '<span class="badge-aktif">Aktif</span>'
                    : '<span class="badge-tutup">Ditutup</span>' ?></td>
                <td class="aksi-col">
                    <a href="lowongan.php?edit=<?= $low['id'] ?>" class="btn-edit">Edit</a>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Hapus lowongan ini?')">
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id"   value="<?= $low['id'] ?>">
                        <button type="submit" class="btn-hapus">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</main></div>
<script>
function toggleForm() {
    const p = document.getElementById('formPanel');
    p.style.display = p.style.display === 'none' ? 'block' : 'none';
}
</script>
</body></html>