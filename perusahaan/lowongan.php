<?php

session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../includes/sidebar_prs.php';

cekRole('perusahaan');

$userId = $_SESSION['user_id'];

// ======================================================
// PROSES FORM
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $aksi = $_POST['aksi'] ?? '';

    // ==================================================
    // TAMBAH / EDIT
    // ==================================================

    if ($aksi === 'tambah' || $aksi === 'edit') {

        $kategoriId = sanitasiInt($_POST['kategori_id']);
        $judul      = escape($conn, $_POST['judul'] ?? '');
        $deskripsi  = escape($conn, $_POST['deskripsi'] ?? '');
        $kualifikasi= escape($conn, $_POST['kualifikasi'] ?? '');
        $lokasi     = escape($conn, $_POST['lokasi'] ?? '');
        $tipeKerja  = escape($conn, $_POST['tipe_kerja'] ?? 'onsite');
        $gaji       = escape($conn, $_POST['gaji'] ?? '');
        $deadline   = escape($conn, $_POST['deadline'] ?? '');
        $status     = escape($conn, $_POST['status'] ?? 'aktif');

        // Validasi
        if (
            empty($judul) ||
            empty($kategoriId) ||
            empty($deskripsi) ||
            empty($deadline)
        ) {

            setPesan('error', 'Semua field wajib harus diisi.');

        } else {

            // TAMBAH
            if ($aksi === 'tambah') {

                query($conn,
                    "INSERT INTO lowongan
                    (
                        user_id,
                        kategori_id,
                        judul,
                        deskripsi,
                        kualifikasi,
                        lokasi,
                        tipe_kerja,
                        gaji,
                        deadline,
                        status
                    )
                    VALUES
                    (
                        {$userId},
                        {$kategoriId},
                        '{$judul}',
                        '{$deskripsi}',
                        '{$kualifikasi}',
                        '{$lokasi}',
                        '{$tipeKerja}',
                        '{$gaji}',
                        '{$deadline}',
                        '{$status}'
                    )"
                );

                setPesan('sukses', 'Lowongan berhasil ditambahkan.');

            }

            // EDIT
            else {

                $id = sanitasiInt($_POST['id']);

                query($conn,
                    "UPDATE lowongan SET
                        kategori_id = {$kategoriId},
                        judul       = '{$judul}',
                        deskripsi   = '{$deskripsi}',
                        kualifikasi = '{$kualifikasi}',
                        lokasi      = '{$lokasi}',
                        tipe_kerja  = '{$tipeKerja}',
                        gaji        = '{$gaji}',
                        deadline    = '{$deadline}',
                        status      = '{$status}'
                    WHERE id = {$id}
                    AND user_id = {$userId}"
                );

                setPesan('sukses', 'Lowongan berhasil diperbarui.');

            }

        }

    }

    // ==================================================
    // HAPUS
    // ==================================================

    if ($aksi === 'hapus') {

        $id = sanitasiInt($_POST['id']);

        query($conn,
            "DELETE FROM lowongan
             WHERE id = {$id}
             AND user_id = {$userId}"
        );

        setPesan('sukses', 'Lowongan berhasil dihapus.');

    }

    redirect('lowongan.php');

}

// ======================================================
// AMBIL DATA
// ======================================================

$semuaKategori = ambilSemua($conn,
    "SELECT *
     FROM kategori
     ORDER BY nama_kategori ASC"
);

$semuaLowongan = ambilSemua($conn,
    "SELECT
        l.*,
        k.nama_kategori
     FROM lowongan l
     JOIN kategori k ON l.kategori_id = k.id
     WHERE l.user_id = {$userId}
     ORDER BY l.created_at DESC"
);

// ======================================================
// EDIT DATA
// ======================================================

$editData = null;

if (isset($_GET['edit'])) {

    $id = sanitasiInt($_GET['edit']);

    $editData = ambilSatu($conn,
        "SELECT *
         FROM lowongan
         WHERE id = {$id}
         AND user_id = {$userId}"
    );

}

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

<?php require_once '../includes/sidebar_prs.php'; ?>

<main class="dash-main">

<!-- TOPBAR -->
<header class="topbar">

    <div class="topbar-left">

        <button class="topbar-mobile-toggle"
                onclick="toggleSidebar()">
            ☰
        </button>

        <h1 class="topbar-title">
            Kelola Lowongan
        </h1>

    </div>

</header>

