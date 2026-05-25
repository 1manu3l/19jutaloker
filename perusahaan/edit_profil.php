<?php
// ============================================================
// perusahaan/edit_profil.php
// Edit profil perusahaan
// ============================================================

session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_check.php';
require_once __DIR__ . '/../functions.php';

// ============================================================
// CEK LOGIN
// ============================================================

if (!sudahLogin() || $_SESSION['role'] !== 'perusahaan') {
    redirect('../login.php');
}

$userId = $_SESSION['user_id'];

$error = '';
$sukses = '';

// ============================================================
// AMBIL DATA USER
// ============================================================

$query = mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE id = '$userId'
");

$user = mysqli_fetch_assoc($query);

// ============================================================
// PROSES UPDATE
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $email  = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp  = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    // VALIDASI
    if (
        empty($nama) ||
        empty($email)
    ) {

        $error = "Nama dan email wajib diisi.";

    } else {

        $update = mysqli_query($conn, "
            UPDATE users
            SET
                nama = '$nama',
                email = '$email',
                no_hp = '$no_hp',
                alamat = '$alamat'
            WHERE id = '$userId'
        ");

        if ($update) {

            $_SESSION['nama'] = $nama;

            $sukses = "Profil berhasil diperbarui.";

            // refresh data
            $query = mysqli_query($conn, "
                SELECT *
                FROM users
                WHERE id = '$userId'
            ");

            $user = mysqli_fetch_assoc($query);

        } else {

            $error = "Terjadi kesalahan saat update profil.";

        }

    }

}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil | <?= APP_NAME ?></title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<div class="dash-layout">

<?php require_once '../includes/sidebar_prs.php'; ?>

<!-- MAIN -->
<main class="dash-main">

    <!-- TOPBAR -->
    <div class="topbar">

        <div class="topbar-left">

            <button class="topbar-mobile-toggle" onclick="toggleSidebar()">
                ☰
            </button>

            <h1 class="topbar-title">
                Edit Profil
            </h1>

        </div>

        <div class="topbar-right">

            <div class="topbar-user">

                <div class="topbar-avatar">
                    <?= strtoupper(substr($_SESSION['nama'], 0, 2)) ?>
                </div>

                <span>
                    <?= htmlspecialchars($_SESSION['nama']) ?>
                </span>

            </div>

        </div>

    </div>

    <!-- CONTENT -->
    <div class="dash-content">

        <!-- HEADER -->
        <div class="page-header">

            <div>

                <h1>✏ Edit Profil Perusahaan</h1>

                <p style="
                    margin-top:8px;
                    color:var(--text-secondary);
                    line-height:1.8;
                    max-width:700px;
                ">
                    Perbarui informasi perusahaan Anda agar terlihat
                    lebih profesional dan terpercaya bagi pelamar.
                </p>

            </div>

        </div>

        <!-- ALERT -->
        <?php if ($error): ?>

            <div class="alert alert-error">
                <?= $error ?>
            </div>

        <?php endif; ?>

        <?php if ($sukses): ?>

            <div class="alert alert-sukses">
                <?= $sukses ?>
            </div>

        <?php endif; ?>

        <!-- FORM -->
        <div class="form-card">

            <h3>Informasi Perusahaan</h3>

            <form method="POST">

                <div class="form-grid-2">

                    <!-- NAMA -->
                    <div class="form-group">

                        <label>Nama Perusahaan</label>

                        <input
                            type="text"
                            name="nama"
                            value="<?= htmlspecialchars($user['nama']) ?>"
                            required
                        >

                    </div>

                    <!-- EMAIL -->
                    <div class="form-group">

                        <label>Email</label>

                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars($user['email']) ?>"
                            required
                        >

                    </div>

                    <!-- HP -->
                    <div class="form-group">

                        <label>Nomor HP</label>

                        <input
                            type="text"
                            name="no_hp"
                            value="<?= htmlspecialchars($user['no_hp']) ?>"
                        >

                    </div>

                    <!-- ALAMAT -->
                    <div class="form-group">

                        <label>Alamat</label>

                        <input
                            type="text"
                            name="alamat"
                            value="<?= htmlspecialchars($user['alamat']) ?>"
                        >

                    </div>

                </div>

                <!-- BUTTON -->
                <div class="form-actions">

                    <button type="submit" class="btn-submit">
                        💾 Simpan Perubahan
                    </button>

                    <a href="profil.php" class="btn-batal">
                        Kembali
                    </a>

                </div>

            </form>

        </div>

    </div>

</main>

</div>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}
</script>

</body>
</html>