<?php
// ============================================================
//  admin/users.php — Kelola semua user (CRUD)
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../functions.php';
cekRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'edit') {
        $id    = sanitasiInt($_POST['id']);
        $nama  = escape($conn, $_POST['nama']  ?? '');
        $email = escape($conn, $_POST['email'] ?? '');
        $role  = escape($conn, $_POST['role']  ?? '');
        $hp    = escape($conn, $_POST['no_hp'] ?? '');

        if (empty($nama) || empty($email)) {
            setPesan('error', 'Nama dan email wajib diisi.');
        } else {
            // Cek email duplikat (kecuali milik user ini sendiri)
            $cek = ambilSatu($conn, "SELECT id FROM users WHERE email='{$email}' AND id!={$id}");
            if ($cek) {
                setPesan('error', 'Email sudah digunakan user lain.');
            } else {
                query($conn, "UPDATE users SET nama='{$nama}',email='{$email}',role='{$role}',no_hp='{$hp}' WHERE id={$id}");
                // Update password jika diisi
                if (!empty($_POST['password_baru'])) {
                    $hash = password_hash($_POST['password_baru'], PASSWORD_BCRYPT);
                    $hashEsc = escape($conn, $hash);
                    query($conn, "UPDATE users SET password='{$hashEsc}' WHERE id={$id}");
                }
                setPesan('sukses', 'User berhasil diperbarui.');
            }
        }
    }

    if ($aksi === 'hapus') {
        $id = sanitasiInt($_POST['id']);
        // Jangan hapus diri sendiri
        if ($id === (int)$_SESSION['user_id']) {
            setPesan('error', 'Tidak bisa menghapus akun sendiri.');
        } else {
            query($conn, "DELETE FROM users WHERE id={$id}");
            setPesan('sukses', 'User berhasil dihapus.');
        }
    }
    redirect('users.php');
}

$semuaUsers = ambilSemua($conn, "SELECT * FROM users ORDER BY role, nama");
$editData   = null;
if (isset($_GET['edit'])) {
    $editData = ambilSatu($conn, "SELECT * FROM users WHERE id=".sanitasiInt($_GET['edit']));
}
?>
<!DOCTYPE html><html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kelola Users — <?= APP_NAME ?></title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
</head><body>
<?php require_once '../includes/sidebar_admin.php'; ?>

<div class="page-header"><h1>👥 Kelola Users</h1></div>
<?php tampilPesan(); ?>

<?php if ($editData): ?>
<div class="form-card">
    <h3>Edit User</h3>
    <form method="POST">
        <input type="hidden" name="aksi" value="edit">
        <input type="hidden" name="id"   value="<?= $editData['id'] ?>">
        <div class="form-grid-2">
            <div class="form-group">
                <label>Nama <span class="wajib">*</span></label>
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
                <label>Password Baru <small>(kosongkan jika tidak ingin ubah)</small></label>
                <input type="password" name="password_baru" placeholder="Isi jika ingin ubah password">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-submit">Simpan Perubahan</button>
            <a href="users.php" class="btn-batal">Batal</a>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="table-card">
    <table class="data-table">
        <thead><tr><th>#</th><th>Nama</th><th>Email</th><th>Role</th><th>No. HP</th><th>Bergabung</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($semuaUsers as $i => $u): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= bersihkan($u['nama']) ?></td>
            <td><?= bersihkan($u['email']) ?></td>
            <td>
                <?php
                $roleColor = ['admin'=>'badge-admin','mahasiswa'=>'badge-mhs','perusahaan'=>'badge-prs'];
                $rc = $roleColor[$u['role']] ?? '';
                ?>
                <span class="<?= $rc ?>"><?= ucfirst($u['role']) ?></span>
            </td>
            <td><?= bersihkan($u['no_hp'] ?? '-') ?></td>
            <td><?= formatTanggal(date('Y-m-d', strtotime($u['created_at']))) ?></td>
            <td class="aksi-col">
                <a href="users.php?edit=<?= $u['id'] ?>" class="btn-edit">Edit</a>
                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('Hapus user ini?')">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id"   value="<?= $u['id'] ?>">
                    <button type="submit" class="btn-hapus">Hapus</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</main></div>
</body></html>