<!-- CONTENT -->
<div class="dash-content">

    <!-- HEADER -->
    <div class="page-header">

        <div>

            <h1>Lowongan Pekerjaan</h1>

            <p style="margin-top:6px;color:var(--text-secondary)">
                Kelola dan publikasikan lowongan pekerjaan perusahaan Anda.
            </p>

        </div>

        <button class="btn-toggle-form"
                onclick="toggleForm()">

            + Tambah Lowongan

        </button>

    </div>

    <?php tampilPesan(); ?>

    <!-- FORM -->
    <div class="form-card"
         id="formPanel"
         style="<?= $editData ? '' : 'display:none' ?>">

        <h3>
            <?= $editData ? 'Edit Lowongan' : 'Tambah Lowongan Baru' ?>
        </h3>

        <form method="POST">

            <input type="hidden"
                   name="aksi"
                   value="<?= $editData ? 'edit' : 'tambah' ?>">

            <?php if ($editData): ?>

                <input type="hidden"
                       name="id"
                       value="<?= $editData['id'] ?>">

            <?php endif; ?>

            <div class="form-grid-2">

                <!-- JUDUL -->
                <div class="form-group">

                    <label>
                        Judul Lowongan
                        <span class="wajib">*</span>
                    </label>

                    <input type="text"
                           name="judul"
                           required
                           value="<?= bersihkan($editData['judul'] ?? '') ?>">

                </div>

                <!-- KATEGORI -->
                <div class="form-group">

                    <label>
                        Kategori
                        <span class="wajib">*</span>
                    </label>

                    <select name="kategori_id" required>

                        <option value="">
                            -- Pilih Kategori --
                        </option>

                        <?php foreach ($semuaKategori as $k): ?>

                            <option value="<?= $k['id'] ?>"
                                <?= ($editData['kategori_id'] ?? 0) == $k['id']
                                    ? 'selected'
                                    : '' ?>>

                                <?= bersihkan($k['nama_kategori']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- LOKASI -->
                <div class="form-group">

                    <label>Lokasi</label>

                    <input type="text"
                           name="lokasi"
                           value="<?= bersihkan($editData['lokasi'] ?? '') ?>">

                </div>

                <!-- TIPE -->
                <div class="form-group">

                    <label>Tipe Kerja</label>

                    <select name="tipe_kerja">

                        <?php foreach (['onsite','remote','hybrid'] as $t): ?>

                            <option value="<?= $t ?>"
                                <?= ($editData['tipe_kerja'] ?? 'onsite') === $t
                                    ? 'selected'
                                    : '' ?>>

                                <?= ucfirst($t) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- GAJI -->
                <div class="form-group">

                    <label>Gaji</label>

                    <input type="text"
                           name="gaji"
                           placeholder="Contoh: Rp 5.000.000"
                           value="<?= bersihkan($editData['gaji'] ?? '') ?>">

                </div>

                <!-- DEADLINE -->
                <div class="form-group">

                    <label>
                        Deadline
                        <span class="wajib">*</span>
                    </label>

                    <input type="date"
                           name="deadline"
                           required
                           value="<?= $editData['deadline'] ?? '' ?>">

                </div>

                <!-- STATUS -->
                <div class="form-group">

                    <label>Status</label>

                    <select name="status">

                        <option value="aktif"
                            <?= ($editData['status'] ?? 'aktif') === 'aktif'
                                ? 'selected'
                                : '' ?>>
                            Aktif
                        </option>

                        <option value="ditutup"
                            <?= ($editData['status'] ?? '') === 'ditutup'
                                ? 'selected'
                                : '' ?>>
                            Ditutup
                        </option>

                    </select>

                </div>

            </div>

            <!-- DESKRIPSI -->
            <div class="form-group">

                <label>
                    Deskripsi
                    <span class="wajib">*</span>
                </label>

                <textarea name="deskripsi"
                          rows="5"
                          required><?= bersihkan($editData['deskripsi'] ?? '') ?></textarea>

            </div>

            <!-- KUALIFIKASI -->
            <div class="form-group">

                <label>Kualifikasi</label>

                <textarea name="kualifikasi"
                          rows="4"><?= bersihkan($editData['kualifikasi'] ?? '') ?></textarea>

            </div>

            <!-- ACTION -->
            <div class="form-actions">

                <button type="submit"
                        class="btn-submit">

                    <?= $editData ? 'Simpan Perubahan' : 'Publikasikan Lowongan' ?>

                </button>

                <a href="lowongan.php"
                   class="btn-batal">

                    Batal

                </a>

            </div>

        </form>

    </div>

    <!-- TABLE -->
    <div class="card">

        <div class="card-header">

            <div class="card-title">
                Semua Lowongan
            </div>

        </div>

        <div class="card-body">

            <div class="table-wrap">

                <table class="data-table">

                    <thead>

                        <tr>

                            <th>#</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (empty($semuaLowongan)): ?>

                        <tr class="empty-row">

                            <td colspan="7">
                                Belum ada lowongan dibuat.
                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($semuaLowongan as $i => $l): ?>

                            <tr>

                                <td><?= $i + 1 ?></td>

                                <td>
                                    <?= bersihkan($l['judul']) ?>
                                </td>

                                <td>
                                    <?= bersihkan($l['nama_kategori']) ?>
                                </td>

                                <td>
                                    <?= bersihkan($l['lokasi']) ?>
                                </td>

                                <td>
                                    <?= formatTanggal($l['deadline']) ?>
                                </td>

                                <td>

                                    <?= $l['status'] === 'aktif'
                                        ? '<span class="badge badge-aktif">Aktif</span>'
                                        : '<span class="badge badge-tutup">Ditutup</span>'
                                    ?>

                                </td>

                                <td class="aksi-col">

                                    <a href="lowongan.php?edit=<?= $l['id'] ?>"
                                       class="btn-edit">

                                        Edit

                                    </a>

                                    <form method="POST"
                                          style="display:inline"
                                          onsubmit="return confirm('Hapus lowongan ini?')">

                                        <input type="hidden"
                                               name="aksi"
                                               value="hapus">

                                        <input type="hidden"
                                               name="id"
                                               value="<?= $l['id'] ?>">

                                        <button type="submit"
                                                class="btn-hapus">

                                            Hapus

                                        </button>

                                    </form>

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

</main>
</div>

<script>

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}

function toggleForm() {

    const panel = document.getElementById('formPanel');

    if (panel.style.display === 'none') {
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }

}

</script>

</main>
</div>

</body>
</html>