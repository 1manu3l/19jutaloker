<?php
// ============================================================
//  admin/kategori.php — Kelola kategori (CRUD)
// ============================================================



session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../functions.php';
cekRole('admin');


// ============================================================
//  PROSES AKSI (Tambah / Edit / Hapus)
// ============================================================
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
                setPesan('sukses', 'Kategori berhasil ditambahkan.');
            } else {
                $id = sanitasiInt($_POST['id']);
                query($conn, "UPDATE kategori SET nama_kategori='{$nama}', deskripsi='{$desk}' WHERE id={$id}");
                setPesan('sukses', 'Kategori berhasil diperbarui.');
            }
        }
    }

    if ($aksi === 'hapus') {
        $id = sanitasiInt($_POST['id']);
        query($conn, "DELETE FROM kategori WHERE id={$id}");
        setPesan('sukses', 'Kategori berhasil dihapus.');
    }
    redirect('kategori.php');
}

// Ambil data
$semuaKategori = ambilSemua($conn, "SELECT * FROM kategori ORDER BY nama_kategori");

// Data edit (jika klik tombol edit)
$editData = null;
if (isset($_GET['edit'])) {
    $editData = ambilSatu($conn, "SELECT * FROM kategori WHERE id=".sanitasiInt($_GET['edit']));
}

require_once '../config.php';
$judulHalaman = 'Kelola Kategori';
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola Kategori — <?= APP_NAME ?></title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head><body>
<?php require_once '../includes/sidebar_admin.php'; ?>

<div class="page-header">
    <h1>📂 Kelola Kategori</h1>
</div>

<?php tampilPesan(); ?>

<div class="layout-crud">

    <!-- Form Tambah / Edit -->
    <div class="form-card">
        <h3><?= $editData ? 'Edit Kategori' : 'Tambah Kategori Baru' ?></h3>
        <form method="POST">
            <input type="hidden" name="aksi" value="<?= $editData ? 'edit' : 'tambah' ?>">
            <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Nama Kategori <span class="wajib">*</span></label>
                <input type="text" name="nama_kategori" required
                       value="<?= bersihkan($editData['nama_kategori'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="3"><?= bersihkan($editData['deskripsi'] ?? '') ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit"><?= $editData ? 'Simpan Perubahan' : 'Tambah Kategori' ?></button>
                <?php if ($editData): ?>
                    <a href="kategori.php" class="btn-batal">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel daftar kategori -->
    <div class="table-card">
        <h3>Daftar Kategori (<?= count($semuaKategori) ?>)</h3>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Nama Kategori</th><th>Deskripsi</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <?php if (empty($semuaKategori)): ?>
                <tr><td colspan="4" class="empty-td">Belum ada kategori.</td></tr>
            <?php else: ?>
                <?php foreach ($semuaKategori as $i => $kat): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= bersihkan($kat['nama_kategori']) ?></td>
                    <td><?= potongTeks(bersihkan($kat['deskripsi'] ?? '-'), 60) ?></td>
                    <td class="aksi-col">
                        <a href="kategori.php?edit=<?= $kat['id'] ?>" class="btn-edit">Edit</a>
                        <form method="POST" style="display:inline"
                              onsubmit="return confirm('Hapus kategori ini?')">
                            <input type="hidden" name="aksi" value="hapus">
                            <input type="hidden" name="id"   value="<?= $kat['id'] ?>">
                            <button type="submit" class="btn-hapus">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</main></div>
</body></html>